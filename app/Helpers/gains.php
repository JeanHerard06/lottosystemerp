<?php
require_once __DIR__ . '/../Services/TimeService.php';
require_once __DIR__ . '/game_engine.php';

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
    ], static fn($n) => $n !== ''));
}

/**
 * Returns a configurable numeric game setting.
 * Scope priority: tenant+lottery > tenant default > global+lottery > global default.
 */
function get_game_setting_value(
    PDO $pdo,
    string $key,
    ?int $tenantId = null,
    ?int $lotteryId = null,
    float $default = 0.0
): float {
    $tenant = max(0, (int)($tenantId ?? 0));
    $lottery = max(0, (int)($lotteryId ?? 0));

    $stmt = $pdo->prepare(
        "SELECT setting_value
         FROM game_settings
         WHERE setting_key = ?
           AND (tenant_id = ? OR tenant_id = 0)
           AND (lottery_id = ? OR lottery_id = 0)
         ORDER BY
           CASE WHEN tenant_id = ? THEN 1 ELSE 0 END DESC,
           CASE WHEN lottery_id = ? THEN 1 ELSE 0 END DESC,
           id DESC
         LIMIT 1"
    );
    $stmt->execute([$key, $tenant, $lottery, $tenant, $lottery]);
    $value = $stmt->fetchColumn();

    return $value === false ? $default : (float)$value;
}

function get_borlette_payouts(PDO $pdo, ?int $tenantId = null, ?int $lotteryId = null): array
{
    return [
        1 => get_game_setting_value($pdo, 'payout_1', $tenantId, $lotteryId, 60.0),
        2 => get_game_setting_value($pdo, 'payout_2', $tenantId, $lotteryId, 20.0),
        3 => get_game_setting_value($pdo, 'payout_3', $tenantId, $lotteryId, 10.0),
    ];
}

