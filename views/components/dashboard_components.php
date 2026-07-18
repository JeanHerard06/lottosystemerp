<?php

declare(strict_types=1);

if (!function_exists('dashboard_money')) {
    function dashboard_money(float $amount): string
    {
        return number_format($amount, 2, '.', ',') . ' HTG';
    }
}

if (!function_exists('dashboard_kpi_card')) {
    function dashboard_kpi_card(
        string $label,
        string $value,
        string $tone = 'white',
        ?string $href = null,
        ?string $subtitle = null
    ): void {
        $classes = [
            'white' => 'bg-white text-gray-900',
            'blue' => 'bg-blue-50 text-blue-950',
            'green' => 'bg-emerald-50 text-emerald-950',
            'yellow' => 'bg-amber-50 text-amber-950',
            'purple' => 'bg-violet-50 text-violet-950',
            'red' => 'bg-rose-50 text-rose-950',
            'slate' => 'bg-slate-900 text-white',
        ];
        $class = $classes[$tone] ?? $classes['white'];
        $tag = $href ? 'a' : 'div';
        $link = $href ? ' href="' . e($href) . '"' : '';

        echo '<' . $tag . $link
            . ' class="dashboard-kpi-card block rounded-2xl shadow-sm border border-black/5 p-5 '
            . $class
            . ' transition hover:-translate-y-0.5 hover:shadow-md">';
        echo '<p class="text-sm opacity-70">' . e($label) . '</p>';
        echo '<p class="mt-2 text-2xl font-bold tracking-tight">' . e($value) . '</p>';
        if ($subtitle !== null && $subtitle !== '') {
            echo '<p class="mt-1 text-xs opacity-65">' . e($subtitle) . '</p>';
        }
        echo '</' . $tag . '>';
    }
}
