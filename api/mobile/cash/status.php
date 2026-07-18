<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../../app/Helpers/cash_sessions.php';
$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);
$session = open_cash_session($pdo, (int)$agent['id']);
$totals = null;
if ($session) {
    $totals = cash_session_totals($pdo, (int)$session['id']);
    $totals['expected_amount'] = cash_expected_amount((float)$session['opening_amount'], $totals);
}
mobile_json(['success' => true, 'session' => $session ?: null, 'totals' => $totals]);
