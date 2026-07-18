<?php

if (!function_exists('game_engine_normalize_code')) {
    function game_engine_normalize_code(string $code): string
    {
        return strtolower(trim(preg_replace('/[^a-zA-Z0-9_\-]/', '', $code)));
    }
}

function game_engine_types(PDO $pdo, ?int $tenantId = null, bool $enabledOnly = true): array
{
    $tenant = max(0, (int)($tenantId ?? 0));
    $sql = "SELECT * FROM game_types WHERE tenant_id IN (0, ?)";
    $sql .= " ORDER BY CASE WHEN tenant_id = ? THEN 0 ELSE 1 END, display_order, name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tenant, $tenant]);

    // Tenant-specific definitions override global definitions with the same code.
    $byCode = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $code = game_engine_normalize_code((string)$row['code']);
        if ($code !== '' && !isset($byCode[$code])) {
            $row['code'] = $code;
            $byCode[$code] = $row;
        }
    }
    if ($enabledOnly) {
        $byCode = array_filter($byCode, static fn(array $row): bool => (int)$row['enabled'] === 1);
    }
    uasort($byCode, static fn(array $a, array $b): int => ((int)$a['display_order'] <=> (int)$b['display_order']) ?: strcmp((string)$a['name'], (string)$b['name']));
    return array_values($byCode);
}

function game_engine_type(PDO $pdo, string $code, ?int $tenantId = null, bool $enabledOnly = true): ?array
{
    $code = game_engine_normalize_code($code);
    foreach (game_engine_types($pdo, $tenantId, $enabledOnly) as $row) {
        if ($row['code'] === $code) {
            return $row;
        }
    }
    return null;
}

function game_engine_validate_play(PDO $pdo, string $code, string $number, ?int $tenantId = null): ?string
{
    $game = game_engine_type($pdo, $code, $tenantId, true);
    if (!$game) {
        return 'Kalite jwèt la pa aktif oswa li pa egziste.';
    }
    $number = trim($number);
    $pattern = trim((string)($game['validation_pattern'] ?? ''));
    if ($pattern !== '') {
        $delimiter = '~';
        $regex = $delimiter . str_replace($delimiter, '\\' . $delimiter, $pattern) . $delimiter . 'u';
        if (@preg_match($regex, '') === false) {
            return 'Règ validation jwèt la pa valid. Kontakte administratè a.';
        }
        if (!preg_match($regex, $number)) {
            return (string)($game['input_hint'] ?: 'Nimewo a pa respekte règ jwèt la.');
        }
    } else {
        $digits = preg_replace('/\D/', '', $number);
        $len = strlen($digits);
        if ($len < (int)$game['min_digits'] || $len > (int)$game['max_digits']) {
            return sprintf('Nimewo a dwe genyen ant %d ak %d chif.', (int)$game['min_digits'], (int)$game['max_digits']);
        }
    }
    return null;
}

function game_engine_payout_multiplier(
    PDO $pdo,
    string $gameCode,
    string $matchLevel = 'exact',
    ?int $tenantId = null,
    ?int $lotteryId = null,
    float $default = 0.0
): float {
    $tenant = max(0, (int)($tenantId ?? 0));
    $lottery = max(0, (int)($lotteryId ?? 0));
    $stmt = $pdo->prepare(
        "SELECT multiplier
         FROM game_payout_rules
         WHERE game_code = ? AND match_level = ? AND enabled = 1
           AND tenant_id IN (0, ?) AND lottery_id IN (0, ?)
         ORDER BY CASE WHEN tenant_id = ? THEN 1 ELSE 0 END DESC,
                  CASE WHEN lottery_id = ? THEN 1 ELSE 0 END DESC,
                  id DESC
         LIMIT 1"
    );
    $stmt->execute([game_engine_normalize_code($gameCode), $matchLevel, $tenant, $lottery, $tenant, $lottery]);
    $value = $stmt->fetchColumn();
    return $value === false ? $default : (float)$value;
}

function game_engine_match(array $game, string $playedRaw, array $tirage): array
{
    $played = preg_replace('/\D/', '', $playedRaw);
    $numbers = draw_numbers($tirage);
    $engine = (string)($game['matching_engine'] ?? 'exact_first');

    if ($played === '' || !$numbers) {
        return ['won' => false, 'match_level' => null, 'winning_position' => null];
    }

    if ($engine === 'borlette_position') {
        $position = borlette_winning_position($played, $tirage);
        return ['won' => $position !== null, 'match_level' => $position ? 'position_' . $position : null, 'winning_position' => $position];
    }

    if ($engine === 'marriage_any') {
        $parts = preg_split('/[^0-9]+/', trim($playedRaw), -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) < 2 && strlen($played) >= 4) {
            $parts = [substr($played, 0, 2), substr($played, 2, 2)];
        }
        $a = isset($parts[0]) ? preg_replace('/\D/', '', $parts[0]) : '';
        $b = isset($parts[1]) ? preg_replace('/\D/', '', $parts[1]) : '';
        $won = $a !== '' && $b !== '' && $a !== $b && in_array($a, $numbers, true) && in_array($b, $numbers, true);
        return ['won' => $won, 'match_level' => $won ? 'exact' : null, 'winning_position' => null];
    }

    if ($engine === 'exact_sequence3') {
        $combo = implode('', array_slice($numbers, 0, 3));
        $won = $played === $combo;
        return ['won' => $won, 'match_level' => $won ? 'exact' : null, 'winning_position' => null];
    }

    if ($engine === 'any_draw') {
        $won = in_array($played, $numbers, true);
        return ['won' => $won, 'match_level' => $won ? 'exact' : null, 'winning_position' => null];
    }

    // exact_first: exact match with the first official result.
    $first = preg_replace('/\D/', '', (string)($tirage['first_number'] ?? ''));
    $won = $first !== '' && hash_equals($first, $played);
    return ['won' => $won, 'match_level' => $won ? 'exact' : null, 'winning_position' => null];
}
