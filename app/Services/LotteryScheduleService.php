<?php
require_once __DIR__ . '/../Helpers/tenant.php';

class LotteryScheduleService
{
    public static function currentSchedule(PDO $pdo, int $lotteryId, ?int $tenantId = null, ?DateTimeInterface $now = null): ?array
    {
        $tenantId = $tenantId ?? current_tenant_id();
        if (!$tenantId) { return null; }
        $now = $now ?: new DateTimeImmutable('now');
        $day = (int)$now->format('w');

        $stmt = $pdo->prepare("\n            SELECT * FROM lottery_schedules\n            WHERE tenant_id=? AND lottery_id=? AND status='active'\n              AND (day_of_week IS NULL OR day_of_week=?)\n            ORDER BY day_of_week IS NULL DESC, draw_time ASC\n        ");
        $stmt->execute([$tenantId, $lotteryId, $day]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) { return null; }

        $today = $now->format('Y-m-d');
        foreach ($rows as $row) {
            $drawAt = new DateTimeImmutable($today . ' ' . $row['draw_time']);
            $closeAt = $row['sales_close_time']
                ? new DateTimeImmutable($today . ' ' . $row['sales_close_time'])
                : $drawAt->modify('-' . (int)$row['close_before_minutes'] . ' minutes');
            $openAt = $row['sales_open_time'] ? new DateTimeImmutable($today . ' ' . $row['sales_open_time']) : null;
            $row['_draw_at'] = $drawAt->format('Y-m-d H:i:s');
            $row['_close_at'] = $closeAt->format('Y-m-d H:i:s');
            $row['_open_at'] = $openAt ? $openAt->format('Y-m-d H:i:s') : null;
            return $row;
        }
        return null;
    }

    public static function salesAreOpen(PDO $pdo, array $lottery, ?DateTimeInterface $now = null): array
    {
        $now = $now ?: new DateTimeImmutable('now');
        if (($lottery['sales_status'] ?? 'open') !== 'open') {
            return [false, 'Vente fermée pour cette lottery.'];
        }
        $schedule = self::currentSchedule($pdo, (int)$lottery['id'], (int)($lottery['tenant_id'] ?? current_tenant_id()), $now);
        if ($schedule) {
            if (!empty($schedule['_open_at']) && $now < new DateTimeImmutable($schedule['_open_at'])) {
                return [false, 'Vente pas encore ouverte pour cette lottery.'];
            }
            if ($now >= new DateTimeImmutable($schedule['_close_at'])) {
                return [false, 'Vente fermée: heure limite atteinte.'];
            }
        }
        return [true, 'OK'];
    }
}
