<?php
/** The 4 info cards under the slider (each opens the request form with a service preselected). */
crud([
    'section' => 'info', 'table' => 'items', 'where' => "grp='info'", 'title' => __('nav_info'),
    'defaults' => ['grp' => 'info', 'active' => 1, 'icon' => 'check'],
    'hint' => __('hint_info'),
    'fields' => [
        ['name' => 'icon', 'type' => 'icon', 'label' => 'f_icon', 'col' => 'half'],
        ['name' => 'link', 'type' => 'service', 'label' => 'f_service', 'col' => 'half', 'help' => 'help_info_service'],
        ['name' => 'title', 'type' => 'text', 'label' => 'f_title', 'i18n' => true, 'required' => true, 'max' => 255],
        ['name' => 'text', 'type' => 'textarea', 'label' => 'f_text', 'i18n' => true],
        ['name' => 'sort', 'type' => 'number', 'label' => 'f_sort', 'col' => 'half'],
        ['name' => 'active', 'type' => 'checkbox', 'label' => 'f_active', 'col' => 'half', 'default_on' => true],
    ],
    'cols' => [
        ['key' => 'icon', 'type' => 'icon'],
        ['key' => 'title', 'type' => 'i18n', 'label' => __('f_title')],
        ['key' => 'link', 'type' => 'fn', 'label' => __('f_service'), 'fn' => function (array $r): string {
            $o = service_options();
            return e($o[$r['link']] ?? '—');
        }],
    ],
]);
