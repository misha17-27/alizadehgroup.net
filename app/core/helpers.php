<?php
/**
 * Generic helpers: escaping, URLs, CSRF, flash messages, misc.
 */
declare(strict_types=1);

/** HTML escape */
function e($v): string
{
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** escape + keep line breaks */
function nl2br_e($v): string
{
    return nl2br(e($v), false);
}

/** text → <p> paragraphs (blank line = new paragraph) */
function paragraphs($v, string $cls = ''): string
{
    $v = trim(str_replace("\r", '', (string)$v));
    if ($v === '') return '';
    $c = $cls !== '' ? ' class="' . e($cls) . '"' : '';
    $out = '';
    foreach (preg_split("/\n{2,}/", $v) as $p) {
        $out .= '<p' . $c . '>' . nl2br_e(trim($p)) . '</p>';
    }
    return $out;
}

/**
 * Rich text from the admin editor, safe to print as is: headings, paragraphs, lists, bold/italic and links stay,
 * every attribute except a link's http(s)/mailto/tel/relative href is dropped, any other tag is removed (its text stays).
 */
function clean_html(string $html): string
{
    $html = strip_tags(str_replace("\0", '', $html), '<p><div><br><h2><h3><h4><strong><b><em><i><u><ul><ol><li><blockquote><a>');
    // rebuild every remaining tag from scratch (quoted attribute values may contain ">")
    $html = preg_replace_callback('~<(/?)([a-z][a-z0-9]*)((?:[^>"\']|"[^"]*"|\'[^\']*\')*)>~i', function (array $m): string {
        $tag = strtolower($m[2]) === 'div' ? 'p' : strtolower($m[2]);
        if ($m[1] === '/') return $tag === 'br' ? '' : '</' . $tag . '>';
        if ($tag !== 'a') return '<' . $tag . '>';
        $href = preg_match('~\bhref\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'>]+))~i', $m[3], $h) ? $h[1] . ($h[2] ?? '') . ($h[3] ?? '') : '';
        $href = trim(preg_replace('~[\x00-\x1F\x7F]+~', '', html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        return preg_match('~^(https?://|mailto:|tel:|/|#)~i', $href) ? '<a href="' . e($href) . '">' : '<a>';
    }, $html);
    // tidy what contenteditable produces: lists and headings wrapped in <p>, empty paragraphs
    $html = preg_replace('~<p>\s*(<(?:ul|ol|h2|h3|h4|blockquote)>)~', '$1', $html);
    $html = preg_replace('~(</(?:ul|ol|h2|h3|h4|blockquote)>)\s*</p>~', '$1', $html);
    $html = preg_replace('~<p>(?:\s|&nbsp;|<br>)*</p>~', '', $html);
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return preg_replace('~[\s\x{00A0}]+~u', '', $text) === '' ? '' : trim($html);   // an empty editor leaves <p><br></p>
}

function starts_with(string $h, string $n): bool
{
    return $n === '' || strncmp($h, $n, strlen($n)) === 0;
}

/** URL inside the app: url('xidmetler') → /yeni/xidmetler */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return base_path() . '/' . $path;
}

/** asset URL with cache-busting for local files */
function asset(string $path): string
{
    if (preg_match('~^(https?:)?//~', $path)) return $path;
    $path = ltrim($path, '/');
    $file = APP_ROOT . '/' . $path;
    $v = is_file($file) ? '?v=' . substr((string)filemtime($file), -6) : '';
    return url($path) . $v;
}

/** public URL for an uploaded/seeded media path ('' → fallback) */
function media(?string $path, string $fallback = ''): string
{
    $path = trim((string)$path);
    if ($path === '') $path = $fallback;
    if ($path === '') return '';
    if (preg_match('~^(https?:)?//~', $path)) return $path;
    return url(ltrim($path, '/'));
}

function redirect(string $to, int $code = 302): void
{
    header('Location: ' . $to, true, $code);
    exit;
}

function json_out($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function is_get(): bool
{
    return in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true);
}

