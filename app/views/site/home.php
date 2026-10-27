<?php
$sl = slides();
$info = items('info');
$bul = items('about');
$why = items('why');
$aboutPage = page('about');
$homePage = page('home');
?>
<?php if ($sl): ?>
<section class="hs" data-slider data-autoplay="7000" data-swipe>
  <div class="hs-media">
    <?php foreach ($sl as $i => $s): ?>
    <div class="hs-bg<?= $i === 0 ? ' is-active' : '' ?>" data-slide="<?= $i ?>"><?= picture($s['image'], 'assets/img/baku-night.jpg', 'alt=""' . ($i === 0 ? ' fetchpriority="high"' : ''), '100vw', $i > 0) ?></div>
    <?php endforeach; ?>
  </div>
  <div class="hs-grid" aria-hidden="true"></div>
  <div class="wrap hs-in">
    <div class="hs-texts">
      <?php foreach ($sl as $i => $s):
        $href = link_url($s['link']);
        $isService = starts_with($s['link'], 'service:');
        $pick = $isService ? substr($s['link'], 8) : '';
      ?>
      <div class="hs-txt<?= $i === 0 ? ' is-active' : '' ?>" data-slide="<?= $i ?>">
        <?php if (L($s, 'eyebrow') !== ''): ?><p class="chip"><?= icon($i === 0 ? 'pin' : 'spark') ?><span><?= e(L($s, 'eyebrow')) ?></span></p><?php endif; ?>
        <?= $i === 0 ? '<h1>' : '<h2 class="hs-title">' ?><?= e(L($s, 'title')) ?><?= $i === 0 ? '</h1>' : '</h2>' ?>
        <?php if (L($s, 'text') !== ''): ?><p class="lead"><?= e(L($s, 'text')) ?></p><?php endif; ?>
        <div class="hs-cta">
          <?php if ($href !== ''): ?><a class="btn btn-lime" href="<?= e($href) ?><?= $s['link'] === 'contact' ? '#form' : '' ?>"><?= e(L($s, 'btn') ?: tx('ui.more')) ?><?= icon('arrow') ?></a><?php endif; ?>
          <?php if ($i === 0): ?>
            <a class="btn btn-glass" href="<?= e(route('services')) ?>"><?= e(tx('hero.cta2')) ?></a>
          <?php else: ?>
            <a class="btn btn-glass" href="<?= e(route('contact')) ?>?xidmet=<?= e(rawurlencode($pick)) ?>#form"><?= e(tx('nav.request')) ?></a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if (count($sl) > 1): ?>
    <div class="hs-tabs" style="--n:<?= count($sl) ?>">
      <?php foreach ($sl as $i => $s):
        $label = $i === 0 ? tx('hero.tab1') : (starts_with($s['link'], 'service:') && ($sv = service_by_key(substr($s['link'], 8))) ? L($sv, 'title') : L($s, 'title'));
      ?>
      <button type="button" class="hs-tab<?= $i === 0 ? ' is-active' : '' ?>" data-goto="<?= $i ?>"><i class="hs-prog"><i data-progress></i></i><b><?= sprintf('%02d', $i + 1) ?></b><span><?= e($label) ?></span></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($info): ?>
<section class="info" aria-label="<?= e(tx('nav.request')) ?>">
  <div class="wrap info-grid">
    <?php foreach ($info as $i => $c): ?>
    <a class="ic glass rv" href="<?= e(route('contact')) ?><?= $c['link'] !== '' ? '?xidmet=' . e(rawurlencode($c['link'])) : '' ?>#form" data-pick="<?= e($c['link']) ?>" style="--d:<?= $i * 80 ?>ms">
      <span class="ic-ico"><?= icon($c['icon']) ?></span><h3><?= e(L($c, 'title')) ?></h3><p><?= e(L($c, 'text')) ?></p>
      <span class="ic-go"><?= e(tx('info.go')) ?><?= icon('arrow-ur') ?></span></a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="sec" id="haqqimizda">
  <div class="wrap about">
    <div class="about-media rv">
      <figure class="am1"><?= picture(($homePage['photo1'] ?? '') ?: ($aboutPage['image'] ?? ''), 'assets/img/conference.jpg', 'alt="" loading="lazy"', '(max-width: 960px) 460px, 40vw') ?></figure>
      <figure class="am2"><?= picture($homePage['photo2'] ?? '', 'assets/img/baku-airport.jpg', 'alt="" loading="lazy"', '(max-width: 960px) 320px, 28vw') ?></figure>
      <div class="am-badge glass"><?= icon('clock') ?><span><?= e(tx('contact.hours')) ?></span></div>
    </div>
    <div class="rv">
      <p class="chip"><span class="dot"></span><?= e(tx('nav.about')) ?></p>
      <h2 class="h2"><?= e(tx('about.title')) ?></h2>
      <p class="lead"><?= e(tx('about.text')) ?></p>
      <?php if ($bul): ?><ul class="ab-list"><?php foreach ($bul as $b): ?><li class="glass"><?= icon('check') ?><span><?= e(L($b, 'title')) ?></span></li><?php endforeach; ?></ul><?php endif; ?>
      <a class="btn btn-glass btn-mt" href="<?= e(route('about')) ?>"><?= e(tx('about.more')) ?><?= icon('arrow') ?></a>
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

<section class="sec" id="xidmetler">
  <div class="wrap">
    <div class="sec-head rv"><div><p class="chip"><span class="dot"></span><?= e(tx('nav.services')) ?></p><h2 class="h2"><?= e(tx('services.title')) ?></h2></div><p class="lead"><?= e(tx('services.text')) ?></p></div>
    <?php include __DIR__ . '/partials/service_cards.php'; ?>
  </div>
</section>

<?php if (partners()): ?>
<section class="sec partners" id="partnyorlar">
  <div class="wrap"><div class="center rv"><p class="chip"><span class="dot"></span><?= e(tx('nav.partners')) ?></p><h2 class="h2"><?= e(tx('partners.title')) ?></h2></div></div>
  <?php include __DIR__ . '/partials/partners_marquee.php'; ?>
  <div class="wrap center"><a class="btn btn-glass btn-mt" href="<?= e(route('partners')) ?>"><?= e(tx('partners.more')) ?><?= icon('arrow') ?></a></div>
</section>
<?php endif; ?>

<section class="sec" id="elaqe">
  <div class="wrap">
    <div class="contact glass" id="form">
      <div class="contact-l">
        <p class="chip"><span class="dot"></span><?= e(tx('nav.contact')) ?></p>
        <h2 class="h2"><?= e(tx('contact.title')) ?></h2><p class="lead"><?= e(tx('contact.text')) ?></p>
        <?php include __DIR__ . '/partials/contact_list.php'; ?>
        <?php include __DIR__ . '/partials/socials.php'; ?>
      </div>
      <div class="contact-r"><?php include __DIR__ . '/partials/form.php'; ?></div>
    </div>
  </div>
</section>
