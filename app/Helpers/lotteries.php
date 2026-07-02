<?php

function lottery_close_datetime(array $lottery, ?string $date = null): ?DateTime
{
    if (empty($lottery['draw_time'])) {
        return null;
    }
    $drawDate = $date ?: date('Y-m-d');
    $drawAt = new DateTime($drawDate . ' ' . $lottery['draw_time']);
    $closeAt = clone $drawAt;
    $minutes = isset($lottery['close_before_minutes']) ? (int)$lottery['close_before_minutes'] : 10;
    if ($minutes > 0) {
        $closeAt->modify('-' . $minutes . ' minutes');
    }
    return $closeAt;
}

function lottery_draw_datetime(array $lottery, ?string $date = null): ?DateTime
{
    if (empty($lottery['draw_time'])) {
        return null;
    }
    return new DateTime(($date ?: date('Y-m-d')) . ' ' . $lottery['draw_time']);
}

function lottery_auto_close_if_due(PDO $pdo, array $lottery): array
{
    $autoClose = (int)($lottery['auto_close_enabled'] ?? 1) === 1;
    $salesStatus = $lottery['sales_status'] ?? 'open';
    $closeAt = lottery_close_datetime($lottery);

    if ($autoClose && $salesStatus === 'open' && $closeAt && new DateTime() >= $closeAt) {
        $stmt = $pdo->prepare("UPDATE lotteries SET sales_status='closed', closed_at=NOW(), closed_by=NULL WHERE id=? AND sales_status='open'");
        $stmt->execute([(int)$lottery['id']]);
        $lottery['sales_status'] = 'closed';
        $lottery['closed_at'] = date('Y-m-d H:i:s');
        $lottery['closed_by'] = null;
    }

    return $lottery;
}

function get_lottery_for_sale(PDO $pdo, ?int $lotteryId, ?int $tenantId = null): ?array
{
    if (!$lotteryId) {
        return null;
    }

    $sql = 'SELECT * FROM lotteries WHERE id=? AND status=1';
    $params = [$lotteryId];

    if ($tenantId && !(function_exists('is_super_admin') && is_super_admin())) {
        $sql .= ' AND (tenant_id=? OR tenant_id IS NULL)';
        $params[] = $tenantId;
    }

    $stmt = $pdo->prepare($sql . ' LIMIT 1');
    $stmt->execute($params);
    $lottery = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$lottery) {
        return null;
    }

    return lottery_auto_close_if_due($pdo, $lottery);
}

function validate_lottery_sale_open(PDO $pdo, ?int $lotteryId, ?int $tenantId = null): void
{
    if (!$lotteryId) {
        return;
    }

    $lottery = get_lottery_for_sale($pdo, $lotteryId, $tenantId);
    if (!$lottery) {
        throw new RuntimeException('Lotterie inactive, introuvable ou hors tenant.');
    }

    $salesStatus = $lottery['sales_status'] ?? 'open';
    if ($salesStatus !== 'open') {
        $label = $salesStatus === 'drawn' ? 'déjà tirée' : 'fermée';
        throw new RuntimeException('Vente refusée: la lotterie ' . ($lottery['name'] ?? '') . ' est ' . $label . '.');
    }

    $closeAt = lottery_close_datetime($lottery);
    if ($closeAt && new DateTime() >= $closeAt) {
        $stmt = $pdo->prepare("UPDATE lotteries SET sales_status='closed', closed_at=NOW(), closed_by=NULL WHERE id=? AND sales_status='open'");
        $stmt->execute([(int)$lottery['id']]);
        throw new RuntimeException('Vente refusée: l’heure limite de vente est dépassée pour ' . ($lottery['name'] ?? 'cette lotterie') . '.');
    }
}

function lottery_status_badge_class(string $status): string
{
    return match ($status) {
        'open' => 'bg-green-100 text-green-800',
        'closed' => 'bg-yellow-100 text-yellow-800',
        'drawn' => 'bg-blue-100 text-blue-800',
        default => 'bg-gray-100 text-gray-800',
    };
}

function lottery_sales_status_label(string $status): string
{
    return match ($status) {
        'open' => 'Ouverte',
        'closed' => 'Fermée',
        'drawn' => 'Tirée',
        default => ucfirst($status),
    };
}
