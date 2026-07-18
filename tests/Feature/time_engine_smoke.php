<?php
require_once __DIR__ . '/../../app/Services/TimeService.php';
require_once __DIR__ . '/../../app/Services/LotteryTimeService.php';
date_default_timezone_set('America/Port-au-Prince');
$ref = new ReflectionClass(TimeService::class);
$prop = $ref->getProperty('timezone');
$prop->setAccessible(true);
$prop->setValue(null, 'America/Port-au-Prince');
$lottery = ['draw_time' => '13:30:00', 'close_before_minutes' => 10, 'sales_status' => 'open'];
$close = LotteryTimeService::closeAt($lottery, '2026-07-11');
if (!$close || $close->format('H:i:s') !== '13:20:00') { fwrite(STDERR, "Time engine failed\n"); exit(1); }
echo "Time engine OK: " . $close->format(DateTimeInterface::ATOM) . PHP_EOL;
