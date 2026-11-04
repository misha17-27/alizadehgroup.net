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
$slash = strlen($path) > 1 && substr($path, -1) === '/';
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

function query_suffix(): string
{
    $qs = (string)($_SERVER['QUERY_STRING'] ?? '');
    return $qs !== '' ? '?' . $qs : '';
}

/** 301 for addresses that moved: the /yeni preview and the pages of the old WordPress site */
function legacy_redirect(string $path): void
{
    if (!is_get()) return;
    if ($path === 'yeni' || starts_with($path, 'yeni/')) {
        redirect(url(implode('/', array_map('rawurlencode', explode('/', substr($path, 5))))) . query_suffix(), 301);
    }
    if (isset(LEGACY_URLS[$path])) {
        [$name, $l, $key] = LEGACY_URLS[$path] + [2 => ''];
        $s = $key !== '' ? service_by_key($key) : null;
        if ($s && !empty($s['active'])) redirect(route('service', ['service' => $s], $l), 301);
        redirect(route($name === 'service' ? 'services' : $name, [], $l), 301);
    }
    // WordPress query links (?p=12, ?page_id=3, ?s=…) on the home page
    if ($path === '' && array_intersect_key($_GET, array_flip(['p', 'page_id', 'cat', 'tag', 'author', 's', 'attachment_id', 'm', 'feed']))) {
        redirect(url(''), 301);
    }
}

/** pages live without a trailing slash: /haqqimizda/ → 301 /haqqimizda */
function canonical(string $url, bool $slash): void
{
    if ($slash && is_get()) redirect($url . query_suffix(), 301);
}

function item_meta(array $r): array
{
    return ['title' => L($r, 'meta_title') ?: L($r, 'title') . ' — Alizadeh Group', 'desc' => L($r, 'meta_desc') ?: L($r, 'short'), 'image' => $r['image']];
}

function item_alt(string $route, array $r): array
{
    return alt_routes($route, ['row' => $r]);
}

/** the pages of an optional section (app/site/sections.php); $parts: what follows its address */
function section_page(string $name, array $parts, bool $slash): void
{
    if (in_array('', $parts, true)) not_found();
    $lng = lang();
    $notOpen = section_state($name) !== 'public';     // a signed-in user looking at a section that visitors do not see yet
    $vars = ['nav' => $name, 'preview' => $notOpen, 'noindex' => $notOpen];
    $pg = (int)($_GET['p'] ?? 1);
    $slug = $parts[0] ?? '';

    if ($name === 'products') {
        $cat = $slug !== '' ? row_by_slug('product_categories', $slug, $lng) : null;
        if (count($parts) > 2 || ($slug !== '' && !$cat)) not_found();
        if (isset($parts[1])) {
            $r = row(PRODUCT_SELECT . ' AND p.slug_' . $lng . '=? AND p.category_id=?', [$parts[1], $cat['id']]);
            if (!$r) not_found();
            canonical(route('product', ['row' => $r]), $slash);
            render('product', $vars + ['r' => $r, 'meta' => item_meta($r), 'alt' => item_alt('product', $r)]);
        }
        canonical($cat ? route('category', ['row' => $cat]) : route('products'), $slash);
        [$list, $pg, $pages] = paged(PRODUCT_SELECT . ($cat ? ' AND p.category_id=?' : ''), $cat ? [$cat['id']] : [], 'p.sort, p.id', $pg);
        $meta = $cat ? ['title' => L($cat, 'title') . ' — Alizadeh Group', 'desc' => L($cat, 'text'), 'image' => $cat['image']] : page_meta('products');
        render('products', $vars + ['cat' => $cat, 'list' => $list, 'pg' => $pg, 'pages' => $pages, 'meta' => $meta,
            'alt' => $cat ? item_alt('category', $cat) : alt_routes('products')]);
    }

    [$route, $table, $order] = $name === 'blog' ? ['post', 'posts', 'published_at DESC, id DESC'] : ['project', 'projects', 'sort, id'];
    if (count($parts) > 1) not_found();
    if ($slug !== '') {
        $r = row_by_slug($table, $slug, $lng);
        if (!$r) not_found();
        canonical(route($route, ['row' => $r]), $slash);
        render($route, $vars + ['r' => $r, 'meta' => item_meta($r), 'alt' => item_alt($route, $r)]);
    }
    canonical(route($name), $slash);
    [$list, $pg, $pages] = paged('SELECT * FROM ' . $table . ' WHERE active=1', [], $order, $pg);
    render($name, $vars + ['list' => $list, 'pg' => $pg, 'pages' => $pages, 'meta' => page_meta($name), 'alt' => alt_routes($name)]);
}

try {
    ensure_db();
    legacy_redirect($path);

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
            foreach (section_urls($l) as $u) $urls[] = $u;
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
            canonical(route($name), $slash);
            render($name, ['meta' => page_meta($name), 'alt' => alt_routes($name), 'nav' => $name]);
        }
    }

    $svcPrefix = $lng === 'en' ? 'services/' : 'xidmetler/';
    if (starts_with($rel, $svcPrefix)) {
        $s = service_by_slug(substr($rel, strlen($svcPrefix)), $lng);
        if ($s) {
            canonical(route('service', ['service' => $s]), $slash);
            render('service', [
                's' => $s,
                'meta' => ['title' => L($s, 'meta_title') ?: L($s, 'title') . ' — Alizadeh Group', 'desc' => L($s, 'meta_desc') ?: L($s, 'page_desc'), 'image' => $s['image']],
                'alt' => alt_routes('service', ['service' => $s]),
                'nav' => 'services',
            ]);
        }
    }

    /* ---------- optional sections: a closed one answers like a page that does not exist ---------- */
    foreach (SECTIONS as $name) {
        $root = $lng === 'en' ? substr(ROUTES[$name]['en'], 3) : ROUTES[$name]['az'];
        if ($rel === $root || starts_with($rel, $root . '/')) {
            if (!section_open($name)) not_found();
            section_page($name, $rel === $root ? [] : explode('/', substr($rel, strlen($root) + 1)), $slash);
        }
    }

    not_found();
} catch (Throwable $e) {
    log_line('app.log', $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><title>Alizadeh Group</title><p style="font:16px system-ui;padding:40px">Texniki xəta baş verdi. Zəhmət olmasa bir az sonra yenidən cəhd edin.</p>';
}
