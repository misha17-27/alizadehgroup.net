<?php
/**
 * Admin core: UI language, authentication, roles, navigation and rendering.
 * Entry point: admin/index.php?section=...
 */
declare(strict_types=1);

require __DIR__ . '/forms.php';
require __DIR__ . '/crud.php';

const ADMIN_UI_LANGS = ['az' => 'AZ', 'ru' => 'RU'];

/* ------------------------------------------------------------ UI language */

function admin_ui_lang(): string
{
    $u = current_user();
    $l = $u['ui_lang'] ?? ($_SESSION['ui_lang'] ?? 'az');
    return isset(ADMIN_UI_LANGS[$l]) ? $l : 'az';
}

/** admin UI string */
function __(string $key, array $repl = []): string
{
    static $dict = [];
    $l = admin_ui_lang();
    if (!isset($dict[$l])) $dict[$l] = require __DIR__ . '/lang/' . $l . '.php';
    $s = $dict[$l][$key] ?? $key;
    foreach ($repl as $k => $v) $s = str_replace('{' . $k . '}', (string)$v, $s);
    return $s;
}

/** content language being edited (?cl=az|en, remembered in the session) */
function content_lang(): string
{
    if (isset($_GET['cl']) && in_array($_GET['cl'], LANGS, true)) $_SESSION['cl'] = $_GET['cl'];
    $cl = $_SESSION['cl'] ?? 'az';
    return in_array($cl, LANGS, true) ? $cl : 'az';
}

/* ------------------------------------------------------------ URLs */

function admin_url(string $section = 'dashboard', array $params = []): string
{
    $q = array_merge(['section' => $section], $params);
    return url('admin/index.php') . '?' . http_build_query($q);
}

/* ------------------------------------------------------------ auth */

function current_user(): ?array
{
    static $user = false;
    if ($user !== false) return $user;
    $id = (int)($_SESSION['uid'] ?? 0);
    $user = $id ? row('SELECT * FROM users WHERE id=? AND active=1', [$id]) : null;
    return $user;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function require_admin(): void
{
    if (!is_admin()) {
        flash(__('no_access'), 'err');
        redirect(admin_url());
    }
}

function login_blocked(string $ip): bool
{
    $since = date('Y-m-d H:i:s', time() - 900);
    return (int)val('SELECT COUNT(*) FROM login_attempts WHERE ip=? AND ok=0 AND created_at>?', [$ip, $since]) >= 6;
}

function attempt_login(string $email, string $pass): bool
{
    $ip = client_ip();
    $u = row('SELECT * FROM users WHERE email=? AND active=1', [mb_strtolower(trim($email))]);
    $ok = $u && password_verify($pass, $u['pass']);
    insert('login_attempts', ['ip' => $ip, 'email' => mb_substr($email, 0, 160), 'ok' => $ok ? 1 : 0, 'created_at' => now()]);
    if (!$ok) return false;
    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$u['id'];
    unset($_SESSION['_csrf']);
    if (password_needs_rehash($u['pass'], PASSWORD_DEFAULT)) update('users', ['pass' => password_hash($pass, PASSWORD_DEFAULT)], 'id=?', [$u['id']]);
    update('users', ['last_login' => now()], 'id=?', [$u['id']]);
    q('DELETE FROM login_attempts WHERE created_at<?', [date('Y-m-d H:i:s', time() - 86400 * 30)]);
    return true;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'] ?? '', $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ------------------------------------------------------------ navigation */

function admin_nav(): array
{
    $new = (int)val("SELECT COUNT(*) FROM requests WHERE status='new'");
    $nav = [
        'g_main' => [
            'dashboard' => ['home', __('nav_dashboard')],
            'requests' => ['mail', __('nav_requests'), $new],
        ],
        'g_content' => [
            'slides' => ['layers', __('nav_slides')],
            'info' => ['grid', __('nav_info')],
            'about' => ['users', __('nav_about')],
            'why' => ['star', __('nav_why')],
            'services' => ['briefcase', __('nav_services')],
            'partners' => ['globe', __('nav_partners')],
        ],
        'g_site' => [
            'texts' => ['sliders', __('nav_texts')],
            'pages' => ['monitor', __('nav_pages')],
            'settings' => ['gear', __('nav_settings')],
        ],
        'g_system' => [
            'users' => ['shield', __('nav_users')],
            'profile' => ['user', __('nav_profile')],
        ],
    ];
    if (!is_admin()) unset($nav['g_site']['settings'], $nav['g_system']['users']);
    return $nav;
}

/* ------------------------------------------------------------ rendering */

/**
 * Render an admin view inside the layout.
 * $vars: title (h1), cl (true → show the content-language switch), any view variables.
 */
function admin_render(string $view, array $vars = [], int $code = 200): void
{
    http_response_code($code);
    $vars += ['title' => '', 'cl' => false];
    extract($vars, EXTR_SKIP);
    ob_start();
    include APP_DIR . '/views/admin/' . $view . '.php';
    $body = ob_get_clean();
    include APP_DIR . '/views/admin/layout.php';
    exit;
}

/** standalone page (login / errors) */
function admin_page(string $view, array $vars = [], int $code = 200): void
{
    http_response_code($code);
    extract($vars, EXTR_SKIP);
    include APP_DIR . '/views/admin/' . $view . '.php';
    exit;
}

/** POST guard for every admin form */
function admin_post_ok(): bool
{
    if (!is_post()) return false;
    if (!csrf_ok()) {
        flash(__('csrf_error'), 'err');
        return false;
    }
    return true;
}

function status_labels(): array
{
    return ['new' => __('st_new'), 'progress' => __('st_progress'), 'done' => __('st_done'), 'spam' => __('st_spam')];
}

function fmt_date(?string $dt, bool $time = true): string
{
    if (!$dt) return '—';
    $t = strtotime($dt);
    return $t ? date($time ? 'd.m.Y H:i' : 'd.m.Y', $t) : '—';
}

function fmt_size(int $b): string
{
    return $b >= 1048576 ? round($b / 1048576, 1) . ' MB' : max(1, (int)round($b / 1024)) . ' KB';
}

/* ------------------------------------------------------------ dispatcher */

function admin_run(): void
{
    start_session();
    send_security_headers();
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');

    if (!is_installed()) redirect(url('install.php'));

    $section = preg_replace('~[^a-z_]~', '', (string)($_GET['section'] ?? 'dashboard')) ?: 'dashboard';

    if ($section === 'login' || !current_user()) {
        require __DIR__ . '/sections/login.php';
        return;
    }
    if ($section === 'logout') {
        if (hash_equals(csrf_token(), (string)($_GET['t'] ?? ''))) logout();
        redirect(admin_url('login'));
    }
    if (isset($_GET['ui']) && isset(ADMIN_UI_LANGS[$_GET['ui']])) {
        update('users', ['ui_lang' => $_GET['ui']], 'id=?', [current_user()['id']]);
        $back = $_GET;
        unset($back['ui']);
        redirect(url('admin/index.php') . '?' . http_build_query($back));
    }

    $file = __DIR__ . '/sections/' . $section . '.php';
    if (!is_file($file)) $file = __DIR__ . '/sections/dashboard.php';
    require $file;
}
