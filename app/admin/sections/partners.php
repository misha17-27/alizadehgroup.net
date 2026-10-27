<?php
/** Partner logos. */
crud([
    'section' => 'partners', 'table' => 'partners', 'title' => __('nav_partners'), 'i18n' => false,
    'defaults' => ['active' => 1],
    'hint' => __('hint_partners'),
    'fields' => [
        ['name' => 'logo', 'type' => 'image', 'label' => 'f_logo', 'required' => true, 'help' => 'help_logo'],
        ['name' => 'name', 'type' => 'text', 'label' => 'f_name', 'required' => true, 'max' => 160],
        ['name' => 'url', 'type' => 'url', 'label' => 'f_website', 'placeholder' => 'https://', 'max' => 255],
        ['name' => 'sort', 'type' => 'number', 'label' => 'f_sort', 'col' => 'half'],
        ['name' => 'active', 'type' => 'checkbox', 'label' => 'f_active', 'col' => 'half', 'default_on' => true],
    ],
    'cols' => [
        ['key' => 'logo', 'type' => 'thumb', 'wide' => true],
        ['key' => 'name', 'type' => 'text', 'label' => __('f_name')],
        ['key' => 'url', 'type' => 'fn', 'label' => __('f_website'), 'fn' => function (array $r): string {
            if ($r['url'] === '') return '<span class="muted">—</span>';
            return '<a class="muted" href="' . e($r['url']) . '" target="_blank" rel="noopener">' . e(parse_url($r['url'], PHP_URL_HOST) ?: $r['url']) . '</a>';
        }],
    ],
]);
