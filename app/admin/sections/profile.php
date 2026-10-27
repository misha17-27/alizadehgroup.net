<?php
/** Own profile: name, e-mail, interface language and password. */
$me = current_user();
$fields = [
    ['name' => 'name', 'type' => 'text', 'label' => 'f_name', 'required' => true, 'col' => 'half', 'max' => 120],
    ['name' => 'email', 'type' => 'email', 'label' => 'c_email', 'required' => true, 'col' => 'half', 'max' => 160],
    ['name' => 'ui_lang', 'type' => 'select', 'label' => 'f_ui_lang', 'options' => ADMIN_UI_LANGS, 'col' => 'half'],
    ['type' => 'section', 'label' => 'sec_password', 'help' => 'help_password'],
    ['name' => 'current_password', 'type' => 'password', 'label' => 'f_current_password', 'col' => 'half'],
    ['name' => 'password', 'type' => 'password', 'label' => 'f_new_password', 'col' => 'half'],
];
$errors = [];
$rowP = $me;
if (is_post() && admin_post_ok()) {
    [$data, $errors] = collect_fields($fields, $me, 'az');
    $data['email'] = mb_strtolower((string)$data['email']);
    if (!isset(ADMIN_UI_LANGS[$data['ui_lang']])) $data['ui_lang'] = 'az';
    if (val('SELECT COUNT(*) FROM users WHERE email=? AND id<>?', [$data['email'], $me['id']])) $errors['email'] = __('err_email_taken');
    $new = (string)($_POST['password'] ?? '');
    if ($new !== '') {
        if (!password_verify((string)($_POST['current_password'] ?? ''), $me['pass'])) $errors['current_password'] = __('err_current_password');
        elseif (mb_strlen($new) < 8) $errors['password'] = __('err_password');
        else $data['pass'] = password_hash($new, PASSWORD_DEFAULT);
    }
    if (!$errors) {
        update('users', $data, 'id=?', [$me['id']]);
        flash(__('saved'));
        redirect(admin_url('profile'));
    }
    flash(__('form_errors'), 'err');
    $rowP = array_merge($me, $data);
}

admin_render('form', [
    'title' => __('nav_profile'), 'fields' => $fields, 'row' => $rowP, 'errors' => $errors, 'id' => (string)$me['id'],
    'action_url' => admin_url('profile'), 'back_url' => '', 'can_delete' => false, 'hint' => '', 'preview_url' => '',
]);
