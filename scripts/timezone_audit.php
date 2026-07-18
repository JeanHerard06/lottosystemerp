<?php
/**
 * Static timezone audit.
 * Run: php scripts/timezone_audit.php
 */
$root = dirname(__DIR__);
$targets = ['api/mobile', 'app/Helpers', 'app/Services', 'cron', 'actions', 'views'];
$patterns = [
    '/\bdate\s*\(/i' => 'direct date()',
    '/\btime\s*\(/i' => 'direct time()',
    '/\bstrtotime\s*\(/i' => 'direct strtotime()',
    '/\bNOW\s*\(\s*\)/i' => 'SQL NOW()',
    '/\bCURDATE\s*\(\s*\)/i' => 'SQL CURDATE()',
];
$ignored = [
    'app/Helpers/migrations.php',
    'scripts/timezone_audit.php',
];
$findings = [];
foreach ($targets as $target) {
    $base = $root . '/' . $target;
    if (!is_dir($base)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') continue;
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        if (in_array($relative, $ignored, true)) continue;
        $lines = file($file->getPathname());
        foreach ($lines as $number => $line) {
            foreach ($patterns as $pattern => $label) {
                if (preg_match($pattern, $line)) {
                    $findings[] = [$relative, $number + 1, $label, trim($line)];
                }
            }
        }
    }
}

if (!$findings) {
    echo "Timezone audit: PASS\n";
    exit(0);
}

echo "Timezone audit: " . count($findings) . " usage(s) remaining for review.\n";
foreach ($findings as [$file, $line, $label, $code]) {
    echo "- {$file}:{$line} [{$label}] {$code}\n";
}
exit(0); // Informational until all legacy modules are migrated.
