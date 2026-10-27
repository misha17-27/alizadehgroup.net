<?php
$p = page('services');
$body = L($p, 'body');
$ph_title = page_h1($p, 'services.title');
$ph_text = page_lead($p);
$ph_image = $p['image'] ?? '';
$ph_crumbs = [[tx('nav.services'), null]];
$ph_chip = '<span class="dot"></span>' . e(tx('nav.services'));
include __DIR__ . '/partials/page_hero.php';
?>
<section class="sec">
  <div class="wrap">
    <?php if ($body !== ''): ?><div class="prose page-text"><?= $body ?></div><?php endif; ?>
    <?php include __DIR__ . '/partials/service_cards.php'; ?>
  </div>
</section>
<section class="sec sec-sm" style="padding-top:0"><div class="wrap"><?php include __DIR__ . '/partials/cta.php'; ?></div></section>