function legacy_payout_rate(PDO $pdo, string $playType, float $default = 0.0): float
{
    $stmt = $pdo->prepare("SELECT payout_rate FROM primes WHERE game_type = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$playType]);
    $rate = $stmt->fetchColumn();

    if ($rate === false) {
        $stmt = $pdo->prepare('SELECT multiplier FROM rates WHERE play_type = ? LIMIT 1');
        $stmt->execute([$playType]);
        $rate = $stmt->fetchColumn();
    }

    return $rate === false || $rate === null || $rate === '' ? $default : (float)$rate;
}

/**
 * Configurable payout multipliers for the non-borlette games.
 * Scope priority is the same as get_game_setting_value():
 * tenant+lottery > tenant default > global+lottery > global default.
 */
function get_extended_game_payouts(PDO $pdo, ?int $tenantId = null, ?int $lotteryId = null): array
{
    $legacyMariage = legacy_payout_rate($pdo, 'mariage', 500.0);
    $legacyLotto3 = legacy_payout_rate($pdo, 'lotto3', 1000.0);
    $legacyLotto4 = legacy_payout_rate($pdo, 'lotto4', 5000.0);

    return [
        'mariage' => get_game_setting_value($pdo, 'payout_mariage', $tenantId, $lotteryId, $legacyMariage),
        'lotto3' => get_game_setting_value($pdo, 'payout_lotto3', $tenantId, $lotteryId, $legacyLotto3),
        'lotto4' => get_game_setting_value($pdo, 'payout_lotto4', $tenantId, $lotteryId, $legacyLotto4),
    ];
}

function get_payout_rate(
    PDO $pdo,
    string $playType,
    ?int $tenantId = null,
    ?int $lotteryId = null,
    ?int $winningPosition = null
): float {
    if ($playType === 'borlette' && $winningPosition !== null && $winningPosition >= 1 && $winningPosition <= 3) {
        $payouts = get_borlette_payouts($pdo, $tenantId, $lotteryId);
        return (float)($payouts[$winningPosition] ?? 0.0);
    }

    if (in_array($playType, ['mariage', 'lotto3', 'lotto4'], true)) {
        $payouts = get_extended_game_payouts($pdo, $tenantId, $lotteryId);
        return (float)($payouts[$playType] ?? 0.0);
    }

    return legacy_payout_rate($pdo, $playType, 0.0);
}

/**
 * Borlette payout position:
 * 1 = first drawn number, 2 = second drawn number, 3 = third drawn number.
 */
function borlette_winning_position(string $played, array $tirage): ?int
{
    $played = normalize_number($played);
    if ($played === '') {
        return null;
    }

    $draws = [
        1 => normalize_number($tirage['first_number'] ?? ''),
        2 => normalize_number($tirage['second_number'] ?? ''),
        3 => normalize_number($tirage['third_number'] ?? ''),
    ];

    foreach ($draws as $position => $number) {
        if ($number !== '' && hash_equals($number, $played)) {
            return $position;
        }
    }

    return null;
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
        return borlette_winning_position($played, $tirage) !== null;
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

/**
 * Calculates the payout for one fiche detail and returns auditable metadata.
 */
function calculate_detail_gain(PDO $pdo, array $detail, array $tirage): array
{
    $playType = game_engine_normalize_code((string)$detail['play_type']);
    $tenantId = isset($tirage['tenant_id']) ? (int)$tirage['tenant_id'] : null;
    $lotteryId = isset($tirage['lottery_id']) ? (int)$tirage['lottery_id'] : null;
    $game = game_engine_type($pdo, $playType, $tenantId, false);

    if ($game) {
        $match = game_engine_match($game, (string)$detail['number_played'], $tirage);
        $won = (bool)$match['won'];
        $position = $match['winning_position'];
        $matchLevel = $match['match_level'] ?: 'exact';
        $fallback = $won ? get_payout_rate($pdo, $playType, $tenantId, $lotteryId, $position) : 0.0;
        $multiplier = $won
            ? game_engine_payout_multiplier($pdo, $playType, $matchLevel, $tenantId, $lotteryId, $fallback)
            : 0.0;
    } else {
        $position = $playType === 'borlette'
            ? borlette_winning_position((string)$detail['number_played'], $tirage)
            : null;
        $won = $playType === 'borlette' ? $position !== null : is_winning_detail($detail, $tirage);
        $multiplier = $won ? get_payout_rate($pdo, $playType, $tenantId, $lotteryId, $position) : 0.0;
        $matchLevel = $position ? 'position_' . $position : 'exact';
    }

    $stake = (float)$detail['amount'];
    return [
        'won' => $won,
        'winning_position' => $position,
        'match_level' => $matchLevel,
        'multiplier' => $multiplier,
        'amount_won' => $won ? round($stake * $multiplier, 2) : 0.0,
    ];
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
        'by_position' => [1 => 0, 2 => 0, 3 => 0],
    ];

    $affectedFiches = [];
    $pdo->beginTransaction();

    try {
        $delete = $pdo->prepare('DELETE FROM gains WHERE tirage_id = ? AND is_paid = 0');
        $delete->execute([$tirageId]);

        $insert = $pdo->prepare(
            'INSERT INTO gains
             (tenant_id, fiche_detail_id, tirage_id, amount_played, amount_won, winning_position, payout_multiplier, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        foreach ($details as $detail) {
            $summary['checked']++;
            $calculation = calculate_detail_gain($pdo, $detail, $tirage);
            $status = $calculation['won'] ? 'won' : 'lost';

            $insert->execute([
                $tirage['tenant_id'] ?? null,
                (int)$detail['id'],
                $tirageId,
                (float)$detail['amount'],
                (float)$calculation['amount_won'],
                $calculation['winning_position'],
                (float)$calculation['multiplier'],
                $status,
            ]);

            $affectedFiches[(int)$detail['fiche_id']] = true;
            if ($calculation['won']) {
                $summary['won']++;
                $summary['total_won'] += (float)$calculation['amount_won'];
                if ($calculation['winning_position'] !== null) {
                    $summary['by_position'][(int)$calculation['winning_position']]++;
                }
            } else {
                $summary['lost']++;
            }
        }

        foreach (array_keys($affectedFiches) as $ficheId) {
            update_fiche_status_from_gains($pdo, (int)$ficheId);
        }

        $stmt = $pdo->prepare("UPDATE tirages SET status = 'processed', processed_at = ? WHERE id = ?");
        $stmt->execute([TimeService::sqlNow(), $tirageId]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    return $summary;
}
