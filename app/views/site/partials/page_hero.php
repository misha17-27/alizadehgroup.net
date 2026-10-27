<?php
/** @var string $ph_title @var string $ph_text @var string $ph_image @var array $ph_crumbs  [ [label, url|null], ... ] */
$ph_chip = $ph_chip ?? '';
?>
<section class="ph">
  <img class="ph-bg" src="<?= e(media($ph_image, 'assets/img/baku-night.jpg')) ?>" alt="" fetchpriority="high">
  <div class="hs-grid" aria-hidden="true"></div>
  <div class="wrap ph-in">
    <nav class="crumbs" aria-label="Breadcrumb">
      <a href="<?= e(route('home')) ?>"><?= e(tx('nav.home')) ?></a>
      <?php foreach ($ph_crumbs as [$cl, $cu]): ?><?= icon('chev', 'ico cr') ?><?php if ($cu): ?><a href="<?= e($cu) ?>"><?= e($cl) ?></a><?php else: ?><span aria-current="page"><?= e($cl) ?></span><?php endif; ?><?php endforeach; ?>
    </nav>
    <?php if ($ph_chip): ?><p class="chip"><?= $ph_chip ?></p><?php endif; ?>
    <h1><?= e($ph_title) ?></h1>
    <?php if ($ph_text !== ''): ?><p class="lead"><?= e($ph_text) ?></p><?php endif; ?>
    <?php if (!empty($ph_actions)): ?><div class="hs-cta"><?= $ph_actions ?></div><?php endif; ?>
    <?php if (!empty($ph_summary)): ?><div class="ph-sum glass"><?= $ph_summary ?></div><?php endif; ?>
  </div>
</section>
