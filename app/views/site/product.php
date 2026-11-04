<?php
/** one product: @var array $r row with its category's cat_* columns */
$p = page('products');
$cat = ['slug_az' => $r['cat_slug_az'], 'slug_en' => $r['cat_slug_en']];
$body = L($r, 'body');
$ph_title = L($r, 'title');
$ph_text = L($r, 'short');
$ph_image = $r['cat_image'] ?: ($p['image'] ?? '');
$ph_crumbs = [[tx('products.nav'), route('products')], [L($r, 'cat_title'), route('category', ['row' => $cat])], [L($r, 'title'), null]];
$ph_chip = '<span class="dot"></span>' . e(L($r, 'cat_title'));
include __DIR__ . '/partials/page_hero.php';
?>
<?php if ($r['image'] !== '' || $body !== ''): ?>
<section class="sec sec-sm">
  <div class="wrap pd<?= $r['image'] === '' || $body === '' ? ' pd-one' : '' ?>">
    <?php if ($r['image'] !== ''): ?><figure class="pd-img glass"><?= picture($r['image'], '', 'alt="' . e(L($r, 'title')) . '"', '(max-width: 960px) 100vw, 50vw') ?></figure><?php endif; ?>
    <?php if ($body !== ''): ?><div class="prose"><?= $body ?></div><?php endif; ?>
  </div>
</section>
<?php endif; ?>
<section class="sec sec-sm" style="padding-top:0"><div class="wrap"><?php include __DIR__ . '/partials/cta.php'; ?></div></section>
