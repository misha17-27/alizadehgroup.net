<?php
/**
 * Optional sections: product catalog, projects, blog. Each is switched in Settings › Sections:
 *   hidden — nowhere (the default), admin — in the admin panel, on the site only for signed-in users, public — for everybody.
 */
declare(strict_types=1);

/** in menu order */
const SECTIONS = ['products', 'projects', 'blog'];
const SECTION_PER_PAGE = 12;

const PRODUCT_SELECT = 'SELECT p.*, c.slug_az AS cat_slug_az, c.slug_en AS cat_slug_en, c.title_az AS cat_title_az, c.title_en AS cat_title_en, c.image AS cat_image
    FROM products p JOIN product_categories c ON c.id=p.category_id WHERE p.active=1 AND c.active=1';

/** a project may have no category, or one that is switched off: then it is shown without it */
const PROJECT_SELECT = 'SELECT p.*, c.slug_az AS cat_slug_az, c.slug_en AS cat_slug_en, c.title_az AS cat_title_az, c.title_en AS cat_title_en
    FROM projects p LEFT JOIN project_categories c ON c.id=p.category_id AND c.active=1 WHERE p.active=1';

function section_state(string $key): string
{
    $state = setting('sec_' . $key, 'hidden');
    return $state === 'admin' || $state === 'public' ? $state : 'hidden';
}

/** sections open to everybody: key → [menu label, url] */
function public_sections(): array
{
    $out = [];
    foreach (SECTIONS as $k) {
        if (section_state($k) === 'public') $out[$k] = [tx($k . '.nav'), route($k)];
    }
    return $out;
}

/** may this visitor open the pages of a section? */
function section_open(string $key): bool
{
    $state = section_state($key);
    if ($state !== 'admin') return $state === 'public';
    if (empty($_COOKIE['agsid'])) return false;
    start_session();
    $uid = (int)($_SESSION['uid'] ?? 0);
    return $uid > 0 && (int)val('SELECT COUNT(*) FROM users WHERE id=? AND active=1', [$uid]) > 0;
}

function product_categories(): array
{
    static $all = null;
    if ($all === null) $all = rows('SELECT * FROM product_categories WHERE active=1 ORDER BY sort, id');
    return $all;
}

function project_categories(): array
{
    static $all = null;
    if ($all === null) $all = rows('SELECT * FROM project_categories WHERE active=1 ORDER BY sort, id');
    return $all;
}

/** "Sorğu göndər" on a product page opens the request form with ?mehsul=<id>: the name of that product, '' without one */
function requested_product(): string
{
    $id = (int)($_GET['mehsul'] ?? 0);
    if ($id < 1 || !section_open('products')) return '';
    $r = row(PRODUCT_SELECT . ' AND p.id=?', [$id]);
    return $r ? L($r, 'title') : '';
}

/** the WhatsApp link of a product page, with a message naming the product; '' when no WhatsApp number is set */
function product_whatsapp_url(array $r): string
{
    $wa = whatsapp_url();
    if ($wa === '') return '';
    $text = str_replace(['{name}', '{url}'], [L($r, 'title'), site_origin() . route('product', ['row' => $r])], tx('products.wa_text'));
    return $wa . '?text=' . rawurlencode($text);
}

/** an active row by its address in a language */
function row_by_slug(string $table, string $slug, string $lng): ?array
{
    return row('SELECT * FROM ' . $table . ' WHERE slug_' . $lng . '=? AND active=1', [$slug]);
}

/** one page of a list: [rows, page, number of pages] */
function paged(string $select, array $params, string $order, int $page): array
{
    $total = (int)val('SELECT COUNT(*) FROM (' . $select . ') t', $params);
    $pages = max(1, (int)ceil($total / SECTION_PER_PAGE));
    $page = min($pages, max(1, $page));
    $list = rows($select . ' ORDER BY ' . $order . ' LIMIT ' . SECTION_PER_PAGE . ' OFFSET ' . (($page - 1) * SECTION_PER_PAGE), $params);
    return [$list, $page, $pages];
}

/** the addresses of the open sections, for the sitemap */
function section_urls(string $lng): array
{
    $urls = [];
    foreach (public_sections() as $k => $_) $urls[] = route($k, [], $lng);
    if (section_state('products') === 'public') {
        foreach (product_categories() as $c) $urls[] = route('category', ['row' => $c], $lng);
        foreach (rows(PRODUCT_SELECT . ' ORDER BY p.id') as $r) $urls[] = route('product', ['row' => $r], $lng);
    }
    if (section_state('projects') === 'public') {
        foreach (project_categories() as $c) $urls[] = route('project_cat', ['row' => $c], $lng);
    }
    foreach (['projects' => ['project', 'projects'], 'blog' => ['post', 'posts']] as $k => [$route, $table]) {
        if (section_state($k) !== 'public') continue;
        foreach (rows('SELECT slug_az, slug_en FROM ' . $table . ' WHERE active=1 ORDER BY id') as $r) $urls[] = route($route, ['row' => $r], $lng);
    }
    return $urls;
}

/** "12.05.2026", or '' when there is no date */
function date_text(?string $d): string
{
    $t = strtotime((string)$d);
    return $t ? date('d.m.Y', $t) : '';
}

/** the id of a YouTube video from any of its usual links, '' when it is not one */
function youtube_id(string $url): string
{
    return preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:[^#\s]*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([\w-]{11})(?![\w-])~i', $url, $m) ? $m[1] : '';
}
