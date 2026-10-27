<?php
$p = page('about');
$bul = items('about');
$why = items('why');
$body = L($p, 'body');
$ph_title = page_h1($p, 'about.title');
$ph_text = page_lead($p);
$ph_image = $p['image'] ?? '';
$ph_crumbs = [[tx('nav.about'), null]];
$ph_chip = '<span class="dot"></span>' . e(tx('nav.about'));
include __DIR__ . '/partials/page_hero.php';
?>
<section class="sec">
  <div class="wrap about">
    <div class="about-media rv">
      <figure class="am1"><img src="<?= e(asset('assets/img/conference.jpg')) ?>" alt="" loading="lazy"></figure>
      <figure class="am2"><img src="<?= e(asset('assets/img/baku-airport.jpg')) ?>" alt="" loading="lazy"></figure>
      <div class="am-badge glass"><?= icon('clock') ?><span><?= e(tx('contact.hours')) ?></span></div>
    </div>
    <div class="rv">
      <h2 class="h2"><?= e(tx('about.title')) ?></h2>
      <?php if ($body !== ''): ?><div class="prose"><?= $body ?></div><?php else: ?><p class="lead"><?= e(tx('about.text')) ?></p><?php endif; ?>
      <?php if ($bul): ?><ul class="ab-list"><?php foreach ($bul as $b): ?><li class="glass"><?= icon($b['icon'] ?: 'check') ?><span><?= e(L($b, 'title')) ?><?php if (L($b, 'text') !== ''): ?><small><?= e(L($b, 'text')) ?></small><?php endif; ?></span></li><?php endforeach; ?></ul><?php endif; ?>
    </div>
  </div>
</section>

<?php if ($why): ?>
<section class="sec why">
  <div class="wrap">
    <div class="center rv"><p class="chip"><span class="dot"></span><?= e(tx('why.sub')) ?></p><h2 class="h2"><?= e(tx('why.title')) ?></h2></div>
    <div class="why-grid">
      <?php foreach ($why as $i => $w): ?>
      <div class="why-c glass rv" style="--d:<?= $i * 60 ?>ms"><span class="why-ic"><?= icon($w['icon']) ?></span><h3><?= e(L($w, 'title')) ?></h3><p><?= e(L($w, 'text')) ?></p><span class="why-no"><?= sprintf('%02d', $i + 1) ?></span></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="sec sec-sm">
  <div class="wrap">
    <div class="sec-head rv"><div><p class="chip"><span class="dot"></span><?= e(tx('nav.services')) ?></p><h2 class="h2"><?= e(tx('services.title')) ?></h2></div><p class="lead"><?= e(tx('services.text')) ?></p></div>
    <?php include __DIR__ . '/partials/service_cards.php'; ?>
  </div>
</section>

<?php if (partners()): ?>
<section class="sec sec-sm partners">
  <div class="wrap"><div class="center rv"><p class="chip"><span class="dot"></span><?= e(tx('nav.partners')) ?></p><h2 class="h2"><?= e(tx('partners.title')) ?></h2></div></div>
  <?php include __DIR__ . '/partials/partners_marquee.php'; ?>
</section>
<?php endif; ?>

<section class="sec sec-sm"><div class="wrap"><?php include __DIR__ . '/partials/cta.php'; ?></div></section>
