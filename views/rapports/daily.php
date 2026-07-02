<?php
require "../../config/database.php";
require "../../includes/header.php";
require "../../includes/sidebar.php";
require "../../includes/topbar.php";

$date = $_GET['date'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
SELECT 
COUNT(*) AS total_fiches,
COALESCE(SUM(total_amount),0) AS total_ventes,
COALESCE(SUM(gain_amount),0) AS total_gains
FROM fiches
WHERE DATE(created_at)=?
");
$stmt->execute([$date]);
$report = $stmt->fetch();

$profit = $report['total_ventes'] - $report['total_gains'];

$fiches = $pdo->prepare("
SELECT f.*, u.name AS agent_name
FROM fiches f
JOIN agents a ON a.id=f.agent_id
JOIN users u ON u.id=a.user_id
WHERE DATE(f.created_at)=?
ORDER BY f.id DESC
");
$fiches->execute([$date]);
$fiches = $fiches->fetchAll();
?>

    <h1 class="text-2xl font-bold mb-5">Rapport Journalier</h1>

    <form method="GET" class="bg-white p-4 rounded shadow mb-5 flex gap-3">
        <input type="date" name="date" value="<?= $date ?>" class="border p-3 rounded">
        <button class="bg-black text-white px-5 rounded">Filtrer</button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">

        <div class="bg-white p-5 rounded shadow">
            <p class="text-gray-500">Fiches</p>
            <h2 class="text-3xl font-bold"><?= $report['total_fiches'] ?></h2>
        </div>

        <div class="bg-white p-5 rounded shadow">
            <p class="text-gray-500">Ventes</p>
            <h2 class="text-3xl font-bold">$<?= number_format($report['total_ventes'],2) ?></h2>
        </div>

        <div class="bg-white p-5 rounded shadow">
            <p class="text-gray-500">Gains</p>
            <h2 class="text-3xl font-bold">$<?= number_format($report['total_gains'],2) ?></h2>
        </div>

        <div class="bg-white p-5 rounded shadow">
            <p class="text-gray-500">Profit</p>
            <h2 class="text-3xl font-bold">$<?= number_format($profit,2) ?></h2>
        </div>

    </div>

    <table class="w-full bg-white rounded shadow">
        <thead>
        <tr class="bg-gray-200 text-left">
            <th class="p-3">Code</th>
            <th class="p-3">Agent</th>
            <th class="p-3">Vente</th>
            <th class="p-3">Gain</th>
            <th class="p-3">Statut</th>
            <th class="p-3">Date</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach($fiches as $f): ?>
            <tr class="border-b">
                <td class="p-3"><?= htmlspecialchars($f['fiche_code']) ?></td>
                <td class="p-3"><?= htmlspecialchars($f['agent_name']) ?></td>
                <td class="p-3">$<?= number_format($f['total_amount'],2) ?></td>
                <td class="p-3">$<?= number_format((float)($f['gain_amount'] ?? 0), 2) ?></td>                <td class="p-3"><?= $f['status'] ?></td>
                <td class="p-3"><?= $f['created_at'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php require "../../includes/fotter.php"; ?>