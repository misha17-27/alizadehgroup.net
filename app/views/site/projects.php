<?php
/** list of projects: @var array $list @var int $pg @var int $pages */
$p = page('projects');
$body = L($p, 'body');
$ph_title = page_h1($p, 'projects.title');
$ph_text = page_lead($p);
$ph_image = $p['image'] ?? '';
$ph_crumbs = [[tx('projects.nav'), null]];
$ph_chip = '<span class="dot"></span>' . e(tx('projects.nav'));
include __DIR__ . '/partials/page_hero.php';
?>
<section class="sec sec-sm">
  <div class="wrap">
    <?php if ($body !== ''): ?><div class="prose page-text"><?= $body ?></div><?php endif; ?>
    <?php if ($list): ?>
    <div class="cds">
      <?php foreach ($list as $r):
        $c_href = route('project', ['row' => $r]);
        $c_img = $r['image'];
        $c_meta = '';
        $c_title = L($r, 'title');
        $c_text = L($r, 'short');
        $c_go = tx('ui.more');
        include __DIR__ . '/partials/card.php';
      endforeach; ?>
    </div>
    <?php $base = route('projects'); include __DIR__ . '/partials/pager.php'; ?>
    <?php else: ?>
    <p class="none"><?= e(tx('projects.empty')) ?></p>
    <?php endif; ?>
  </div>
</section>
<section class="sec sec-sm" style="padding-top:0"><div class="wrap"><?php include __DIR__ . '/partials/cta.php'; ?></div></section>
