<?php
/** Blog (optional section "blog"): newest post first. */
require_section('blog');

crud([
    'section' => 'blog', 'table' => 'posts', 'title' => __('nav_blog'),
    'sortable' => false, 'order' => 'published_at DESC, id DESC',
    'defaults' => ['active' => 1, 'published_at' => date('Y-m-d')],
    'hint' => __('hint_blog'),
    'fields' => [
        ['name' => 'title', 'type' => 'text', 'label' => 'f_title', 'i18n' => true, 'required' => true, 'max' => 255],
        ['name' => 'slug', 'type' => 'text', 'label' => 'f_slug', 'i18n' => true, 'max' => 120, 'help' => 'help_slug'],
        ['name' => 'published_at', 'type' => 'date', 'label' => 'f_date', 'col' => 'half'],
        ['name' => 'active', 'type' => 'checkbox', 'label' => 'f_active', 'col' => 'half', 'default_on' => true],
        ['name' => 'short', 'type' => 'textarea', 'label' => 'f_short', 'i18n' => true, 'help' => 'help_item_short'],
        ['name' => 'body', 'type' => 'html', 'label' => 'f_body', 'i18n' => true],
        ['name' => 'image', 'type' => 'image', 'label' => 'f_image', 'help' => 'help_item_image'],
        ['type' => 'section', 'label' => 'sec_seo'],
        ['name' => 'meta_title', 'type' => 'text', 'label' => 'f_meta_title', 'i18n' => true, 'max' => 255, 'help' => 'help_meta_title'],
        ['name' => 'meta_desc', 'type' => 'textarea', 'label' => 'f_meta_desc', 'i18n' => true, 'help' => 'help_meta_desc'],
    ],
    'cols' => [
        ['key' => 'image', 'type' => 'thumb'],
        ['key' => 'title', 'type' => 'i18n', 'label' => __('f_title')],
        ['key' => 'published_at', 'type' => 'fn', 'label' => __('f_date'), 'fn' => function (array $r): string {
            return '<span class="muted">' . e(fmt_date($r['published_at'], false)) . '</span>';
        }],
    ],
    'before_save' => function (array $data, array $row, array $errors, bool $isNew): array {
        if ((string)($data['published_at'] ?? '') === '') $data['published_at'] = date('Y-m-d');
        [$data, $errors] = crud_slug('posts', $data, $row, $errors, $isNew);
        $data['updated_at'] = now();
        return [$data, $errors];
    },
    'preview' => function (array $r): string {
        return route('post', ['row' => $r], content_lang());
    },
]);
