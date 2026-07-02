<?php

function normalize_number(?string $value): string
{
    return preg_replace('/[^0-9]/', '', (string)$value);
}

function draw_numbers(array $tirage): array
{
    return array_values(array_filter([
        normalize_number($tirage['first_number'] ?? ''),
        normalize_number($tirage['second_number'] ?? ''),
        normalize_number($tirage['third_number'] ?? ''),
    ], fn($n) => $n !== ''));
}

function get_payout_rate(PDO $pdo, string $playType): float
{
    $stmt = $pdo->prepare("SELECT payout_rate FROM primes WHERE game_type = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$playType]);
    $rate = $stmt->fetchColumn();

    if ($rate === false) {
        $stmt = $pdo->prepare('SELECT multiplier FROM rates WHERE play_type = ? LIMIT 1');
        $stmt->execute([$playType]);
        $rate = $stmt->fetchColumn();
    }

    return (float)($rate ?: 0);
}

function is_winning_detail(array $detail, array $tirage): bool
{
    $playedRaw = trim((string)$detail['number_played']);
    $played = normalize_number($playedRaw);
    $numbers = draw_numbers($tirage);
    $playType = (string)$detail['play_type'];

    if ($played === '' || empty($numbers)) {
        return false;
    }

    if ($playType === 'borlette') {
        return in_array($played, $numbers, true);
    }

    if ($playType === 'mariage') {
        $parts = preg_split('/[^0-9]+/', $playedRaw, -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) < 2 && strlen($played) >= 4) {
            $parts = [substr($played, 0, 2), substr($played, 2, 2)];
        }
        if (count($parts) < 2) {
            return false;
        }
        $a = normalize_number($parts[0]);
        $b = normalize_number($parts[1]);
        return $a !== $b && in_array($a, $numbers, true) && in_array($b, $numbers, true);
    }

    if ($playType === 'lotto3') {
        $combo = implode('', array_slice($numbers, 0, 3));
        return strlen($played) === strlen($combo) && $played === $combo;
    }

    if ($playType === 'lotto4') {
        $first = normalize_number($tirage['first_number'] ?? '');
        return $played !== '' && $played === $first;
    }

    return false;
}

function update_fiche_status_from_gains(PDO $pdo, int $ficheId): void
{
    $stmt = $pdo->prepare("SELECT status FROM fiches WHERE id = ? LIMIT 1");
    $stmt->execute([$ficheId]);
    $current = $stmt->fetchColumn();
    if ($current === 'cancelled') {
        return;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM gains g JOIN fiche_details fd ON fd.id = g.fiche_detail_id WHERE fd.fiche_id = ? AND g.status = 'won'");
    $stmt->execute([$ficheId]);
    $wonCount = (int)$stmt->fetchColumn();

    if ($wonCount === 0) {
        $pdo->prepare("UPDATE fiches SET gain_amount = 0, status = 'lost' WHERE id = ? AND status <> 'cancelled'")->execute([$ficheId]);
        return;
    }

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount_won),0), SUM(CASE WHEN is_paid = 1 THEN 1 ELSE 0 END) FROM gains g JOIN fiche_details fd ON fd.id = g.fiche_detail_id WHERE fd.fiche_id = ? AND g.status = 'won'");
    $stmt->execute([$ficheId]);
    $row = $stmt->fetch(PDO::FETCH_NUM);
    $amount = (float)($row[0] ?? 0);
    $paidCount = (int)($row[1] ?? 0);
    $status = ($paidCount >= $wonCount) ? 'paid' : 'won';

    $stmt = $pdo->prepare('UPDATE fiches SET gain_amount = ?, status = ? WHERE id = ? AND status <> "cancelled"');
    $stmt->execute([$amount, $status, $ficheId]);
}

function calculate_tirage_gains(PDO $pdo, int $tirageId): array
{
    $stmt = $pdo->prepare('SELECT * FROM tirages WHERE id = ? LIMIT 1');
    $stmt->execute([$tirageId]);
    $tirage = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tirage) {
        throw new RuntimeException('Tirage introuvable.');
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM gains WHERE tirage_id = ? AND is_paid = 1');
    $stmt->execute([$tirageId]);
    if ((int)$stmt->fetchColumn() > 0) {
        throw new RuntimeException('Recalcul refusé: au moins un gain de ce tirage est déjà payé.');
    }

    $where = ["f.status <> 'cancelled'", "DATE(f.created_at) = ?"];
    $params = [$tirage['draw_date']];

    if (!empty($tirage['lottery_id'])) {
        $where[] = 'f.lottery_id = ?';
        $params[] = (int)$tirage['lottery_id'];
    } else {
        $where[] = 'f.lottery_id IS NULL';
    }

    if (!empty($tirage['tenant_id'])) {
        $where[] = 'f.tenant_id = ?';
        $params[] = (int)$tirage['tenant_id'];
    }

    $sql = "SELECT fd.*, f.agent_id, f.status AS fiche_status
            FROM fiche_details fd
            JOIN fiches f ON f.id = fd.fiche_id
            WHERE " . implode(' AND ', $where);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $summary = [
        'checked' => 0,
        'won' => 0,
        'lost' => 0,
        'total_won' => 0.0,
    ];

    $affectedFiches = [];
    $pdo->beginTransaction();

    try {
        $delete = $pdo->prepare('DELETE FROM gains WHERE tirage_id = ? AND is_paid = 0');
        $delete->execute([$tirageId]);

        $insert = $pdo->prepare('INSERT INTO gains (tenant_id, fiche_detail_id, tirage_id, amount_played, amount_won, status) VALUES (?, ?, ?, ?, ?, ?)');

        foreach ($details as $detail) {
            $summary['checked']++;
            $won = is_winning_detail($detail, $tirage);
            $rate = get_payout_rate($pdo, (string)$detail['play_type']);
            $amountWon = $won ? ((float)$detail['amount'] * $rate) : 0.0;
            $status = $won ? 'won' : 'lost';

            $insert->execute([
                $tirage['tenant_id'] ?? null,
                (int)$detail['id'],
                $tirageId,
                (float)$detail['amount'],
                $amountWon,
                $status,
            ]);

            $affectedFiches[(int)$detail['fiche_id']] = true;
            if ($won) {
                $summary['won']++;
                $summary['total_won'] += $amountWon;
            } else {
                $summary['lost']++;
            }
        }

        foreach (array_keys($affectedFiches) as $ficheId) {
            update_fiche_status_from_gains($pdo, (int)$ficheId);
        }

        $stmt = $pdo->prepare("UPDATE tirages SET status = 'processed', processed_at = NOW() WHERE id = ?");
        $stmt->execute([$tirageId]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    return $summary;
}
