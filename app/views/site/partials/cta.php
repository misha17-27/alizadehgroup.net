<?php /** @var string $cta_service optional service key to preselect */
$cta_href = route('contact') . (!empty($cta_service) ? '?xidmet=' . rawurlencode($cta_service) : '') . '#form'; ?>
<div class="cta glass">
  <div><h2 class="h3x"><?= e(tx('ui.write')) ?></h2><p><?= e(tx('ui.write_text')) ?></p></div>
  <div class="cta-btns">
    <a class="btn btn-lime" href="<?= e($cta_href) ?>"><?= e(tx('nav.request')) ?><?= icon('arrow') ?></a>
    <?php if (setting('phone')): ?><a class="btn btn-glass" href="<?= e(tel_href(setting('phone'))) ?>"><?= icon('phone') ?><?= e(setting('phone')) ?></a><?php endif; ?>
  </div>
</div>
