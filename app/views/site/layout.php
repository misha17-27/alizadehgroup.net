<?php
/** @var string $content @var array $meta @var array $alt @var string $nav */
$l = lang();
$svcs = services_list();
$phone = setting('phone');
$email = setting('email');
$wa = whatsapp_url();
$title = $meta['title'] ?? 'Alizadeh Group';
$desc = $meta['desc'] ?? '';
$ogImage = !empty($meta['image']) ? site_origin() . media($meta['image']) : '';
$canonical = site_origin() . ($alt[$l] ?? url(''));
$ga = setting('ga_id');
$active = function (string $k) use ($nav) { return $nav === $k ? ' class="is-current" aria-current="page"' : ''; };
?><!doctype html>
<html lang="<?= e($l) ?>">
<head>
<meta charset="utf-8">
<?= csp_meta() ?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<?php if ($desc !== ''): ?><meta name="description" content="<?= e(mb_substr($desc, 0, 300)) ?>"><?php endif; ?>
<?php if (!empty($noindex)): ?><meta name="robots" content="noindex"><?php endif; ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<?php foreach ($alt as $al => $au): ?><link rel="alternate" hreflang="<?= e($al) ?>" href="<?= e(site_origin() . $au) ?>">
<?php endforeach; ?>
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($title) ?>">
<?php if ($desc !== ''): ?><meta property="og:description" content="<?= e(mb_substr($desc, 0, 300)) ?>"><?php endif; ?>
<?php if ($ogImage): ?><meta property="og:image" content="<?= e($ogImage) ?>"><?php endif; ?>
<meta property="og:locale" content="<?= $l === 'az' ? 'az_AZ' : 'en_US' ?>">
<meta name="theme-color" content="#07090a">
<link rel="icon" type="image/png" href="<?= e(asset('assets/img/favicon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/site.css')) ?>">
<script>document.documentElement.classList.add("js")</script>
<script src="<?= e(asset('assets/js/site.js')) ?>" defer></script>
<?php if ($ga): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag("js",new Date());gtag("config",<?= json_encode($ga) ?>);</script>
<?php endif; ?>
</head>
<body class="t5">
<?= icon_sprite() ?>
<a class="skip" href="#main"><?= e(tx('ui.skip')) ?></a>

<header class="hd" data-header>
  <div class="wrap"><div class="hd-bar">
    <a class="logo" href="<?= e(route('home')) ?>" aria-label="Alizadeh Group"><img src="<?= e(asset('assets/img/logo-white.svg')) ?>" alt="Alizadeh Group" width="130" height="30"></a>
    <nav class="nav" aria-label="Main">
      <a href="<?= e(route('about')) ?>"<?= $active('about') ?>><?= e(tx('nav.about')) ?></a>
      <div class="dd" data-dd>
        <a class="dd-btn<?= $nav === 'services' ? ' is-current' : '' ?>" href="<?= e(route('services')) ?>" data-dd-btn aria-expanded="false"><?= e(tx('nav.services')) ?><?= icon('chev') ?></a>
        <div class="dd-menu">
          <?php foreach ($svcs as $i => $s): ?>
          <a href="<?= e(route('service', ['service' => $s])) ?>"><span class="dd-ic"><?= icon($s['icon']) ?></span><span><b><?= e(L($s, 'title')) ?></b><small><?= sprintf('%02d', $i + 1) ?></small></span></a>
          <?php endforeach; ?>
        </div>
      </div>
      <a href="<?= e(route('partners')) ?>"<?= $active('partners') ?>><?= e(tx('nav.partners')) ?></a>
      <a href="<?= e(route('contact')) ?>"<?= $active('contact') ?>><?= e(tx('nav.contact')) ?></a>
    </nav>
    <div class="hd-act">
      <div class="lang" role="group" aria-label="<?= e(tx('nav.lang')) ?>">
        <?php foreach (LANGS as $al): ?><a href="<?= e($alt[$al] ?? route('home', [], $al)) ?>" hreflang="<?= $al ?>" lang="<?= $al ?>"<?= $al === $l ? ' class="is-active" aria-current="true"' : '' ?>><?= strtoupper($al) ?></a><?php endforeach; ?>
      </div>
      <a class="btn btn-lime btn-sm hd-cta" href="<?= e(route('contact')) ?>#form"><?= e(tx('nav.request')) ?></a>
      <button class="burger" type="button" data-burger aria-expanded="false" aria-controls="mnav" aria-label="<?= e(tx('nav.open')) ?>" data-label-open="<?= e(tx('nav.open')) ?>" data-label-close="<?= e(tx('nav.close')) ?>"><span></span><span></span></button>
    </div>
  </div></div>
