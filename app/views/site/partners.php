<?php
$p = page('partners');
$ps = partners();
$ph_title = tx('partners.title');
$ph_text = L($p, 'description');
$ph_image = $p['image'] ?? '';
$ph_crumbs = [[tx('nav.partners'), null]];
$ph_chip = '<span class="dot"></span>' . e(tx('nav.partners'));
include __DIR__ . '/partials/page_hero.php';
?>
<section class="sec">
  <div class="wrap">
    <ul class="pgrid">
      <?php foreach ($ps as $i => $pt):
        $img = '<img src="' . e(media($pt['logo'])) . '" alt="' . e($pt['name']) . '" width="335" height="210" loading="lazy" decoding="async">'; ?>
      <li class="rv" style="--d:<?= ($i % 6) * 40 ?>ms"><?php if ($pt['url']): ?><a href="<?= e($pt['url']) ?>" target="_blank" rel="noopener" title="<?= e($pt['name']) ?>"><?= $img ?></a><?php else: ?><?= $img ?><?php endif; ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<section class="sec sec-sm" style="padding-top:0"><div class="wrap"><?php include __DIR__ . '/partials/cta.php'; ?></div></section>
