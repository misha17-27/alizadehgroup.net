<?php
/** Admin users (admins only). Roles: admin — everything; editor — content and requests. */
require_admin();
$roles = ['admin' => __('role_admin'), 'editor' => __('role_editor')];

crud([
    'section' => 'users', 'table' => 'users', 'title' => __('nav_users'), 'i18n' => false,
    'sortable' => false, 'has_active' => false, 'order' => 'id', 'defaults' => ['role' => 'editor', 'active' => 1, 'ui_lang' => 'az'],
    'hint' => __('hint_users'),
    'fields' => [
        ['name' => 'name', 'type' => 'text', 'label' => 'f_name', 'required' => true, 'col' => 'half', 'max' => 120],
        ['name' => 'email', 'type' => 'email', 'label' => 'c_email', 'required' => true, 'col' => 'half', 'max' => 160],
        ['name' => 'role', 'type' => 'select', 'label' => 'f_role', 'options' => $roles, 'col' => 'third'],
        ['name' => 'ui_lang', 'type' => 'select', 'label' => 'f_ui_lang', 'options' => ADMIN_UI_LANGS, 'col' => 'third'],
        ['name' => 'active', 'type' => 'checkbox', 'label' => 'f_active', 'col' => 'third', 'default_on' => true],
        ['name' => 'password', 'type' => 'password', 'label' => 'f_new_password', 'help' => 'help_password'],
    ],
    'cols' => [
        ['key' => 'name', 'type' => 'text', 'label' => __('f_name')],
        ['key' => 'email', 'type' => 'text', 'label' => __('c_email')],
        ['key' => 'role', 'type' => 'fn', 'label' => __('f_role'), 'fn' => function (array $r) use ($roles): string {
            return '<span class="pill' . ($r['role'] === 'admin' ? ' pill-dark' : '') . '">' . e($roles[$r['role']] ?? $r['role']) . '</span>';
        }],
        ['key' => 'last_login', 'type' => 'fn', 'label' => __('last_login'), 'fn' => function (array $r): string {
            return '<span class="muted">' . e(fmt_date($r['last_login'])) . '</span>';
        }],
    ],
    'before_save' => function (array $data, array $row, array $errors, bool $isNew) use ($roles): array {
        $data['email'] = mb_strtolower((string)($data['email'] ?? ''));
        if (!isset($roles[$data['role'] ?? ''])) $data['role'] = 'editor';
        if (val('SELECT COUNT(*) FROM users WHERE email=? AND id<>?', [$data['email'], (int)($row['id'] ?? 0)])) $errors['email'] = __('err_email_taken');
        $pass = (string)($_POST['password'] ?? '');
        if ($pass !== '') {
            if (mb_strlen($pass) < 8) $errors['password'] = __('err_password');
            else $data['pass'] = password_hash($pass, PASSWORD_DEFAULT);
        } elseif ($isNew) {
            $errors['password'] = __('err_password');
        }
        $me = (int)current_user()['id'];
        if (!$isNew && (int)$row['id'] === $me && ($data['role'] !== 'admin' || empty($data['active']))) {
            $errors['role'] = __('err_self_demote');
        }
        if ($isNew) $data['created_at'] = now();
        return [$data, $errors];
    },
    'before_delete' => function (array $r): ?string {
        if ((int)$r['id'] === (int)current_user()['id']) return __('err_self_delete');
        if ($r['role'] === 'admin' && (int)val("SELECT COUNT(*) FROM users WHERE role='admin' AND active=1") <= 1) return __('err_last_admin');
        return null;
    },
]);
