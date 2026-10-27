<?php
/**
 * Site content: languages, routes and repositories (settings, texts, services, slides, partners, pages).
 */
declare(strict_types=1);

const LANGS = ['az', 'en'];

/** public route patterns per language */
const ROUTES = [
    'home' => ['az' => '', 'en' => 'en'],
    'about' => ['az' => 'haqqimizda', 'en' => 'en/about'],
    'services' => ['az' => 'xidmetler', 'en' => 'en/services'],
    'service' => ['az' => 'xidmetler/{slug}', 'en' => 'en/services/{slug}'],
    'partners' => ['az' => 'partnyorlar', 'en' => 'en/partners'],
    'contact' => ['az' => 'elaqe', 'en' => 'en/contact'],
];

function lang(?string $set = null): string
{
    static $lang = 'az';
    if ($set !== null && in_array($set, LANGS, true)) $lang = $set;
    return $lang;
}

/** pick a translated column: L($row, 'title') → title_az / title_en (falls back to AZ) */
function L(array $row, string $field, ?string $lng = null): string
{
    $lng = $lng ?: lang();
    $v = (string)($row[$field . '_' . $lng] ?? '');
    if ($v === '' && $lng !== 'az') $v = (string)($row[$field . '_az'] ?? '');
    return $v;
}

/** URL of a public route */
function route(string $name, array $params = [], ?string $lng = null): string
{
    $lng = $lng ?: lang();
    $p = ROUTES[$name][$lng] ?? '';
    if ($name === 'service' && isset($params['service'])) {
        $p = str_replace('{slug}', (string)$params['service']['slug_' . $lng], $p);
    }
    return url($p === '' ? '' : $p);
}

/* ---------------- settings & texts ---------------- */

function settings(): array
{
    static $s = null;
    if ($s === null) {
        $s = [];
        try {
            foreach (rows('SELECT k, v FROM settings') as $r) $s[$r['k']] = (string)$r['v'];
        } catch (Throwable $e) {
            $s = [];
        }
    }
    return $s;
}

function setting(string $k, string $default = ''): string
{
    $s = settings();
    return isset($s[$k]) && $s[$k] !== '' ? $s[$k] : $default;
}

function texts(): array
{
    static $t = null;
    if ($t === null) {
        $t = [];
        foreach (rows('SELECT k, az, en FROM texts') as $r) $t[$r['k']] = $r;
    }
    return $t;
}

/** translated UI/section text by key */
function tx(string $key, ?string $lng = null): string
{
    $lng = $lng ?: lang();
    $t = texts()[$key] ?? null;
    if (!$t) return $key;
    $v = (string)($t[$lng] ?? '');
    return $v !== '' ? $v : (string)$t['az'];
}

/* ---------------- repositories ---------------- */

function services_list(bool $onlyActive = true): array
{
    static $cache = [];
    $k = $onlyActive ? 1 : 0;
    if (!isset($cache[$k])) {
        $cache[$k] = rows('SELECT * FROM services' . ($onlyActive ? ' WHERE active=1' : '') . ' ORDER BY sort, id');
    }
    return $cache[$k];
}

function service_by_slug(string $slug, string $lng): ?array
{
    foreach (services_list() as $s) {
        if ($s['slug_' . $lng] === $slug) return $s;
    }
    return null;
}

function service_by_key(string $key): ?array
{
    foreach (services_list(false) as $s) {
        if ($s['skey'] === $key) return $s;
    }
    return null;
}

function items(string $grp, bool $onlyActive = true): array
{
    return rows('SELECT * FROM items WHERE grp=?' . ($onlyActive ? ' AND active=1' : '') . ' ORDER BY sort, id', [$grp]);
}

function slides(): array
{
    return rows('SELECT * FROM slides WHERE active=1 ORDER BY sort, id');
}

function partners(): array
{
    return rows('SELECT * FROM partners WHERE active=1 ORDER BY sort, id');
}

function page(string $k): array
{
    static $cache = [];
    if (!isset($cache[$k])) $cache[$k] = row('SELECT * FROM pages WHERE k=?', [$k]) ?: ['k' => $k, 'image' => ''];
    return $cache[$k];
}

/** decode a JSON list column */
function jlist($v): array
{
    $a = json_decode((string)$v, true);
    return is_array($a) ? $a : [];
}

/** value of a JSON-row field in the current language: jl($row, 't') → t_az / t_en, jl($row) → az / en */
function jl(array $r, string $f = '', ?string $lng = null): string
{
    $lng = $lng ?: lang();
    $k = $f === '' ? $lng : $f . '_' . $lng;
    $ka = $f === '' ? 'az' : $f . '_az';
    $v = (string)($r[$k] ?? '');
    return $v !== '' ? $v : (string)($r[$ka] ?? '');
}

/** resolve a slide/item link: "contact", "services", "service:<key>", "#anchor" or a URL */
function link_url(string $link): string
{
    $link = trim($link);
    if ($link === '') return '';
    if (starts_with($link, 'service:')) {
        $s = service_by_key(substr($link, 8));
        return $s ? route('service', ['service' => $s]) : route('services');
    }
    if (isset(ROUTES[$link]) && $link !== 'service') return route($link);
    return $link;
}

function whatsapp_url(): string
{
    $n = preg_replace('~\D+~', '', setting('whatsapp'));
    return $n ? 'https://wa.me/' . $n : '';
}

function map_embed_url(): string
{
    $q = setting('map_query');
    return $q !== '' ? 'https://maps.google.com/maps?q=' . rawurlencode($q) . '&z=16&output=embed' : '';
}
