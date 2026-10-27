<?php
/** Static pages: H1, the text under it and the main text, SEO title/description, hero image. */
$names = ['home' => __('page_home'), 'about' => __('page_about'), 'services' => __('page_services'),
    'partners' => __('page_partners'), 'contact' => __('page_contact')];

// the home page has no page header: its H1 and texts are edited in the slider and the other content sections
$content = (string)($_GET['id'] ?? $_POST['id'] ?? '') === 'home'
    ? [['type' => 'section', 'label' => 'fs_content', 'help' => 'help_home_content']]
    : [
        ['type' => 'section', 'label' => 'fs_content'],
        ['name' => 'h1', 'type' => 'text', 'label' => 'f_h1', 'i18n' => true, 'max' => 255],
        ['name' => 'lead', 'type' => 'textarea', 'label' => 'f_lead', 'i18n' => true, 'rows' => 3],
        ['name' => 'body', 'type' => 'html', 'label' => 'f_body', 'i18n' => true, 'help' => 'help_body'],
    ];

crud([
    'section' => 'pages', 'table' => 'pages', 'pk' => 'k', 'title' => __('nav_pages'),
    'can_create' => false, 'can_delete' => false, 'sortable' => false, 'has_active' => false,
    'order' => "CASE k WHEN 'home' THEN 1 WHEN 'about' THEN 2 WHEN 'services' THEN 3 WHEN 'partners' THEN 4 WHEN 'contact' THEN 5 ELSE 9 END, k",
    'hint' => __('hint_pages'),
    'fields' => array_merge($content, [
        ['type' => 'section', 'label' => 'fs_seo'],
        ['name' => 'title', 'type' => 'text', 'label' => 'f_meta_title', 'i18n' => true, 'max' => 255, 'help' => 'help_meta_title'],
        ['name' => 'description', 'type' => 'textarea', 'label' => 'f_meta_desc', 'i18n' => true, 'help' => 'help_meta_desc'],
        ['name' => 'image', 'type' => 'image', 'label' => 'f_hero_image', 'help' => 'help_hero_image'],
    ]),
    'cols' => [
        ['key' => 'image', 'type' => 'thumb'],
        ['key' => 'k', 'type' => 'fn', 'label' => __('f_page'), 'fn' => function (array $r) use ($names): string {
            return '<b>' . e($names[$r['k']] ?? $r['k']) . '</b>';
        }],
        ['key' => 'k', 'type' => 'fn', 'label' => __('f_address'), 'fn' => function (array $r): string {
            $u = isset(ROUTES[$r['k']]) ? route($r['k'], [], content_lang()) : '';
            return $u !== '' ? '<a class="addr" href="' . e($u) . '" target="_blank" rel="noopener">' . e($u) . '</a>' : '';
        }],
        ['key' => 'h1', 'type' => 'i18n', 'label' => __('f_h1')],
        ['key' => 'title', 'type' => 'i18n', 'label' => __('f_meta_title')],
    ],
    'preview' => function (array $r): string {
        return isset(ROUTES[$r['k']]) ? route($r['k'], [], content_lang()) : '';
    },
]);
