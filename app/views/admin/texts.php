<?php
/** @var array $groups @var string $g @var array $items @var string $lng */
?>
<div class="note"><?= icon('spark') ?><span><?= e(__('hint_texts')) ?></span></div>
<div class="tabs tabs-wrap">
  <?php foreach ($groups as $k => $lab): ?><a href="<?= e(admin_url('texts', ['g' => $k])) ?>"<?= $g === $k ? ' class="on"' : '' ?>><?= e($lab) ?></a><?php endforeach; ?>
</div>
<form class="panel form" method="post" action="<?= e(admin_url('texts', ['g' => $g])) ?>" data-dirty>
  <?= csrf_field() ?>
  <div class="panel-h"><h3><?= e($groups[$g]) ?></h3><span class="lp"><?= strtoupper($lng) ?></span></div>
  <?php foreach ($items as $t):
    $v = (string)$t[$lng];
    $ref = $lng !== 'az' ? (string)$t['az'] : '';
    $long = mb_strlen($v) > 70 || mb_strlen($ref) > 70; ?>
    <div class="fld fld-full txt-row">
      <label for="t_<?= e($t['k']) ?>"><code><?= e($t['k']) ?></code></label>
      <?php if ($long): ?>
        <textarea id="t_<?= e($t['k']) ?>" name="t[<?= e($t['k']) ?>]" rows="3"><?= e($v) ?></textarea>
      <?php else: ?>
        <input id="t_<?= e($t['k']) ?>" type="text" name="t[<?= e($t['k']) ?>]" value="<?= e($v) ?>">
      <?php endif; ?>
      <?php if ($ref !== ''): ?><small class="muted">AZ: <?= e($ref) ?></small><?php endif; ?>
    </div>
  <?php endforeach; ?>
  <div class="form-foot"><button class="btn" type="submit"><?= icon('check') ?><?= e(__('save')) ?></button></div>
</form>
