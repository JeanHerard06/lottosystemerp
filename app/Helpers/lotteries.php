<?php

require_once __DIR__ . '/../Services/TimeService.php';
require_once __DIR__ . '/../Services/LotteryTimeService.php';

function lottery_close_datetime(array $lottery, ?string $date = null): ?DateTimeImmutable
{
    return LotteryTimeService::closeAt($lottery, $date);
}

function lottery_draw_datetime(array $lottery, ?string $date = null): ?DateTimeImmutable
{
    return LotteryTimeService::drawAt($lottery, $date);
}

function lottery_auto_close_if_due(PDO $pdo, array $lottery): array
{
    $autoClose = (int)($lottery['auto_close_enabled'] ?? 1) === 1;
    if ($autoClose && LotteryTimeService::isSalesClosed($lottery) && ($lottery['sales_status'] ?? 'open') === 'open') {
        $closedAt = TimeService::sqlNow();
        $stmt = $pdo->prepare("UPDATE lotteries SET sales_status='closed', closed_at=?, closed_by=NULL WHERE id=? AND sales_status='open'");
        $stmt->execute([$closedAt, (int)$lottery['id']]);
        $lottery['sales_status'] = 'closed';
        $lottery['closed_at'] = $closedAt;
        $lottery['closed_by'] = null;
    }
    return $lottery;
}

function get_lottery_for_sale(PDO $pdo, ?int $lotteryId, ?int $tenantId = null): ?array
{
    if (!$lotteryId) return null;
    $sql = 'SELECT * FROM lotteries WHERE id=? AND status=1';
    $params = [$lotteryId];
    if ($tenantId && !(function_exists('is_super_admin') && is_super_admin())) {
        $sql .= ' AND (tenant_id=? OR tenant_id IS NULL)';
        $params[] = $tenantId;
    }
    $stmt = $pdo->prepare($sql . ' LIMIT 1');
    $stmt->execute($params);
    $lottery = $stmt->fetch(PDO::FETCH_ASSOC);
    return $lottery ? lottery_auto_close_if_due($pdo, $lottery) : null;
}

function validate_lottery_sale_open(PDO $pdo, ?int $lotteryId, ?int $tenantId = null): void
{
    if (!$lotteryId) return;
    $lottery = get_lottery_for_sale($pdo, $lotteryId, $tenantId);
    if (!$lottery) throw new RuntimeException('Lotterie inactive, introuvable ou hors tenant.');
    $status = $lottery['sales_status'] ?? 'open';
    if ($status !== 'open') {
        throw new RuntimeException('Vente refusée: la lotterie ' . ($lottery['name'] ?? '') . ' est ' . ($status === 'drawn' ? 'déjà tirée' : 'fermée') . '.');
    }
    if (LotteryTimeService::isSalesClosed($lottery)) {
        $closedAt = TimeService::sqlNow();
        $stmt = $pdo->prepare("UPDATE lotteries SET sales_status='closed', closed_at=?, closed_by=NULL WHERE id=? AND sales_status='open'");
        $stmt->execute([$closedAt, (int)$lottery['id']]);
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
