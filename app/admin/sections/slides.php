<?php
/** Home page hero slider. */
crud([
    'section' => 'slides', 'table' => 'slides', 'title' => __('nav_slides'),
    'defaults' => ['active' => 1, 'link' => 'contact'],
    'hint' => __('hint_slides'),
    'fields' => [
        ['name' => 'image', 'type' => 'image', 'label' => 'f_bg_image', 'help' => 'help_bg_image'],
        ['name' => 'eyebrow', 'type' => 'text', 'label' => 'f_eyebrow', 'i18n' => true, 'max' => 255],
        ['name' => 'title', 'type' => 'text', 'label' => 'f_title', 'i18n' => true, 'required' => true, 'max' => 255],
        ['name' => 'text', 'type' => 'textarea', 'label' => 'f_text', 'i18n' => true],
        ['name' => 'btn', 'type' => 'text', 'label' => 'f_btn', 'i18n' => true, 'col' => 'half', 'max' => 80],
        ['name' => 'link', 'type' => 'link', 'label' => 'f_link', 'col' => 'half'],
        ['name' => 'sort', 'type' => 'number', 'label' => 'f_sort', 'col' => 'half'],
        ['name' => 'active', 'type' => 'checkbox', 'label' => 'f_active', 'col' => 'half', 'default_on' => true],
    ],
    'cols' => [
        ['key' => 'image', 'type' => 'thumb'],
        ['key' => 'title', 'type' => 'i18n', 'label' => __('f_title')],
        ['key' => 'link', 'type' => 'fn', 'label' => __('f_link'), 'fn' => function (array $r): string {
            $o = link_options();
            return e($o[$r['link']] ?? $r['link']);
        }],
    ],
]);
