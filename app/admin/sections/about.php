<?php
/** "About us" bullet points. The block title and text live in Texts › about. */
crud([
    'section' => 'about', 'table' => 'items', 'where' => "grp='about'", 'title' => __('nav_about'),
    'defaults' => ['grp' => 'about', 'active' => 1, 'icon' => 'check'],
    'hint' => __('hint_about'),
    'hint_link' => [admin_url('texts', ['g' => 'about']), __('nav_texts')],
    'new_label' => __('add_point'),
    'fields' => [
        ['name' => 'icon', 'type' => 'icon', 'label' => 'f_icon', 'col' => 'half'],
        ['name' => 'title', 'type' => 'text', 'label' => 'f_point', 'i18n' => true, 'required' => true, 'max' => 255],
        ['name' => 'text', 'type' => 'textarea', 'label' => 'f_note', 'i18n' => true, 'help' => 'help_optional'],
        ['name' => 'sort', 'type' => 'number', 'label' => 'f_sort', 'col' => 'half'],
        ['name' => 'active', 'type' => 'checkbox', 'label' => 'f_active', 'col' => 'half', 'default_on' => true],
    ],
    'cols' => [
        ['key' => 'icon', 'type' => 'icon'],
        ['key' => 'title', 'type' => 'i18n', 'label' => __('f_point')],
    ],
]);
