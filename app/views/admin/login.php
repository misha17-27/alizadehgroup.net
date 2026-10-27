<?php
/** @var string $error @var string $email */
$ts = setting('turnstile_site');
?><!doctype html>
<html lang="<?= e(admin_ui_lang()) ?>">
<head>
<meta charset="utf-8">
<?= csp_meta() ?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e(__('login_title')) ?> — Alizadeh Group admin</title>
<link rel="icon" type="image/png" href="<?= e(asset('assets/img/favicon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body>
<div class="login">
  <form class="box" method="post" action="<?= e(admin_url('login')) ?>">
    <div class="login-top">
      <img src="<?= e(asset('assets/img/logo-dark.svg')) ?>" alt="Alizadeh Group">
      <span class="uisw uisw-light"><?php foreach (ADMIN_UI_LANGS as $k => $lab): ?><a href="<?= e(admin_url('login', ['ui' => $k])) ?>"<?= admin_ui_lang() === $k ? ' class="on"' : '' ?>><?= e($lab) ?></a><?php endforeach; ?></span>
    </div>
    <h2><?= e(__('login_title')) ?></h2>
    <div class="sub"><?= e(__('login_sub')) ?></div>
    <?php if ($error): ?><div class="flash flash-err"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <label for="email"><?= e(__('c_email')) ?></label>
    <input id="email" type="email" name="email" value="<?= e($email) ?>" required autofocus autocomplete="username">
    <label for="password"><?= e(__('password')) ?></label>
    <input id="password" type="password" name="password" required autocomplete="current-password">
    <?php if ($ts): ?>
      <div class="captcha"><div class="cf-turnstile" data-sitekey="<?= e($ts) ?>" data-theme="light"></div></div>
      <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>
    <button class="btn wide" type="submit"><?= e(__('login_btn')) ?></button>
    <a class="login-back" href="<?= e(url('')) ?>">← <?= e(__('view_site')) ?></a>
  </form>
</div>
</body>
</html>
