<?php
/**
 * Alizadeh Group — application bootstrap (shared by site and admin).
 * PHP 7.4+, PDO (MySQL or SQLite).
 */
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('APP_DIR', __DIR__);
define('STORAGE_DIR', APP_ROOT . '/storage');
define('UPLOAD_DIR', APP_ROOT . '/uploads');
define('CONFIG_FILE', APP_DIR . '/config.php');

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_DIR . '/logs/php-error.log');
mb_internal_encoding('UTF-8');
date_default_timezone_set('Asia/Baku');

require APP_DIR . '/core/helpers.php';
require APP_DIR . '/core/db.php';
require APP_DIR . '/core/icons.php';
require APP_DIR . '/core/uploads.php';
require APP_DIR . '/site/content.php';
require APP_DIR . '/site/request.php';

/** config (app/config.php is created by install.php and is not in git) */
function config(?string $key = null, $default = null)
{
    $cfg = config_all();
    if ($key === null) return $cfg;
    return $cfg[$key] ?? $default;
}

/** whole config; pass an array to replace it for the current request (used by install.php) */
function config_all(?array $set = null): array
{
    static $cfg = null;
    if ($set !== null) $cfg = $set;
    if ($cfg === null) $cfg = is_file(CONFIG_FILE) ? (require CONFIG_FILE) : [];
    return $cfg;
}

function is_installed(): bool
{
    return is_file(CONFIG_FILE);
}

/** database version this code expects; an older database is upgraded on the first request after a deploy */
const DB_VERSION = 2;

function ensure_db(): void
{
    if ((int)setting('db_version', '1') >= DB_VERSION) return;
    require_once APP_DIR . '/database/migrate.php';
    upgrade_db();
}

/** base path of the app (works in a sub-folder such as /yeni) */
function base_path(): string
{
    static $base = null;
    if ($base !== null) return $base;
    $cfgBase = config('base_path');
    if ($cfgBase !== null && $cfgBase !== '') {
        return $base = rtrim((string)$cfgBase, '/');
    }
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $file = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME'] ?? '') ?: '');
    $root = str_replace('\\', '/', APP_ROOT);
    $rel = ($file !== '' && strpos($file, $root) === 0) ? substr($file, strlen($root)) : '/' . basename($script);
    $base = substr($script, 0, max(0, strlen($script) - strlen($rel)));
    return $base = rtrim($base, '/');
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['HTTP_CF_VISITOR'] ?? '') && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false);
}

function site_origin(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return (is_https() ? 'https://' : 'http://') . $host;
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $dir = STORAGE_DIR . '/sessions';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    if (is_dir($dir) && is_writable($dir)) {
        session_save_path($dir);
        ini_set('session.gc_probability', '1');      // own save path → make sure PHP cleans it up
        ini_set('session.gc_divisor', '100');
    }
    ini_set('session.gc_maxlifetime', '14400');       // 4 h idle
    ini_set('session.use_strict_mode', '1');
    session_name('agsid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => (base_path() ?: '') . '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Content-Security-Policy without frame-ancestors. The hosting (LiteSpeed) replaces the CSP header with its own
 * "upgrade-insecure-requests", so every page also carries these directives in a <meta> tag (csp_meta()).
 * Scripts and styles are not restricted (inline scripts, Google Analytics, Turnstile, Cloudflare).
 */
const CSP_DIRECTIVES = "base-uri 'self'; object-src 'none'; form-action 'self'";

function send_security_headers(): void
{
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()');
    // frame-ancestors works only as a header; X-Frame-Options covers it where the header gets replaced
    header("Content-Security-Policy: frame-ancestors 'self'; " . CSP_DIRECTIVES);
}

/** the CSP as a <meta> tag, right after <meta charset> */
function csp_meta(): string
{
    return '<meta http-equiv="Content-Security-Policy" content="' . e(CSP_DIRECTIVES) . '">';
}

foreach ([STORAGE_DIR . '/logs', STORAGE_DIR . '/requests'] as $d) {
    if (!is_dir($d)) @mkdir($d, 0775, true);
}
