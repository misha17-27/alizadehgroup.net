<?php
/** list of posts, newest first: @var array $list @var int $pg @var int $pages */
$p = page('blog');
$body = L($p, 'body');
$ph_title = page_h1($p, 'blog.title');
$ph_text = page_lead($p);
$ph_image = $p['image'] ?? '';
$ph_crumbs = [[tx('blog.nav'), null]];
$ph_chip = '<span class="dot"></span>' . e(tx('blog.nav'));
include __DIR__ . '/partials/page_hero.php';
?>
<section class="sec sec-sm">
  <div class="wrap">
    <?php if ($body !== ''): ?><div class="prose page-text"><?= $body ?></div><?php endif; ?>
    <?php if ($list): ?>
    <div class="cds">
      <?php foreach ($list as $r):
        $c_href = route('post', ['row' => $r]);
        $c_img = $r['image'];
        $c_meta = post_date($r);
        $c_title = L($r, 'title');
        $c_text = L($r, 'short');
        $c_go = tx('blog.more');
        include __DIR__ . '/partials/card.php';
      endforeach; ?>
    </div>
    <?php $base = route('blog'); include __DIR__ . '/partials/pager.php'; ?>
    <?php else: ?>
    <p class="none"><?= e(tx('blog.empty')) ?></p>
    <?php endif; ?>
  </div>
</section>
