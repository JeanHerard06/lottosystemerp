<?php
require_once __DIR__ . '/../app/Helpers/settings.php';
require_once __DIR__ . '/../app/Helpers/notifications.php';
$branding = current_branding($pdo);
$unreadNotifications = function_exists('unread_notifications_count') ? unread_notifications_count($pdo) : 0;
?>
<main class="flex-1">
<header class="bg-white shadow p-4 md:p-5 flex justify-between items-center gap-3 sticky top-0 z-30">
    <div class="flex items-center gap-3 min-w-0">
        <button type="button" onclick="openMobileSidebar()" class="md:hidden bg-black text-white rounded-lg px-3 py-2 flex items-center justify-center" aria-label="Ouvrir le menu">
            ☰
        </button>
        <?php if (!empty($branding['logo_path'])): ?>
            <img src="<?= e($branding['logo_path']) ?>" alt="Logo" class="h-10 w-10 object-contain rounded">
        <?php endif; ?>
        <div>
            <h2 class="text-lg md:text-xl font-bold truncate"><?= e($branding['business_name'] ?? 'MCS Lotto Enterprise') ?></h2>
            <p class="text-gray-500 text-sm truncate">Bienvenue, <?= e($_SESSION['name'] ?? 'Utilisateur') ?></p>
        </div>
    </div>
    <div class="flex items-center gap-2 md:gap-3 shrink-0">
        <?php if (function_exists('has_permission') && has_permission($pdo, 'notifications.view')): ?>
            <a href="/views/notifications/index.php" class="relative bg-gray-100 px-3 md:px-4 py-2 rounded font-semibold text-gray-700 text-sm">
                Notifications
                <?php if ($unreadNotifications > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full px-2 py-0.5"><?= (int)$unreadNotifications ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
        <div class="hidden sm:block bg-yellow-100 text-yellow-800 px-4 py-2 rounded font-semibold text-sm">
            <?= e(strtoupper($_SESSION['role'] ?? '')) ?>
        </div>
    </div>
</header>
<div class="p-4 md:p-6">
