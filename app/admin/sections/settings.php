<?php
/** Site settings: contacts, socials, notifications, integrations (admins only). */
require_admin();

$fields = [
    ['type' => 'section', 'label' => 'sec_contacts'],
    ['name' => 'phone', 'type' => 'text', 'label' => 'c_phone', 'col' => 'half', 'max' => 60],
    ['name' => 'email', 'type' => 'email', 'label' => 'c_email', 'col' => 'half', 'max' => 160],
    ['name' => 'whatsapp', 'type' => 'text', 'label' => 'f_whatsapp', 'col' => 'half', 'help' => 'help_whatsapp', 'max' => 30],
    ['name' => 'map_query', 'type' => 'text', 'label' => 'f_map', 'col' => 'half', 'help' => 'help_map', 'max' => 255],
    ['name' => 'address_az', 'type' => 'text', 'label' => 'f_address_az', 'col' => 'half', 'max' => 255],
    ['name' => 'address_en', 'type' => 'text', 'label' => 'f_address_en', 'col' => 'half', 'max' => 255],
    ['type' => 'section', 'label' => 'sec_social'],
    ['name' => 'instagram', 'type' => 'url', 'label' => 'Instagram', 'col' => 'third', 'max' => 255],
    ['name' => 'facebook', 'type' => 'url', 'label' => 'Facebook', 'col' => 'third', 'max' => 255],
    ['name' => 'linkedin', 'type' => 'url', 'label' => 'LinkedIn', 'col' => 'third', 'max' => 255],
    ['type' => 'section', 'label' => 'sec_notify'],
    ['name' => 'notify_email', 'type' => 'email', 'label' => 'f_notify_email', 'help' => 'help_notify_email', 'max' => 160],
    ['type' => 'section', 'label' => 'sec_integrations'],
    ['name' => 'ga_id', 'type' => 'text', 'label' => 'f_ga', 'col' => 'third', 'placeholder' => 'G-XXXXXXX', 'max' => 40],
    ['name' => 'turnstile_site', 'type' => 'text', 'label' => 'f_ts_site', 'col' => 'third', 'help' => 'help_turnstile', 'max' => 120],
    ['name' => 'turnstile_secret', 'type' => 'text', 'label' => 'f_ts_secret', 'col' => 'third', 'max' => 120],
];

$rowS = settings();
$errors = [];
if (is_post() && admin_post_ok()) {
    [$data, $errors] = collect_fields($fields, $rowS, 'az');
    if (!$errors) {
        foreach ($data as $k => $v) {
            if (val('SELECT COUNT(*) FROM settings WHERE k=?', [$k])) update('settings', ['v' => (string)$v], 'k=?', [$k]);
            else insert('settings', ['k' => $k, 'v' => (string)$v]);
        }
        flash(__('saved'));
        redirect(admin_url('settings'));
    }
    flash(__('form_errors'), 'err');
    $rowS = array_merge($rowS, $data);
}

admin_render('form', [
    'title' => __('nav_settings'), 'fields' => $fields, 'row' => $rowS, 'errors' => $errors, 'id' => 'settings',
    'action_url' => admin_url('settings'), 'back_url' => '', 'can_delete' => false, 'hint' => '', 'preview_url' => '',
]);
