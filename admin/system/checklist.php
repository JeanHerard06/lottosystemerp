<?php
/**
 * Lotto ERP Enterprise - Manual RC1 Browser Test Checklist
 * Copy to: admin/system/checklist.php
 */
$sections = [
    'Login & Security' => ['Login super_admin', 'Login tenant_admin', 'Login superviseur', 'Login agent', 'Bad password rejected', 'Suspended tenant blocked'],
    'Tenant Isolation' => ['Tenant A cannot see Tenant B users', 'Tenant A cannot see Tenant B agencies', 'Tenant A cannot see Tenant B tickets', 'Agent sees only own tickets'],
    'Users/Roles' => ['Create user', 'Edit user', 'Reset password', 'Tenant cannot assign super_admin', 'Sidebar follows permissions'],
    'Lottery' => ['Create lottery', 'Set schedule', 'Manual close', 'Manual reopen', 'Auto close via cron', 'Cannot sell after close time'],
    'Fiches/Tickets' => ['Create fiche', 'Multiple lines', 'Print ticket 80mm', 'Reprint ticket', 'Cancel fiche with permission'],
    'Gains' => ['Enter tirage result', 'Calculate gains', 'Pay gain', 'Prevent double payment'],
    'Cash Sessions' => ['Open session', 'Sell with session open', 'Block sale without session', 'Close session', 'Supervisor approval'],
    'Reports' => ['Daily report', 'Monthly report', 'Agent report', 'Tenant report', 'Export CSV/PDF if available'],
    'Notifications' => ['Notification appears', 'Mark as read', 'Delete notification', 'Tenant isolation'],
    'Mobile/API' => ['API login', 'API create fiche', 'Mobile dashboard', 'API tenant isolation'],
];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>RC1 Test Checklist</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f3f4f6;margin:0;padding:30px;color:#111827}
        .box{max-width:1100px;margin:auto;background:white;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.08);padding:24px}
        h1{margin-top:0}.section{margin:22px 0}.section h2{font-size:18px;background:#111827;color:white;padding:10px 14px;border-radius:8px}
        label{display:block;padding:9px 4px;border-bottom:1px solid #e5e7eb}.muted{color:#6b7280;font-size:14px}
        input{margin-right:8px;transform:scale(1.1)}
    </style>
</head>
<body>
<div class="box">
    <h1>RC1 Browser Test Checklist</h1>
    <p class="muted">Sèvi ak paj sa a pandan w ap teste nan navigatè. Li pa sove done; li se yon checklist vizyèl.</p>
    <?php foreach ($sections as $title => $items): ?>
        <div class="section">
            <h2><?= htmlspecialchars($title) ?></h2>
            <?php foreach ($items as $item): ?>
                <label><input type="checkbox"> <?= htmlspecialchars($item) ?></label>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