function post(string $key, $default = '')
{
    return $_POST[$key] ?? $default;
}

function str_post(string $key, int $max = 2000): string
{
    $v = $_POST[$key] ?? '';
    if (!is_string($v)) return '';
    $v = trim(str_replace("\0", '', $v));
    return mb_substr($v, 0, $max);
}

/** Cloudflare's networks (https://www.cloudflare.com/ips/, checked October 2026): only they may report the visitor's IP */
const CLOUDFLARE_NETS = ['173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18', '108.162.192.0/18',
    '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13', '104.24.0.0/14',
    '172.64.0.0/13', '131.0.72.0/22', '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32',
    '2a06:98c0::/29', '2c0f:f248::/32'];

/** is the IP inside the network ('162.158.0.0/15', '2606:4700::/32') */
function ip_in_net(string $ip, string $net): bool
{
    [$base, $bits] = explode('/', $net) + [1 => '128'];
    $a = @inet_pton($ip);
    $b = @inet_pton($base);
    if ($a === false || $b === false || strlen($a) !== strlen($b)) return false;
    $bytes = intdiv((int)$bits, 8);
    $rest = (int)$bits % 8;
    if (strncmp($a, $b, $bytes) !== 0) return false;
    return $rest === 0 || ((ord($a[$bytes]) ^ ord($b[$bytes])) & (0xFF << (8 - $rest)) & 0xFF) === 0;
}

/** visitor IP; the CF-Connecting-IP header counts only when the request came from Cloudflare (anyone else can fake it) */
function client_ip(): string
{
    $ip = preg_replace('~^::ffff:(?=\d+\.)~i', '', (string)($_SERVER['REMOTE_ADDR'] ?? ''));
    $cf = (string)($_SERVER['HTTP_CF_CONNECTING_IP'] ?? '');
    if ($cf !== '' && $ip !== '') {
        foreach (CLOUDFLARE_NETS as $net) {
            if (ip_in_net($ip, $net)) { $ip = $cf; break; }
        }
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

/* ---------------- CSRF ---------------- */
function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok(): bool
{
    start_session();
    $t = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($t) && !empty($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $t);
}

/* ---------------- flash ---------------- */
function flash(string $msg, string $type = 'ok'): void
{
    start_session();
    $_SESSION['_flash'][] = [$type, $msg];
}

function flashes(): array
{
    start_session();
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

/* ---------------- misc ---------------- */
function slugify(string $s): string
{
    $map = ['ə' => 'e', 'Ə' => 'e', 'ı' => 'i', 'İ' => 'i', 'ğ' => 'g', 'Ğ' => 'g', 'ş' => 's', 'Ş' => 's', 'ç' => 'c', 'Ç' => 'c',
        'ö' => 'o', 'Ö' => 'o', 'ü' => 'u', 'Ü' => 'u'];
    $s = strtr($s, $map);
    $s = strtolower((string)preg_replace('~[^A-Za-z0-9]+~', '-', $s));
    return trim($s, '-');
}

function tel_href(string $phone): string
{
    return 'tel:+' . ltrim((string)preg_replace('~\D+~', '', $phone), '+');
}

function uid(): string
{
    return bin2hex(random_bytes(6));
}

function log_line(string $file, string $msg): void
{
    @file_put_contents(STORAGE_DIR . '/logs/' . $file, '[' . now() . '] ' . $msg . "\n", FILE_APPEND | LOCK_EX);
}

/** verify Cloudflare Turnstile token (skipped when keys are not configured) */
function turnstile_ok(?string $token): bool
{
    $secret = setting('turnstile_secret');
    if ($secret === '' || setting('turnstile_site') === '') return true;
    if (!$token) return false;
    $ctx = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query(['secret' => $secret, 'response' => $token, 'remoteip' => client_ip()]),
        'timeout' => 8,
    ]]);
    $res = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $ctx);
    $j = $res ? json_decode($res, true) : null;
    return is_array($j) && !empty($j['success']);
}
