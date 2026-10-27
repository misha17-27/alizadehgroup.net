<?php
/** SEO title/description and hero image of the static pages. */
$names = ['home' => __('page_home'), 'about' => __('page_about'), 'services' => __('page_services'),
    'partners' => __('page_partners'), 'contact' => __('page_contact')];

crud([
    'section' => 'pages', 'table' => 'pages', 'pk' => 'k', 'title' => __('nav_pages'),
    'can_create' => false, 'can_delete' => false, 'sortable' => false, 'has_active' => false, 'order' => 'k',
    'hint' => __('hint_pages'),
    'fields' => [
        ['name' => 'title', 'type' => 'text', 'label' => 'f_meta_title', 'i18n' => true, 'max' => 255, 'help' => 'help_meta_title'],
        ['name' => 'description', 'type' => 'textarea', 'label' => 'f_meta_desc', 'i18n' => true, 'help' => 'help_meta_desc'],
        ['name' => 'image', 'type' => 'image', 'label' => 'f_hero_image', 'help' => 'help_hero_image'],
    ],
    'cols' => [
        ['key' => 'image', 'type' => 'thumb'],
        ['key' => 'k', 'type' => 'fn', 'label' => __('f_page'), 'link' => true, 'fn' => function (array $r) use ($names): string {
            return '<b>' . e($names[$r['k']] ?? $r['k']) . '</b>';
        }],
        ['key' => 'title', 'type' => 'i18n', 'label' => __('f_meta_title')],
    ],
    'preview' => function (array $r): string {
        return isset(ROUTES[$r['k']]) ? route($r['k'], [], content_lang()) : '';
    },
]);
