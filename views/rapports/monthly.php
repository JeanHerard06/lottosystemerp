<?php
require "../../config/database.php";
require "../../includes/header.php";
require "../../includes/sidebar.php";
require "../../includes/topbar.php";

$month = $_GET['month'] ?? date('Y-m');

$stmt = $pdo->prepare("
SELECT 
DATE(created_at) AS jour,
COUNT(*) AS total_fiches,
COALESCE(SUM(total_amount),0) AS ventes,
COALESCE(SUM(gain_amount),0) AS gains
FROM fiches
WHERE DATE_FORMAT(created_at, '%Y-%m')=?
GROUP BY DATE(created_at)
ORDER BY jour DESC
");
$stmt->execute([$month]);
$reports = $stmt->fetchAll();
?>

    <h1 class="text-2xl font-bold mb-5">Rapport Mensuel</h1>

    <form method="GET" class="bg-white p-4 rounded shadow mb-5 flex gap-3">
        <input type="month" name="month" value="<?= $month ?>" class="border p-3 rounded">
        <button class="bg-black text-white px-5 rounded">Filtrer</button>
    </form>

    <table class="w-full bg-white rounded shadow">
        <thead>
        <tr class="bg-gray-200 text-left">
            <th class="p-3">Jour</th>
            <th class="p-3">Fiches</th>
            <th class="p-3">Ventes</th>
            <th class="p-3">Gains</th>
            <th class="p-3">Profit</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach($reports as $r): ?>
            <tr class="border-b">
                <td class="p-3"><?= $r['jour'] ?></td>
                <td class="p-3"><?= $r['total_fiches'] ?></td>
                <td class="p-3">$<?= number_format($r['ventes'],2) ?></td>
                <td class="p-3">$<?= number_format($r['gains'],2) ?></td>
                <td class="p-3">$<?= number_format($r['ventes'] - $r['gains'],2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php require "../../includes/footer.php"; ?>