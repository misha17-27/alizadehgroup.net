<?php
/** Projects (optional section "projects"): date, category, duration, text, photo gallery and YouTube videos. */
require_section('projects');

$categories = category_options(false, 'project_categories');

crud([
    'section' => 'projects', 'table' => 'projects', 'title' => __('nav_projects'),
    'defaults' => ['active' => 1, 'gallery' => '[]', 'videos' => '[]'],
    'hint' => __('hint_projects'),
    'hint_link' => [admin_url('project_categories'), __('nav_project_categories')],
    'fields' => [
        ['name' => 'title', 'type' => 'text', 'label' => 'f_title', 'i18n' => true, 'required' => true, 'max' => 255],
        ['name' => 'slug', 'type' => 'text', 'label' => 'f_slug', 'i18n' => true, 'max' => 120, 'help' => 'help_slug'],
        ['name' => 'project_date', 'type' => 'date', 'label' => 'f_project_date', 'col' => 'half'],
        ['name' => 'category_id', 'type' => 'select', 'label' => 'f_category', 'options' => ['' => '—'] + $categories, 'col' => 'half', 'help' => 'help_project_category'],
        ['name' => 'duration', 'type' => 'text', 'label' => 'f_duration', 'i18n' => true, 'max' => 255, 'help' => 'help_duration'],
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
        ['key' => 'category_id', 'type' => 'fn', 'label' => __('f_category'), 'fn' => function (array $r) use ($categories): string {
            return isset($categories[$r['category_id']]) ? '<span class="pill">' . e($categories[$r['category_id']]) . '</span>' : '<span class="muted">—</span>';
        }],
        ['key' => 'project_date', 'type' => 'fn', 'label' => __('f_project_date'), 'fn' => function (array $r): string {
            return '<span class="muted">' . e(date_text($r['project_date']) ?: '—') . '</span>';
        }],
        ['key' => 'gallery', 'type' => 'fn', 'label' => __('f_gallery'), 'fn' => function (array $r): string {
            return '<span class="muted">' . count(jlist($r['gallery'])) . '</span>';
        }],
        ['key' => 'videos', 'type' => 'fn', 'label' => __('rep_videos'), 'fn' => function (array $r): string {
            return '<span class="muted">' . count(jlist($r['videos'])) . '</span>';
        }],
    ],
    'before_save' => function (array $data, array $row, array $errors, bool $isNew) use ($categories): array {
        // both may stay empty: an empty form value does not fit the DATE and INT columns
        $data['project_date'] = ($data['project_date'] ?? '') === '' ? null : $data['project_date'];
        $data['category_id'] = isset($categories[(int)($data['category_id'] ?? 0)]) ? (int)$data['category_id'] : 0;
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
