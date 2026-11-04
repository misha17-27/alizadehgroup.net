<?php
/** catalog, or one category: @var ?array $cat @var array $list @var int $pg @var int $pages */
$p = page('products');
$cats = product_categories();
$body = $cat ? '' : L($p, 'body');
$ph_title = $cat ? L($cat, 'title') : page_h1($p, 'products.title');
$ph_text = $cat ? L($cat, 'text') : page_lead($p);
$ph_image = ($cat['image'] ?? '') ?: ($p['image'] ?? '');
$ph_crumbs = $cat ? [[tx('products.nav'), route('products')], [L($cat, 'title'), null]] : [[tx('products.nav'), null]];
$ph_chip = '<span class="dot"></span>' . e(tx('products.nav'));
include __DIR__ . '/partials/page_hero.php';
?>
<section class="sec sec-sm">
  <div class="wrap">
    <?php if ($body !== ''): ?><div class="prose page-text"><?= $body ?></div><?php endif; ?>
    <?php if ($cats): ?>
    <nav class="pills" aria-label="<?= e(tx('products.categories')) ?>">
      <a href="<?= e(route('products')) ?>"<?= $cat ? '' : ' class="is-on" aria-current="page"' ?>><?= e(tx('products.all')) ?></a>
      <?php foreach ($cats as $c): ?><a href="<?= e(route('category', ['row' => $c])) ?>"<?= $cat && (int)$cat['id'] === (int)$c['id'] ? ' class="is-on" aria-current="page"' : '' ?>><?= e(L($c, 'title')) ?></a><?php endforeach; ?>
    </nav>
    <?php endif; ?>
    <?php if ($list): ?>
    <div class="cds">
      <?php foreach ($list as $r):
        $c_href = route('product', ['row' => $r]);
        $c_img = $r['image'];
        $c_meta = $cat ? '' : L($r, 'cat_title');
        $c_title = L($r, 'title');
        $c_text = L($r, 'short');
        $c_go = tx('ui.more');
        include __DIR__ . '/partials/card.php';
      endforeach; ?>
    </div>
    <?php $base = $cat ? route('category', ['row' => $cat]) : route('products'); include __DIR__ . '/partials/pager.php'; ?>
    <?php else: ?>
    <p class="none"><?= e(tx('products.empty')) ?></p>
    <?php endif; ?>
  </div>
</section>
<section class="sec sec-sm" style="padding-top:0"><div class="wrap"><?php include __DIR__ . '/partials/cta.php'; ?></div></section>
