<?php
/** "Why Alizadeh Group?" items. The block title and subtitle live in Texts › why. */
crud([
    'section' => 'why', 'table' => 'items', 'where' => "grp='why'", 'title' => __('nav_why'),
    'defaults' => ['grp' => 'why', 'active' => 1, 'icon' => 'star'],
    'hint' => __('hint_why'),
    'hint_link' => [admin_url('texts', ['g' => 'why']), __('nav_texts')],
    'fields' => [
        ['name' => 'icon', 'type' => 'icon', 'label' => 'f_icon', 'col' => 'half'],
        ['name' => 'title', 'type' => 'text', 'label' => 'f_title', 'i18n' => true, 'required' => true, 'max' => 255],
        ['name' => 'text', 'type' => 'textarea', 'label' => 'f_text', 'i18n' => true],
        ['name' => 'sort', 'type' => 'number', 'label' => 'f_sort', 'col' => 'half'],
        ['name' => 'active', 'type' => 'checkbox', 'label' => 'f_active', 'col' => 'half', 'default_on' => true],
    ],
    'cols' => [
        ['key' => 'icon', 'type' => 'icon'],
        ['key' => 'title', 'type' => 'i18n', 'label' => __('f_title')],
        ['key' => 'text', 'type' => 'i18n', 'label' => __('f_text')],
    ],
]);
