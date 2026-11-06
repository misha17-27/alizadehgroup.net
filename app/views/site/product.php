<?php
/** one product: photo, text, the request and WhatsApp buttons: @var array $r row with its category's cat_* columns */
$p = page('products');
$cat = ['slug_az' => $r['cat_slug_az'], 'slug_en' => $r['cat_slug_en']];
$body = L($r, 'body');
// the request form opens on the supply service with this product filled in; without that service as an "other request"
$req_service = 'diger';
foreach (services_list() as $s) {
    if ($s['form_type'] === 'supply') { $req_service = $s['skey']; break; }
}
$wa_href = product_whatsapp_url($r);
$ph_title = L($r, 'title');
$ph_text = L($r, 'short');
$ph_image = $r['cat_image'] ?: ($p['image'] ?? '');
$ph_crumbs = [[tx('products.nav'), route('products')], [L($r, 'cat_title'), route('category', ['row' => $cat])], [L($r, 'title'), null]];
$ph_chip = '<span class="dot"></span>' . e(L($r, 'cat_title'));
include __DIR__ . '/partials/page_hero.php';
?>
<section class="sec sec-sm">
  <div class="wrap pd<?= $r['image'] === '' ? ' pd-one' : '' ?>">
    <?php if ($r['image'] !== ''): ?><figure class="pd-img glass"><?= picture($r['image'], '', 'alt="' . e(L($r, 'title')) . '"', '(max-width: 960px) 100vw, 50vw') ?></figure><?php endif; ?>
    <div>
      <?php if ($body !== ''): ?><div class="prose"><?= $body ?></div><?php endif; ?>
      <div class="pd-btns">
        <a class="btn btn-lime" href="<?= e(route('contact') . '?xidmet=' . rawurlencode($req_service) . '&mehsul=' . (int)$r['id'] . '#form') ?>"><?= e(tx('nav.request')) ?><?= icon('arrow') ?></a>
        <?php if ($wa_href !== ''): ?><a class="btn btn-glass" href="<?= e($wa_href) ?>" target="_blank" rel="noopener"><?= icon('wa') ?><?= e(tx('products.whatsapp')) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
</section>
