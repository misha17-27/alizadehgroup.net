<?php
/**
 * Generic edit form (crud, settings, profile).
 * @var array $fields @var array $row @var array $errors @var string $id @var string $action_url @var string $back_url
 * @var bool $can_delete @var string $hint @var string $preview_url
 */
$cl = content_lang();
?>
<?php if ($hint): ?><div class="note"><?= icon('spark') ?><span><?= e($hint) ?></span></div><?php endif; ?>
<form class="panel form" method="post" action="<?= e($action_url) ?>" enctype="multipart/form-data" data-dirty>
  <?= csrf_field() ?>
  <input type="hidden" name="do" value="save">
  <input type="hidden" name="id" value="<?= e($id) ?>">
  <?= render_fields($fields, $row, $cl, $errors) ?>
  <div class="form-foot">
    <button class="btn" type="submit" name="stay" value="1"><?= icon('check') ?><?= e(__('save')) ?></button>
    <?php if ($back_url): ?>
      <button class="btn ghost" type="submit"><?= e(__('save_close')) ?></button>
      <a class="btn ghost" href="<?= e($back_url) ?>"><?= e(__('cancel')) ?></a>
    <?php endif; ?>
    <?php if ($preview_url): ?><a class="btn ghost" href="<?= e($preview_url) ?>" target="_blank" rel="noopener"><?= icon('arrow-ur') ?><?= e(__('view_on_site')) ?></a><?php endif; ?>
  </div>
</form>
<?php if ($can_delete): ?>
<form method="post" action="<?= e($back_url ?: $action_url) ?>" class="del-form" data-confirm="<?= e(__('confirm_delete')) ?>">
  <?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= e($id) ?>">
  <button class="btn red sm" type="submit"><?= icon('x') ?><?= e(__('delete')) ?></button>
</form>
<?php endif; ?>
