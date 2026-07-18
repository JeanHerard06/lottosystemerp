<?php
$root = realpath(__DIR__ . '/..');
require_once $root . '/app/Helpers/csrf.php';
$errors = [];
$warnings = [];
$actionCount = 0;

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/actions'));
foreach ($it as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') { continue; }
    $actionCount++;
    $path = $file->getPathname();
    $rel = str_replace($root . DIRECTORY_SEPARATOR, '', $path);
    $code = file_get_contents($path) ?: '';
    $usesPost = str_contains($code, '$_POST') || str_contains($code, "REQUEST_METHOD");
    if ($usesPost && !str_contains($code, 'require_post()') && !str_contains($code, "REQUEST_METHOD")) {
        $warnings[] = "$rel: POST method guard missing";
    }
    if ($usesPost && !str_contains($code, 'verify_csrf()') && !str_contains($code, 'csrf_verify()')
        && !str_contains($code, "require __DIR__ . '/blocages/store.php'")
        && !str_contains($code, "require __DIR__ . '/limites/store.php'")) {
        $errors[] = "$rel: CSRF verification missing";
    }
    if (preg_match('/\$_(GET|POST|REQUEST)\[[^\]]+\].*(SELECT|UPDATE|DELETE|INSERT)/is', $code)) {
        $warnings[] = "$rel: review direct request data near SQL";
    }
}

$checks = [
    'csrf alias' => function_exists('csrf_verify'),
];

echo "RC1.3 SECURITY AUDIT\n";
echo "Actions scanned: {$actionCount}\n";
foreach ($checks as $label => $ok) { echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . "\n"; }
foreach ($warnings as $w) { echo "[WARN] $w\n"; }
foreach ($errors as $e) { echo "[FAIL] $e\n"; }
exit($errors ? 1 : 0);
