<?php
// Run from CLI: php cron/auto_close_lotteries.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Services/TimeService.php';
require_once __DIR__ . '/../app/Services/LotteryTimeService.php';

TimeService::boot($pdo, null);
$startedAt = TimeService::sqlNow();
$closed = 0;
$status = 'success';
$message = '';

try {
    $sql = "SELECT l.id, l.name, l.tenant_id, l.draw_time, l.close_before_minutes,
                   l.sales_status, l.auto_close_enabled,
                   s.day_of_week, s.draw_time AS schedule_draw_time,
                   s.close_before_minutes AS schedule_close_before_minutes,
                   s.sales_close_time
            FROM lotteries l
            LEFT JOIN lottery_schedules s
              ON s.lottery_id = l.id
             AND (s.tenant_id = l.tenant_id OR s.tenant_id IS NULL)
             AND s.status = 'active'
            WHERE l.sales_status = 'open'
              AND l.auto_close_enabled = 1
            ORDER BY l.tenant_id, l.id";

    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $update = $pdo->prepare("UPDATE lotteries SET sales_status='closed', closed_at=? WHERE id=? AND sales_status='open'");

    foreach ($rows as $row) {
        $tenantId = !empty($row['tenant_id']) ? (int)$row['tenant_id'] : null;
        TimeService::boot($pdo, $tenantId);
        $now = TimeService::now();

        if ($row['day_of_week'] !== null && (int)$row['day_of_week'] !== (int)$now->format('w')) {
            continue;
        }

        $drawTime = trim((string)($row['schedule_draw_time'] ?: $row['draw_time']));
        if ($drawTime === '') {
            continue;
        }

        $lotteryForTime = $row;
        $lotteryForTime['draw_time'] = $drawTime;
        $lotteryForTime['close_before_minutes'] = (int)($row['schedule_close_before_minutes'] ?? $row['close_before_minutes'] ?? 10);

        $closeAt = null;
        if (!empty($row['sales_close_time'])) {
            $closeAt = TimeService::at(TimeService::today(), (string)$row['sales_close_time']);
        } else {
            $closeAt = LotteryTimeService::closeAt($lotteryForTime);
        }

        if ($closeAt && $now >= $closeAt) {
            $update->execute([TimeService::sqlNow(), (int)$row['id']]);
            $closed += $update->rowCount();
        }
    }

    $message = 'Lotteries fermées automatiquement: ' . $closed;
} catch (Throwable $e) {
    $status = 'failed';
    $message = $e->getMessage();
}

try {
    TimeService::boot($pdo, null);
    $stmt = $pdo->prepare('INSERT INTO cron_runs(job_name, status, message, started_at, finished_at) VALUES(?, ?, ?, ?, ?)');
    $stmt->execute(['auto_close_lotteries', $status, $message, $startedAt, TimeService::sqlNow()]);
} catch (Throwable) {
    // Cron logging must never hide the actual job result.
}

echo $message . PHP_EOL;
