<div class="sc-grid">
<?php foreach (services_list() as $i => $s): ?>
  <a class="sc rv" href="<?= e(route('service', ['service' => $s])) ?>" style="--d:<?= ($i % 2) * 80 ?>ms">
    <?= picture($s['image'], '', 'alt="" loading="lazy"', '(max-width: 960px) 100vw, 50vw') ?>
    <span class="sc-in"><span class="sc-top"><span class="sc-ic"><?= icon($s['icon']) ?></span><span class="sc-no"><?= sprintf('%02d', $i + 1) ?></span></span>
      <h3><?= e(L($s, 'title')) ?></h3><p><?= e(L($s, 'short')) ?></p>
      <span class="sc-go"><?= e(tx('ui.more')) ?><?= icon('arrow') ?></span></span>
  </a>
<?php endforeach; ?>
</div>
