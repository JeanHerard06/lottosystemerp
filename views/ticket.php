<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ticket <?= e($fiche['fiche_code'] ?? '') ?></title>
<style>
@page { size: 80mm auto; margin: 0; }
body { width: 72mm; font-family: Arial, sans-serif; font-size: 12px; margin: 0 auto; color: #000; }
.center { text-align: center; }
.bold { font-weight: bold; }
.row { display: flex; justify-content: space-between; gap: 8px; }
.small { font-size: 10px; }
.logo { max-width: 36mm; max-height: 18mm; margin: 2px auto; display:block; }
hr { border: none; border-top: 1px dashed #999; margin: 6px 0; }
.cancelled { border: 2px solid #000; padding: 6px; margin: 6px 0; font-weight: bold; text-align:center; }
</style>
</head>
<body>
<div class="center">
    <?php if (!empty($branding['logo_path'])): ?>
        <img src="<?= e($branding['logo_path']) ?>" class="logo" alt="Logo">
    <?php endif; ?>
    <h2 style="margin:4px 0;"><?= e($branding['business_name'] ?? 'MCS LOTTO') ?></h2>
    <div class="small"><?= e($branding['ticket_subtitle'] ?? 'Système de gestion bòlèt') ?></div>
    <?php if (!empty($branding['business_address'])): ?><div class="small"><?= e($branding['business_address']) ?></div><?php endif; ?>
    <?php if (!empty($branding['business_phone'])): ?><div class="small">Tél: <?= e($branding['business_phone']) ?></div><?php endif; ?>
</div>
<hr>
<div class="row"><span>Fiche</span><span class="bold"><?= e($fiche['fiche_code']) ?></span></div>
<div class="row"><span>Agent</span><span><?= e($fiche['agent_name']) ?></span></div>
<div class="row"><span>Tirage</span><span><?= e($fiche['lottery_name'] ?? '-') ?></span></div>
<div class="row"><span>Date</span><span><?= e($fiche['created_at']) ?></span></div>
<?php if (($fiche['status'] ?? '') === 'cancelled'): ?><div class="cancelled">TICKET ANNULÉ</div><?php endif; ?>
<hr>
<?php foreach ($details as $d): ?>
<div class="row">
    <span><?= e(strtoupper($d['play_type'])) ?> - <?= e($d['number_played']) ?></span>
    <span><?= number_format((float)$d['amount'], 2) ?></span>
</div>
<?php endforeach; ?>
<hr>
<div class="row bold"><span>Total</span><span><?= e($branding['currency'] ?? 'HTG') ?> <?= number_format((float)$fiche['total_amount'], 2) ?></span></div>
<div class="row"><span>Statut</span><span><?= e($fiche['status']) ?></span></div>
<hr>
<div class="center small">
    <?= nl2br(e($branding['ticket_footer'] ?? 'Conservez ce reçu. Aucun paiement sans validation.')) ?><br>
    Bonne chance !
</div>
<script>window.onload = function(){ window.print(); };</script>
</body>
</html>
