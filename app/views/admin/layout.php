<?php
/** @var string $body @var string $title @var bool $cl */
$me = current_user();
$section = (string)($_GET['section'] ?? 'dashboard');
$nav = admin_nav();
$cur = content_lang();
$langUrl = function (string $l): string { $q = $_GET; $q['cl'] = $l; return url('admin/index.php') . '?' . http_build_query($q); };
$uiUrl = function (string $l): string { $q = $_GET; $q['ui'] = $l; return url('admin/index.php') . '?' . http_build_query($q); };
$roles = ['admin' => __('role_admin'), 'editor' => __('role_editor')];
?><!doctype html>
<html lang="<?= e(admin_ui_lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e(strip_tags($title)) ?> — Alizadeh Group admin</title>
<link rel="icon" type="image/png" href="<?= e(asset('assets/img/favicon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
<script src="<?= e(asset('assets/js/admin.js')) ?>" defer></script>
</head>
<body>
<?= icon_sprite() ?>
<input type="checkbox" id="navtoggle" hidden>
<div class="wrap">
  <aside class="side">
    <a class="brand" href="<?= e(admin_url()) ?>"><img src="<?= e(asset('assets/img/logo-white.svg')) ?>" alt="Alizadeh Group"><span>admin</span></a>
    <nav>
      <?php foreach ($nav as $grp => $links): ?>
        <div class="navgroup"><?= e(__($grp)) ?></div>
        <?php foreach ($links as $key => $l): ?>
          <a href="<?= e(admin_url($key)) ?>"<?= $section === $key ? ' class="on" aria-current="page"' : '' ?>><?= icon($l[0]) ?><span><?= e($l[1]) ?></span><?php if (!empty($l[2])): ?><em class="badge"><?= (int)$l[2] ?></em><?php endif; ?></a>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </nav>
    <div class="who">
      <b><?= e($me['name'] ?: $me['email']) ?></b><br><span class="who-mail"><?= e($me['email']) ?></span><br>
      <span class="role"><?= e($roles[$me['role']] ?? $me['role']) ?></span>
      <div class="who-act">
        <span class="uisw"><?php foreach (ADMIN_UI_LANGS as $k => $lab): ?><a href="<?= e($uiUrl($k)) ?>"<?= admin_ui_lang() === $k ? ' class="on"' : '' ?>><?= e($lab) ?></a><?php endforeach; ?></span>
        <a class="logout" href="<?= e(admin_url('logout', ['t' => csrf_token()])) ?>"><?= icon('arrow-l') ?><?= e(__('logout')) ?></a>
      </div>
    </div>
  </aside>
  <label class="scrim" for="navtoggle" aria-hidden="true"></label>
  <main>
    <div class="bar">
      <label class="burger" for="navtoggle" aria-label="Menu"><span></span><span></span><span></span></label>
      <h1><?= e($title) ?></h1>
      <div class="bar-r">
        <?php if (!empty($cl)): ?>
        <div class="langsw" title="<?= e(__('content_lang')) ?>"><span class="langsw-l"><?= e(__('content_lang')) ?>:</span><?php foreach (LANGS as $l): ?><a href="<?= e($langUrl($l)) ?>"<?= $cur === $l ? ' class="on"' : '' ?>><?= strtoupper($l) ?></a><?php endforeach; ?></div>
        <?php endif; ?>
        <a class="btn ghost sm hide-sm" href="<?= e(url('')) ?>" target="_blank" rel="noopener"><?= icon('arrow-ur') ?><?= e(__('view_site')) ?></a>
      </div>
    </div>
    <div class="body">
      <?php foreach (flashes() as [$ft, $fm]): ?><div class="flash flash-<?= e($ft) ?>" data-flash><?= icon($ft === 'ok' ? 'check' : 'x') ?><?= e($fm) ?></div><?php endforeach; ?>
      <?= $body ?>
    </div>
  </main>
</div>
</body>
</html>
