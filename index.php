<?php
/**
 * Alizadeh Group — public site front controller.
 * AZ (default) and EN (/en/...) routes, request form API, sitemap.
 */
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

if (!is_installed()) {
    redirect(url('install.php'));
}
send_security_headers();

$path = (string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$base = base_path();
if ($base !== '' && strpos($path, $base) === 0) $path = substr($path, strlen($base));
$path = trim(rawurldecode($path), '/');
if ($path === 'index.php') $path = '';

$lng = ($path === 'en' || starts_with($path, 'en/')) ? 'en' : 'az';
lang($lng);

/** render a site view inside the layout */
function render(string $view, array $vars = [], int $code = 200): void
{
    http_response_code($code);
    extract($vars, EXTR_SKIP);
    ob_start();
    include APP_DIR . '/views/site/' . $view . '.php';
    $content = ob_get_clean();
    include APP_DIR . '/views/site/layout.php';
    exit;
}

function not_found(): void
{
    $alt = ['az' => route('home', [], 'az'), 'en' => route('home', [], 'en')];
    render('404', ['meta' => ['title' => tx('e404.title') . ' — Alizadeh Group', 'desc' => '', 'image' => ''], 'alt' => $alt, 'nav' => '', 'noindex' => true], 404);
}

function page_meta(string $k): array
{
    $p = page($k);
    return ['title' => L($p, 'title') ?: 'Alizadeh Group', 'desc' => L($p, 'description'), 'image' => $p['image'] ?? ''];
}

function alt_routes(string $name, array $params = []): array
{
    $o = [];
    foreach (LANGS as $l) $o[$l] = route($name, $params, $l);
    return $o;
}

try {
    /* ---------- API: request form ---------- */
    if ($path === 'api/request' || $path === 'en/api/request') {
        if (!is_post()) json_out(['ok' => false], 405);
        start_session();
        $res = handle_request_submit();
        $ajax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
        if ($ajax) json_out($res, $res['ok'] ? 200 : 422);
        flash($res['msg'], $res['ok'] ? 'ok' : 'err');
        redirect(route('contact') . '#form');
    }

    /* ---------- sitemap / robots ---------- */
    if ($path === 'sitemap.xml') {
        header('Content-Type: application/xml; charset=utf-8');
        $urls = [];
        foreach (LANGS as $l) {
            foreach (['home', 'about', 'services', 'partners', 'contact'] as $r) $urls[] = route($r, [], $l);
            foreach (services_list() as $s) $urls[] = route('service', ['service' => $s], $l);
        }
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $u) echo '<url><loc>' . e(site_origin() . $u) . '</loc></url>';
        echo '</urlset>';
        exit;
    }
    if ($path === 'robots.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\nDisallow: " . url('admin/') . "\nDisallow: " . url('api/') . "\nSitemap: " . site_origin() . url('sitemap.xml') . "\n";
        exit;
    }

    // the session (CSRF + flash) is started lazily by the request form partial
    $rel = $lng === 'en' ? ltrim(substr($path, 2), '/') : $path;

    /* ---------- pages ---------- */
    $static = [
        'home' => ['az' => '', 'en' => ''],
        'about' => ['az' => 'haqqimizda', 'en' => 'about'],
        'services' => ['az' => 'xidmetler', 'en' => 'services'],
        'partners' => ['az' => 'partnyorlar', 'en' => 'partners'],
        'contact' => ['az' => 'elaqe', 'en' => 'contact'],
    ];
    foreach ($static as $name => $slugs) {
        if ($rel === $slugs[$lng]) {
            render($name, ['meta' => page_meta($name), 'alt' => alt_routes($name), 'nav' => $name]);
        }
    }

    $svcPrefix = $lng === 'en' ? 'services/' : 'xidmetler/';
    if (starts_with($rel, $svcPrefix)) {
        $s = service_by_slug(substr($rel, strlen($svcPrefix)), $lng);
        if ($s) {
            render('service', [
                's' => $s,
                'meta' => ['title' => L($s, 'meta_title') ?: L($s, 'title') . ' — Alizadeh Group', 'desc' => L($s, 'meta_desc') ?: L($s, 'page_desc'), 'image' => $s['image']],
                'alt' => alt_routes('service', ['service' => $s]),
                'nav' => 'services',
            ]);
        }
    }

    not_found();
} catch (Throwable $e) {
    log_line('app.log', $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><title>Alizadeh Group</title><p style="font:16px system-ui;padding:40px">Texniki xəta baş verdi. Zəhmət olmasa bir az sonra yenidən cəhd edin.</p>';
}
