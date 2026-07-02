<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../app/Helpers/security.php';
require_once __DIR__ . '/../app/Helpers/permissions.php';
require_once __DIR__ . '/../app/Helpers/settings.php';
require_once __DIR__ . '/../app/Core/Autoload.php';

require_permission($pdo, 'fiches.view');

$id = (int)($_GET['id'] ?? 0);
[$fiche, $details] = (new TicketService($pdo))->loadPrintableFiche($id);
$branding = current_branding($pdo);

include __DIR__ . '/../views/ticket.php';
