<?php
/** Dashboard: counters, requests by service, latest requests. */
$stats = [
    'new' => (int)val("SELECT COUNT(*) FROM requests WHERE status='new'"),
    'month' => (int)val('SELECT COUNT(*) FROM requests WHERE created_at>=?', [date('Y-m-01 00:00:00')]),
    'total' => (int)val('SELECT COUNT(*) FROM requests'),
    'services' => (int)val('SELECT COUNT(*) FROM services WHERE active=1'),
    'partners' => (int)val('SELECT COUNT(*) FROM partners WHERE active=1'),
];
$byService = rows('SELECT service_title, COUNT(*) AS n FROM requests GROUP BY service_title ORDER BY n DESC');
$latest = rows('SELECT * FROM requests ORDER BY id DESC LIMIT 8');

/* for administrators: the PHP version that really runs (cPanel shows the one chosen) and server limits the site depends on */
$server = null;
if (is_admin()) {
    $bytes = function (string $key): int {
        $v = strtolower(trim((string)ini_get($key)));
        return (int)$v * (['k' => 1024, 'm' => 1048576, 'g' => 1073741824][substr($v, -1)] ?? 1);
    };
    $limit = min(array_filter([$bytes('upload_max_filesize'), $bytes('post_max_size')]) ?: [0]);
    $warn = [];
    if ($limit && $limit < DOC_MAX) $warn[] = __('srv_upload_low', ['size' => fmt_size($limit)]);
    if (!function_exists('curl_init') && !ini_get('allow_url_fopen')) $warn[] = __('srv_no_http');
    if (!function_exists('mail')) $warn[] = __('srv_no_mail');
    if (!function_exists('imagecreatetruecolor')) $warn[] = __('srv_no_gd');
    elseif (!function_exists('imagewebp')) $warn[] = __('srv_no_webp');
    // OPcache keeps compiled PHP in memory; without it every page is compiled again
    // (no status when the API is restricted: then only "opcache.enable = 0" counts as off)
    $opcache = function_exists('opcache_get_status')
        ? ((@opcache_get_status(false) ?: [])['opcache_enabled'] ?? (ini_get('opcache.enable') ? null : false))
        : false;
    if ($opcache === false) $warn[] = __('srv_no_opcache');
    $server = [
        'php' => PHP_VERSION . ($opcache ? ' · OPcache' : ''),
        'db' => (db_driver() === 'sqlite' ? 'SQLite ' : 'MySQL ') . db()->getAttribute(PDO::ATTR_SERVER_VERSION),
        'upload' => $limit ? fmt_size($limit) : '—',
        'warn' => $warn,
    ];
}

admin_render('dashboard', ['title' => __('nav_dashboard'), 'stats' => $stats, 'latest' => $latest, 'byService' => $byService, 'server' => $server]);
