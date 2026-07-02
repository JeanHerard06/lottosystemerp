<?php
// Run from CLI: php cron/auto_close_lotteries.php
require_once __DIR__ . '/../config/database.php';

$startedAt = date('Y-m-d H:i:s');
$closed = 0;
$status = 'success';
$message = '';
try {
    $nowTime = date('H:i:s');
    $day = (int)date('w');
    $sql = "SELECT l.id, l.name, l.tenant_id, s.close_before_minutes, s.draw_time,
                   COALESCE(s.sales_close_time, TIME(DATE_SUB(CONCAT(CURDATE(),' ',s.draw_time), INTERVAL s.close_before_minutes MINUTE))) AS close_time
            FROM lotteries l
            JOIN lottery_schedules s ON s.lottery_id=l.id AND s.tenant_id=l.tenant_id
            WHERE l.sales_status='open'
              AND l.auto_close_enabled=1
              AND s.status='active'
              AND (s.day_of_week IS NULL OR s.day_of_week=?)
              AND ? >= COALESCE(s.sales_close_time, TIME(DATE_SUB(CONCAT(CURDATE(),' ',s.draw_time), INTERVAL s.close_before_minutes MINUTE)))";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$day, $nowTime]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $update = $pdo->prepare("UPDATE lotteries SET sales_status='closed', closed_at=NOW() WHERE id=? AND sales_status='open'");
    foreach ($rows as $row) {
        $update->execute([(int)$row['id']]);
        $closed += $update->rowCount();
    }
    $message = "Lotteries fermées automatiquement: " . $closed;
} catch (Throwable $e) {
    $status = 'failed';
    $message = $e->getMessage();
}

try {
    $stmt = $pdo->prepare('INSERT INTO cron_runs(job_name, status, message, started_at, finished_at) VALUES(?, ?, ?, ?, NOW())');
    $stmt->execute(['auto_close_lotteries', $status, $message, $startedAt]);
} catch (Throwable $e) {}

echo $message . PHP_EOL;
