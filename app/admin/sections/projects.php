<?php
/** Projects (optional section "projects"): text, photo gallery and YouTube videos. */
require_section('projects');

crud([
    'section' => 'projects', 'table' => 'projects', 'title' => __('nav_projects'),
    'defaults' => ['active' => 1, 'gallery' => '[]', 'videos' => '[]'],
    'hint' => __('hint_projects'),
    'fields' => [
        ['name' => 'title', 'type' => 'text', 'label' => 'f_title', 'i18n' => true, 'required' => true, 'max' => 255],
        ['name' => 'slug', 'type' => 'text', 'label' => 'f_slug', 'i18n' => true, 'max' => 120, 'help' => 'help_slug'],
        ['name' => 'short', 'type' => 'textarea', 'label' => 'f_short', 'i18n' => true, 'help' => 'help_item_short'],
        ['name' => 'body', 'type' => 'html', 'label' => 'f_body', 'i18n' => true],
        ['name' => 'image', 'type' => 'image', 'label' => 'f_image', 'help' => 'help_item_image'],
        ['name' => 'gallery', 'type' => 'gallery', 'label' => 'f_gallery'],
        ['name' => 'videos', 'type' => 'repeater', 'label' => 'rep_videos', 'help' => 'help_videos', 'sub' => [
            ['key' => 'url', 'type' => 'text', 'label' => 'f_youtube'],
        ]],
        ['name' => 'sort', 'type' => 'number', 'label' => 'f_sort', 'col' => 'half'],
        ['name' => 'active', 'type' => 'checkbox', 'label' => 'f_active', 'col' => 'half', 'default_on' => true],
        ['type' => 'section', 'label' => 'sec_seo'],
        ['name' => 'meta_title', 'type' => 'text', 'label' => 'f_meta_title', 'i18n' => true, 'max' => 255, 'help' => 'help_meta_title'],
        ['name' => 'meta_desc', 'type' => 'textarea', 'label' => 'f_meta_desc', 'i18n' => true, 'help' => 'help_meta_desc'],
    ],
    'cols' => [
        ['key' => 'image', 'type' => 'thumb'],
        ['key' => 'title', 'type' => 'i18n', 'label' => __('f_title')],
        ['key' => 'gallery', 'type' => 'fn', 'label' => __('f_gallery'), 'fn' => function (array $r): string {
            return '<span class="muted">' . count(jlist($r['gallery'])) . '</span>';
        }],
        ['key' => 'videos', 'type' => 'fn', 'label' => __('rep_videos'), 'fn' => function (array $r): string {
            return '<span class="muted">' . count(jlist($r['videos'])) . '</span>';
        }],
    ],
    'before_save' => function (array $data, array $row, array $errors, bool $isNew): array {
        foreach (jlist($data['videos'] ?? '') as $v) {
            if (youtube_id((string)($v['url'] ?? '')) === '') $errors['videos'] = __('err_youtube');
        }
        [$data, $errors] = crud_slug('projects', $data, $row, $errors, $isNew);
        $data['updated_at'] = now();
        return [$data, $errors];
    },
    'preview' => function (array $r): string {
        return route('project', ['row' => $r], content_lang());
    },
]);
