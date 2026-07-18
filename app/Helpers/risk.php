<?php
require_once __DIR__ . '/../Services/TimeService.php';

function risk_current_time(): string
{
    return TimeService::sqlNow();
}

function active_blocage_match(PDO $pdo, string $number, string $gameType, ?int $lotteryId, ?int $agencyId): ?array
{
    $stmt = $pdo->prepare("\n        SELECT *\n        FROM blocages\n        WHERE status = 'active'\n          AND (number_value = ? OR number_value = '*' OR number_value IS NULL OR number_value = '')\n          AND (game_type IS NULL OR game_type = '' OR game_type = ? OR game_type = '*')\n          AND (lottery_id IS NULL OR lottery_id = ?)\n          AND (agency_id IS NULL OR agency_id = ?)\n          AND (starts_at IS NULL OR starts_at <= ?)\n          AND (ends_at IS NULL OR ends_at >= ?)\n        ORDER BY id DESC\n        LIMIT 1\n    ");
    $now = TimeService::sqlNow();
    $stmt->execute([$number, $gameType, $lotteryId, $agencyId, $now, $now]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function matching_limits(PDO $pdo, string $number, string $gameType, ?int $lotteryId, ?int $agencyId): array
{
    $stmt = $pdo->prepare("\n        SELECT *\n        FROM limites\n        WHERE status = 'active'\n          AND number_value = ?\n          AND (game_type IS NULL OR game_type = '' OR game_type = ? OR game_type = '*')\n          AND (lottery_id IS NULL OR lottery_id = ?)\n          AND (agency_id IS NULL OR agency_id = ?)\n        ORDER BY\n          CASE WHEN agency_id IS NULL THEN 1 ELSE 0 END,\n          CASE WHEN lottery_id IS NULL THEN 1 ELSE 0 END,\n          CASE WHEN game_type IS NULL OR game_type = '' OR game_type = '*' THEN 1 ELSE 0 END,\n          id DESC\n    ");
    $stmt->execute([$number, $gameType, $lotteryId, $agencyId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function played_amount_for_limit(PDO $pdo, array $limit, string $number, string $gameType, ?int $lotteryId, ?int $agencyId): float
{
    $where = ["fd.number_played = ?", "f.status <> 'cancelled'", "DATE(f.created_at) = ?"];
    $params = [$number, TimeService::today()];

    if (!empty($limit['game_type']) && $limit['game_type'] !== '*') {
        $where[] = 'fd.play_type = ?';
        $params[] = $gameType;
    }
    if (!empty($limit['lottery_id'])) {
        $where[] = 'f.lottery_id = ?';
        $params[] = (int)$lotteryId;
    }
    if (!empty($limit['agency_id'])) {
        $where[] = 'a.agency_id = ?';
        $params[] = (int)$agencyId;
    }

    $sql = "\n        SELECT COALESCE(SUM(fd.amount), 0)\n        FROM fiche_details fd\n        JOIN fiches f ON f.id = fd.fiche_id\n        JOIN agents a ON a.id = f.agent_id\n        WHERE " . implode(' AND ', $where);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (float)$stmt->fetchColumn();
}

function validate_risk_before_sale(PDO $pdo, array $agent, ?int $lotteryId, array $lines): void
{
    $agencyId = isset($agent['agency_id']) ? (int)$agent['agency_id'] : null;

    if ($lotteryId) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM lotteries WHERE id = ? AND status = 1');
        $stmt->execute([$lotteryId]);
        if ((int)$stmt->fetchColumn() === 0) {
            throw new RuntimeException('Lotterie inactive ou introuvable.');
        }
    }

    foreach ($lines as $line) {
        $number = (string)$line['number'];
        $gameType = (string)$line['type'];
        $amount = (float)$line['amount'];

        $blocage = active_blocage_match($pdo, $number, $gameType, $lotteryId, $agencyId);
        if ($blocage) {
            $scope = [];
            if (!empty($blocage['game_type'])) $scope[] = 'jeu: ' . $blocage['game_type'];
            if (!empty($blocage['lottery_id'])) $scope[] = 'lotterie: #' . $blocage['lottery_id'];
            if (!empty($blocage['agency_id'])) $scope[] = 'agence: #' . $blocage['agency_id'];
            $suffix = $scope ? ' (' . implode(', ', $scope) . ')' : '';
            throw new RuntimeException('Vente refusée: numéro ' . $number . ' bloqué' . $suffix . '. ' . ($blocage['motif'] ?? ''));
        }

        foreach (matching_limits($pdo, $number, $gameType, $lotteryId, $agencyId) as $limit) {
            $already = played_amount_for_limit($pdo, $limit, $number, $gameType, $lotteryId, $agencyId);
            $max = (float)$limit['max_amount'];
            if (($already + $amount) > $max) {
                $available = max(0, $max - $already);
                throw new RuntimeException('Limite atteinte pour le numéro ' . $number . '. Disponible: ' . number_format($available, 2));
            }
        }
    }
}

function risk_exposure(PDO $pdo, ?string $date = null): array
{
    $date = $date ?: TimeService::today();
    $stmt = $pdo->prepare("\n        SELECT fd.number_played, fd.play_type, COALESCE(l.name, 'Toutes') AS lottery_name,\n               COALESCE(SUM(fd.amount),0) AS total_played, COUNT(*) AS lines_count\n        FROM fiche_details fd\n        JOIN fiches f ON f.id = fd.fiche_id\n        LEFT JOIN lotteries l ON l.id = f.lottery_id\n        WHERE DATE(f.created_at)=? AND f.status <> 'cancelled'\n        GROUP BY fd.number_played, fd.play_type, f.lottery_id\n        ORDER BY total_played DESC\n        LIMIT 20\n    ");
    $stmt->execute([$date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
