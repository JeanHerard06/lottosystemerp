<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$requiredFiles = [
    'views/components/ui_components.php',
    'public/assets/css/app.css',
    'public/assets/js/app.js',
];
$errors = [];
foreach ($requiredFiles as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Fichier manquant: ' . $file;
    }
}

$componentSource = @file_get_contents($root . '/views/components/ui_components.php') ?: '';
foreach (['ui_page_header','ui_stat_card','ui_status_badge','ui_empty_state','ui_action_link'] as $function) {
    if (!str_contains($componentSource, 'function ' . $function . '(')) {
        $errors[] = 'Composant manquant: ' . $function;
    }
}

$pages = [
    'views/fiches.php',
    'views/gagnants.php',
    'views/agents.php',
    'views/agencies/index.php',
    'views/cash_sessions/index.php',
    'views/commissions/index.php',
];
foreach ($pages as $page) {
    $source = @file_get_contents($root . '/' . $page) ?: '';
    if (!str_contains($source, 'ui_page_header(')) {
        $errors[] = 'Page non migrée vers ui_page_header: ' . $page;
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo "RC1.2 UI component check: PASS\n";
