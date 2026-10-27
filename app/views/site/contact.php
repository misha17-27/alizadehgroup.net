<?php
$p = page('contact');
$ph_title = tx('contact.title');
$ph_text = L($p, 'description');
$ph_image = $p['image'] ?? '';
$ph_crumbs = [[tx('nav.contact'), null]];
$ph_chip = '<span class="dot"></span>' . e(tx('nav.contact'));
include __DIR__ . '/partials/page_hero.php';
$map = map_embed_url();
?>
<section class="sec">
  <div class="wrap">
    <div class="contact glass" id="form">
      <div class="contact-l">
        <h2 class="h2"><?= e(tx('contact.title')) ?></h2><p class="lead"><?= e(tx('contact.text')) ?></p>
        <?php include __DIR__ . '/partials/contact_list.php'; ?>
        <?php include __DIR__ . '/partials/socials.php'; ?>
      </div>
      <div class="contact-r"><?php include __DIR__ . '/partials/form.php'; ?></div>
    </div>
    <?php if ($map): ?>
    <div class="map glass rv">
      <iframe src="<?= e($map) ?>" title="<?= e(tx('contact.map')) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    </div>
    <?php endif; ?>
  </div>
</section>
