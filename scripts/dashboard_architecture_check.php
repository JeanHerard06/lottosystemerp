<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$service = file_get_contents($root . '/app/Services/DashboardService.php');
$repository = file_get_contents($root . '/app/Repositories/DashboardRepository.php');
$component = $root . '/views/components/dashboard_components.php';

$checks = [
    'DashboardService has no SELECT SQL' => stripos($service, 'SELECT ') === false,
    'DashboardRepository owns SELECT SQL' => stripos($repository, 'SELECT ') !== false,
    'Shared Mobile financial engine is used' => strpos($service, 'mobile_agent_dashboard_metrics') !== false,
    'Dashboard component exists' => is_file($component),
];

$failed = false;
foreach ($checks as $label => $ok) {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
    if (!$ok) $failed = true;
}

exit($failed ? 1 : 0);
