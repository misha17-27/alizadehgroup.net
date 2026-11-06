<?php
/** list of projects, or those of one category: @var ?array $cat @var array $list @var int $pg @var int $pages */
$p = page('projects');
$cats = project_categories();
$body = $cat ? '' : L($p, 'body');
$ph_title = $cat ? L($cat, 'title') : page_h1($p, 'projects.title');
$ph_text = $cat ? '' : page_lead($p);
$ph_image = ($cat['image'] ?? '') ?: ($p['image'] ?? '');
$ph_crumbs = $cat ? [[tx('projects.nav'), route('projects')], [L($cat, 'title'), null]] : [[tx('projects.nav'), null]];
$ph_chip = '<span class="dot"></span>' . e(tx('projects.nav'));
include __DIR__ . '/partials/page_hero.php';
?>
<section class="sec sec-sm">
  <div class="wrap">
    <?php if ($body !== ''): ?><div class="prose page-text"><?= $body ?></div><?php endif; ?>
    <?php if ($cats): ?>
    <nav class="pills" aria-label="<?= e(tx('projects.categories')) ?>">
      <a href="<?= e(route('projects')) ?>"<?= $cat ? '' : ' class="is-on" aria-current="page"' ?>><?= e(tx('projects.all')) ?></a>
      <?php foreach ($cats as $c): ?><a href="<?= e(route('project_cat', ['row' => $c])) ?>"<?= $cat && (int)$cat['id'] === (int)$c['id'] ? ' class="is-on" aria-current="page"' : '' ?>><?= e(L($c, 'title')) ?></a><?php endforeach; ?>
    </nav>
    <?php endif; ?>
    <?php if ($list): ?>
    <div class="cds">
      <?php foreach ($list as $r):
        $c_href = route('project', ['row' => $r]);
        $c_img = $r['image'];
        $c_meta = implode(' · ', array_filter([$cat ? '' : L($r, 'cat_title'), date_text($r['project_date'])], 'strlen'));
        $c_title = L($r, 'title');
        $c_text = L($r, 'short');
        $c_go = tx('ui.more');
        include __DIR__ . '/partials/card.php';
      endforeach; ?>
    </div>
    <?php $base = $cat ? route('project_cat', ['row' => $cat]) : route('projects'); include __DIR__ . '/partials/pager.php'; ?>
    <?php else: ?>
    <p class="none"><?= e(tx('projects.empty')) ?></p>
    <?php endif; ?>
  </div>
</section>
<section class="sec sec-sm" style="padding-top:0"><div class="wrap"><?php include __DIR__ . '/partials/cta.php'; ?></div></section>
