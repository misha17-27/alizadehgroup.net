<?php
/** Site texts (UI labels and section texts) by group, edited in the selected content language. */
$cl = content_lang();
$groups = [
    'hero' => __('tg_hero'), 'info' => __('tg_info'), 'about' => __('tg_about'), 'why' => __('tg_why'),
    'services' => __('tg_services'), 'inner' => __('tg_inner'), 'partners' => __('tg_partners'), 'contact' => __('tg_contact'),
    'f' => __('tg_form'), 'nav' => __('tg_nav'), 'ui' => __('tg_ui'), 'footer' => __('tg_footer'), 'e404' => __('tg_404'),
    'products' => __('nav_products'), 'projects' => __('nav_projects'), 'blog' => __('nav_blog'), 'sections' => __('tg_sections'),
];
$have = array_column(rows('SELECT DISTINCT grp FROM texts'), 'grp');
foreach ($have as $g) if (!isset($groups[$g])) $groups[$g] = $g;
// the texts of hidden sections stay out of sight; "sections" holds what the open ones share
$closed = array_filter(SECTIONS, function ($k) { return section_state($k) === 'hidden'; });
foreach ($closed as $k) unset($groups[$k]);
if (count($closed) === count(SECTIONS)) unset($groups['sections']);
$g = (string)($_GET['g'] ?? 'hero');
if (!isset($groups[$g])) $g = 'hero';

if (is_post() && admin_post_ok()) {
    $vals = $_POST['t'] ?? [];
    if (is_array($vals)) {
        foreach ($vals as $k => $v) {
            if (!is_string($k) || !is_string($v)) continue;
            update('texts', [$cl => trim(str_replace("\0", '', $v))], 'k=? AND grp=?', [$k, $g]);
        }
    }
    flash(__('saved'));
    redirect(admin_url('texts', ['g' => $g]));
}

$items = rows('SELECT * FROM texts WHERE grp=? ORDER BY k', [$g]);
admin_render('texts', ['title' => __('nav_texts'), 'cl' => true, 'groups' => $groups, 'g' => $g, 'items' => $items, 'lng' => $cl]);
