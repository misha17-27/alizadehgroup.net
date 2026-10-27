<?php
/** Login screen (also handles ?ui=az|ru before sign-in). */
if (isset($_GET['ui']) && isset(ADMIN_UI_LANGS[$_GET['ui']])) $_SESSION['ui_lang'] = $_GET['ui'];
if (current_user()) redirect(admin_url());

$error = '';
$email = '';
if (is_post()) {
    $email = trim((string)($_POST['email'] ?? ''));
    if (!csrf_ok()) $error = __('csrf_error');
    elseif (login_blocked(client_ip())) $error = __('login_blocked');
    elseif (!turnstile_ok($_POST['cf-turnstile-response'] ?? null)) $error = __('captcha_error');
    elseif (attempt_login($email, (string)($_POST['password'] ?? ''))) redirect(admin_url());
    else $error = __('login_failed');
}
admin_page('login', ['error' => $error, 'email' => $email]);
