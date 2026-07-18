<?php
require_once dirname(__DIR__) . '/app/Services/TimeService.php';
require_once dirname(__DIR__) . '/app/Services/LotteryTimeService.php';

TimeService::configure('America/Port-au-Prince');

$failures = [];
if (TimeService::timezone() !== 'America/Port-au-Prince') {
    $failures[] = 'Timezone resolution failed.';
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', TimeService::today())) {
    $failures[] = 'today() format invalid.';
}
if (TimeService::normalizeDate('invalid') !== TimeService::today()) {
    $failures[] = 'normalizeDate fallback failed.';
}

$lottery = [
    'draw_time' => '13:30:00',
    'close_before_minutes' => 10,
    'sales_status' => 'open',
];
$closeAt = LotteryTimeService::closeAt($lottery, '2026-07-12');
if (!$closeAt || $closeAt->format('H:i:s') !== '13:20:00') {
    $failures[] = 'Lottery close calculation failed.';
}

if ($failures) {
    foreach ($failures as $failure) echo "FAIL: {$failure}\n";
    exit(1);
}

echo "Timezone smoke test: PASS\n";
echo json_encode(TimeService::meta(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
