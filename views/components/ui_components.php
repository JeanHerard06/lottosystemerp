<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/Helpers/permissions.php';

if (!function_exists('ui_money')) {
    function ui_money(float $amount, string $currency = 'HTG'): string
    {
        return number_format($amount, 2, '.', ',') . ' ' . $currency;
    }
}

if (!function_exists('ui_page_header')) {
    /**
     * @param array<int,array{label:string,href?:string,class?:string,icon?:string,type?:string,attributes?:array<string,string>}> $actions
     */
    function ui_page_header(string $title, string $subtitle = '', array $actions = []): void
    {
        echo '<section class="ui-page-header responsive-page-header">';
        echo '<div class="min-w-0">';
        echo '<h1 class="ui-page-title">' . e($title) . '</h1>';
        if ($subtitle !== '') {
            echo '<p class="ui-page-subtitle">' . e($subtitle) . '</p>';
        }
        echo '</div>';

        if ($actions !== []) {
            echo '<div class="ui-page-actions">';
            foreach ($actions as $action) {
                $label = (string)($action['label'] ?? 'Action');
                $class = (string)($action['class'] ?? 'ui-btn ui-btn-primary');
                $icon = trim((string)($action['icon'] ?? ''));
                $attributes = '';
                foreach (($action['attributes'] ?? []) as $key => $value) {
                    $attributes .= ' ' . e((string)$key) . '="' . e((string)$value) . '"';
                }
                $content = ($icon !== '' ? '<span aria-hidden="true">' . e($icon) . '</span>' : '') . '<span>' . e($label) . '</span>';
                if (!empty($action['href'])) {
                    echo '<a href="' . e((string)$action['href']) . '" class="' . e($class) . ' responsive-primary-action"' . $attributes . '>' . $content . '</a>';
                } else {
                    $type = (string)($action['type'] ?? 'button');
                    echo '<button type="' . e($type) . '" class="' . e($class) . ' responsive-primary-action"' . $attributes . '>' . $content . '</button>';
                }
            }
            echo '</div>';
        }
        echo '</section>';
    }
}

if (!function_exists('ui_stat_card')) {
    function ui_stat_card(
        string $label,
        string $value,
        string $tone = 'slate',
        string $meta = '',
        ?string $href = null,
        string $icon = ''
    ): void {
        $allowed = ['slate','blue','green','amber','red','violet','cyan'];
        if (!in_array($tone, $allowed, true)) {
            $tone = 'slate';
        }
        $tag = $href ? 'a' : 'article';
        $hrefAttribute = $href ? ' href="' . e($href) . '"' : '';
        echo '<' . $tag . $hrefAttribute . ' class="ui-stat-card ui-stat-' . e($tone) . ' dashboard-kpi-card">';
        echo '<div class="ui-stat-card-top">';
        echo '<p class="ui-stat-label">' . e($label) . '</p>';
        if ($icon !== '') {
            echo '<span class="ui-stat-icon" aria-hidden="true">' . e($icon) . '</span>';
        }
        echo '</div>';
        echo '<p class="ui-stat-value">' . e($value) . '</p>';
        if ($meta !== '') {
            echo '<p class="ui-stat-meta">' . e($meta) . '</p>';
        }
        echo '</' . $tag . '>';
    }
}

if (!function_exists('ui_status_badge')) {
    function ui_status_badge(string $status, ?string $label = null): string
    {
        $normalized = strtolower(trim($status));
        $tone = match ($normalized) {
            'active','open','opened','won','paid','approved','posted','success','valid','validated' => 'success',
            'pending','draft','waiting','closed','warning' => 'warning',
            'cancelled','canceled','rejected','failed','lost','inactive','blocked','error' => 'danger',
            default => 'muted',
        };
        $text = $label ?? ucfirst(str_replace('_', ' ', $status));
        return '<span class="ui-badge ui-badge-' . e($tone) . '">' . e($text) . '</span>';
    }
}

if (!function_exists('ui_empty_state')) {
    function ui_empty_state(string $title, string $message = '', string $icon = '—'): void
    {
        echo '<div class="ui-empty-state">';
        echo '<div class="ui-empty-icon" aria-hidden="true">' . e($icon) . '</div>';
        echo '<h3>' . e($title) . '</h3>';
        if ($message !== '') {
            echo '<p>' . e($message) . '</p>';
        }
        echo '</div>';
    }
}

if (!function_exists('ui_action_link')) {
    function ui_action_link(string $label, string $href, string $tone = 'secondary', string $icon = ''): string
    {
        $allowed = ['primary','secondary','success','danger','warning','ghost'];
        if (!in_array($tone, $allowed, true)) {
            $tone = 'secondary';
        }
        return '<a href="' . e($href) . '" class="ui-btn ui-btn-' . e($tone) . ' ui-btn-sm">'
            . ($icon !== '' ? '<span aria-hidden="true">' . e($icon) . '</span>' : '')
            . '<span>' . e($label) . '</span></a>';
    }
}
