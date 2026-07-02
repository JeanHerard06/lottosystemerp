<?php
// Compatibilité ancienne URL: calcule les gains du dernier tirage.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../app/Helpers/security.php';
require_once __DIR__ . '/../app/Helpers/permissions.php';
require_once __DIR__ . '/../app/Helpers/audit.php';
require_once __DIR__ . '/../app/Helpers/gains.php';

require_permission($pdo, 'gains.calculate');

$tirage = $pdo->query('SELECT id FROM tirages ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
if (!$tirage) {
    die('Aucun tirage disponible.');
}

$summary = calculate_tirage_gains($pdo, (int)$tirage['id']);
audit_log($pdo, current_user_id(), 'CALCULATE_GAINS', 'Calcul gains dernier tirage #' . $tirage['id']);

header('Location: ../views/tirages/show.php?id=' . (int)$tirage['id'] . '&calculated=1');
exit;
