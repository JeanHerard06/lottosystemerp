<?php
// Dashboard v2 entry point placeholder.
// Integrate with existing auth/tenant helpers from the project.
?>
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="card"><p>Ventes aujourd’hui</p><h2 id="salesToday">0</h2></div>
    <div class="card"><p>Profit</p><h2 id="profitToday">0</h2></div>
    <div class="card"><p>Lotteries ouvertes</p><h2 id="openLotteries">0</h2></div>
    <div class="card"><p>Cash sessions</p><h2 id="openCashSessions">0</h2></div>
</div>
<canvas id="salesTrendChart" class="mt-6"></canvas>
<script src="/public/assets/js/dashboard-v2.js"></script>
