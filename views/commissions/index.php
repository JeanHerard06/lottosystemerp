<?php
require_once "../../config/database.php";
require_once "../../includes/header.php";
require_once "../../app/Helpers/permissions.php";
require_once "../../app/Helpers/tenant.php";
require_once "../../app/Helpers/game_engine.php";
require_permission($pdo, 'commissions.manage');
require_once "../../includes/sidebar.php";
require_once "../../includes/topbar.php";

if (is_super_admin()) {
    $agents = $pdo->query("SELECT a.id,u.name FROM agents a JOIN users u ON u.id=a.user_id ORDER BY u.name")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $tenantId = current_tenant_id() ?? 0;
    $stmt = $pdo->prepare("SELECT a.id,u.name FROM agents a JOIN users u ON u.id=a.user_id WHERE a.tenant_id=? ORDER BY u.name");
    $stmt->execute([$tenantId]);
    $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$agentId=(int)($_GET['agent_id'] ?? ($agents[0]['id'] ?? 0));
$rows=[];
if($agentId>0){
    $stmt=$pdo->prepare("SELECT game_type, percentage FROM commissions WHERE agent_id=?");
    $stmt->execute([$agentId]);
    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $r){$rows[$r['game_type']]=$r['percentage'];}
}
$games=game_engine_types($pdo,current_tenant_id() ?? 0,true);
$defaults=[]; foreach($games as $g){$defaults[$g['code']]=0;}
$selectedAgentName = '';
foreach ($agents as $agent) {
    if ((int)$agent['id'] === $agentId) { $selectedAgentName = (string)$agent['name']; break; }
}

ui_page_header('Commissions agents', 'Définissez un taux par jeu pour chaque agent.');
?>
<div class="grid grid-cols-1 lg:grid-cols-[minmax(0,24rem)_minmax(0,1fr)] gap-5 items-start">
    <aside class="ui-panel ui-panel-body">
        <form method="GET" data-no-responsive-filter="1">
            <label class="block font-semibold text-slate-800">Agent</label>
            <p class="text-sm text-slate-500 mt-1">Sélectionnez l’agent à configurer.</p>
            <select name="agent_id" onchange="this.form.submit()" class="form-control mt-3">
                <?php foreach($agents as $a): ?>
                    <option value="<?= (int)$a['id'] ?>" <?= $agentId===(int)$a['id']?'selected':'' ?>><?= e($a['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <div class="mt-4 rounded-xl bg-amber-50 border border-amber-100 p-4 text-sm text-amber-900">
            Les taux sont exprimés en pourcentage et peuvent varier selon le jeu.
        </div>
    </aside>

    <section class="ui-panel ui-panel-body">
        <?php if($agentId>0): ?>
            <div class="mb-5">
                <p class="text-xs uppercase tracking-wide font-bold text-slate-500">Agent sélectionné</p>
                <h2 class="text-xl font-bold mt-1"><?= e($selectedAgentName ?: 'Agent') ?></h2>
            </div>
            <form method="POST" action="/actions/commissions/update.php" data-no-responsive-filter="1">
                <?= csrf_field() ?><input type="hidden" name="agent_id" value="<?= $agentId ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 responsive-form-grid">
                    <?php foreach($defaults as $game=>$default):
                        $val=$rows[$game] ?? $default;
                        $gameName = ucfirst($game);
                        foreach ($games as $gameConfig) {
                            if ($gameConfig['code'] === $game) { $gameName = (string)$gameConfig['name']; break; }
                        }
                    ?>
                    <label class="block rounded-xl border border-slate-200 p-4 bg-slate-50/60">
                        <span class="block mb-2 font-semibold text-slate-800"><?= e($gameName) ?></span>
                        <div class="relative">
                            <input type="number" step="0.01" min="0" max="100" name="rates[<?= e($game) ?>]" value="<?= e((string)$val) ?>" class="form-control pr-10">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 font-bold">%</span>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="responsive-form-actions flex justify-end mt-5">
                    <button class="ui-btn ui-btn-primary">Enregistrer les commissions</button>
                </div>
            </form>
        <?php else: ?>
            <?php ui_empty_state('Aucun agent disponible', 'Créez un agent avant de définir ses commissions.', '％'); ?>
        <?php endif; ?>
    </section>
</div>
<?php require "../../includes/footer.php"; ?>
