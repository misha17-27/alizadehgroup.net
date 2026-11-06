<?php
/** Project categories (optional section "projects"); a project may also have none. */
require_section('projects');

crud([
    'section' => 'project_categories', 'table' => 'project_categories', 'title' => __('nav_project_categories'),
    'defaults' => ['active' => 1],
    'hint' => __('hint_project_categories'),
    'fields' => [
        ['name' => 'title', 'type' => 'text', 'label' => 'f_title', 'i18n' => true, 'required' => true, 'max' => 255],
        ['name' => 'slug', 'type' => 'text', 'label' => 'f_slug', 'i18n' => true, 'max' => 120, 'help' => 'help_slug'],
        ['name' => 'image', 'type' => 'image', 'label' => 'f_hero_image', 'help' => 'help_hero_image'],
        ['name' => 'sort', 'type' => 'number', 'label' => 'f_sort', 'col' => 'half'],
        ['name' => 'active', 'type' => 'checkbox', 'label' => 'f_active', 'col' => 'half', 'default_on' => true],
    ],
    'cols' => [
        ['key' => 'image', 'type' => 'thumb'],
        ['key' => 'title', 'type' => 'i18n', 'label' => __('f_title')],
        ['key' => 'id', 'type' => 'fn', 'label' => __('nav_projects'), 'fn' => function (array $r): string {
            return '<span class="muted">' . (int)val('SELECT COUNT(*) FROM projects WHERE category_id=?', [$r['id']]) . '</span>';
        }],
    ],
    'before_save' => function (array $data, array $row, array $errors, bool $isNew): array {
        return crud_slug('project_categories', $data, $row, $errors, $isNew);
    },
    'before_delete' => function (array $r): ?string {
        return (int)val('SELECT COUNT(*) FROM projects WHERE category_id=?', [$r['id']]) ? __('err_project_category_used') : null;
    },
    'preview' => function (array $r): string {
        return route('project_cat', ['row' => $r], content_lang());
    },
]);