</header>

<nav class="mnav" id="mnav" data-mnav aria-label="Mobile">
  <a class="ml" href="<?= e(route('home')) ?>"><?= e(tx('nav.home')) ?></a>
  <a class="ml" href="<?= e(route('about')) ?>"><?= e(tx('nav.about')) ?></a>
  <a class="ml" href="<?= e(route('services')) ?>"><?= e(tx('nav.services')) ?></a>
  <div class="msvc">
    <?php foreach ($svcs as $s): ?><a href="<?= e(route('service', ['service' => $s])) ?>"><?= icon($s['icon']) ?><?= e(L($s, 'title')) ?></a><?php endforeach; ?>
  </div>
  <a class="ml" href="<?= e(route('partners')) ?>"><?= e(tx('nav.partners')) ?></a>
  <a class="ml" href="<?= e(route('contact')) ?>"><?= e(tx('nav.contact')) ?></a>
  <div class="mf">
    <?php if ($phone): ?><a href="<?= e(tel_href($phone)) ?>"><?= e($phone) ?></a><?php endif; ?>
    <?php if ($email): ?><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><?php endif; ?>
    <a class="btn btn-lime" href="<?= e(route('contact')) ?>#form"><?= e(tx('nav.request')) ?></a>
  </div>
</nav>

<main id="main">
<?= $content ?>
</main>

<footer class="ft">
  <div class="wrap">
    <div class="ft-top">
      <div class="ft-brand">
        <img src="<?= e(asset('assets/img/logo-white.svg')) ?>" alt="Alizadeh Group" width="150" height="35" loading="lazy" class="ft-logo">
        <p><?= e(tx('footer.text')) ?></p>
        <?php include __DIR__ . '/partials/socials.php'; ?>
      </div>
      <div><h4><?= e(tx('footer.menu')) ?></h4><ul>
        <li><a href="<?= e(route('about')) ?>"><?= e(tx('nav.about')) ?></a></li>
        <li><a href="<?= e(route('services')) ?>"><?= e(tx('nav.services')) ?></a></li>
        <li><a href="<?= e(route('partners')) ?>"><?= e(tx('nav.partners')) ?></a></li>
        <li><a href="<?= e(route('contact')) ?>"><?= e(tx('nav.contact')) ?></a></li></ul></div>
      <div><h4><?= e(tx('nav.services')) ?></h4><ul>
        <?php foreach ($svcs as $s): ?><li><a href="<?= e(route('service', ['service' => $s])) ?>"><?= e(L($s, 'title')) ?></a></li><?php endforeach; ?></ul></div>
      <div><h4><?= e(tx('nav.contact')) ?></h4><ul>
        <?php if ($phone): ?><li><a href="<?= e(tel_href($phone)) ?>"><?= e($phone) ?></a></li><?php endif; ?>
        <?php if ($email): ?><li><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li><?php endif; ?>
        <li><?= e(setting('address_' . $l, setting('address_az'))) ?></li></ul></div>
    </div>
    <div class="ft-mark" aria-hidden="true"><img src="<?= e(asset('assets/img/logo-white.svg')) ?>" alt="" width="1206" height="279" loading="lazy"></div>
    <div class="ft-bot"><span>© <?= date('Y') ?> Alizadeh Group. <?= e(tx('footer.rights')) ?></span><a href="#main"><?= e(tx('ui.top')) ?></a></div>
  </div>
</footer>
<?php if ($wa): ?><a class="wa" href="<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><?= icon('wa') ?></a><?php endif; ?>
</body>
</html>
