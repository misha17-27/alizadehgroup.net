<?php
/** @var array $r @var array $statuses */
$details = jlist($r['details']);
$files = jlist($r['files']);
$wa = preg_replace('~\D+~', '', $r['phone']);
?>
<p class="back"><a href="<?= e(admin_url('requests')) ?>">← <?= e(__('nav_requests')) ?></a></p>
<div class="grid2 grid2-wide">
  <div>
    <div class="panel">
      <div class="panel-h"><h3><?= e($r['service_title']) ?></h3><span class="st st-<?= e($r['status']) ?>"><?= e($statuses[$r['status']] ?? $r['status']) ?></span></div>
      <dl class="dl">
        <dt><?= e(__('c_name')) ?></dt><dd><b><?= e($r['name']) ?></b></dd>
        <?php if ($r['company']): ?><dt><?= e(__('c_company')) ?></dt><dd><?= e($r['company']) ?></dd><?php endif; ?>
        <dt><?= e(__('c_phone')) ?></dt><dd><a href="<?= e(tel_href($r['phone'])) ?>"><?= e($r['phone']) ?></a></dd>
        <dt><?= e(__('c_email')) ?></dt><dd><a href="mailto:<?= e($r['email']) ?>?subject=<?= rawurlencode('Alizadeh Group — ' . $r['service_title']) ?>"><?= e($r['email']) ?></a></dd>
        <dt><?= e(__('date')) ?></dt><dd><?= e(fmt_date($r['created_at'])) ?></dd>
      </dl>
      <div class="actions-row">
        <a class="btn sm" href="<?= e(tel_href($r['phone'])) ?>"><?= icon('phone') ?><?= e(__('call')) ?></a>
        <?php if ($wa): ?><a class="btn sm ghost" href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener"><?= icon('wa') ?>WhatsApp</a><?php endif; ?>
        <a class="btn sm ghost" href="mailto:<?= e($r['email']) ?>?subject=<?= rawurlencode('Alizadeh Group — ' . $r['service_title']) ?>"><?= icon('mail') ?><?= e(__('reply')) ?></a>
      </div>
    </div>

    <?php if ($details): ?>
    <div class="panel">
      <h3><?= e(__('details')) ?></h3>
      <dl class="dl"><?php foreach ($details as $d): ?><dt><?= e(tx($d[0], 'az')) ?></dt><dd><?= e($d[1]) ?></dd><?php endforeach; ?></dl>
    </div>
    <?php endif; ?>

    <?php if ($r['message'] !== ''): ?>
    <div class="panel"><h3><?= e(__('c_message')) ?></h3><div class="msg"><?= nl2br_e($r['message']) ?></div></div>
    <?php endif; ?>

    <?php if ($files): ?>
    <div class="panel">
      <h3><?= e(__('files')) ?> (<?= count($files) ?>)</h3>
      <ul class="files"><?php foreach ($files as $i => $f): ?>
        <li><a href="<?= e(admin_url('requests', ['id' => $r['id'], 'file' => $i])) ?>"><?= icon('upload') ?><span><b><?= e($f['name']) ?></b><small><?= e($f['product'] ?? '') ?> · <?= e(fmt_size((int)$f['size'])) ?></small></span></a></li>
      <?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
  </div>

  <div>
    <form class="panel" method="post">
      <?= csrf_field() ?><input type="hidden" name="do" value="update"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
      <h3><?= e(__('processing')) ?></h3>
      <label><?= e(__('status')) ?></label>
      <?= select_html('status', $statuses, $r['status']) ?>
      <label><?= e(__('note')) ?></label>
      <textarea name="note" rows="5" placeholder="<?= e(__('note_ph')) ?>"><?= e($r['note']) ?></textarea>
      <div class="form-foot"><button class="btn" type="submit"><?= icon('check') ?><?= e(__('save')) ?></button></div>
    </form>
    <div class="panel meta">
      <h3><?= e(__('tech_info')) ?></h3>
      <dl class="dl dl-sm">
        <dt><?= e(__('site_lang')) ?></dt><dd><?= e(strtoupper($r['lang'])) ?></dd>
        <dt>IP</dt><dd><?= e($r['ip']) ?></dd>
        <dt><?= e(__('updated')) ?></dt><dd><?= e(fmt_date($r['updated_at'])) ?></dd>
        <dt>User-Agent</dt><dd class="muted small"><?= e($r['ua']) ?></dd>
      </dl>
    </div>
    <form method="post" class="del-form" data-confirm="<?= e(__('confirm_delete')) ?>">
      <?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
      <button class="btn red sm" type="submit"><?= icon('x') ?><?= e(__('delete')) ?></button>
    </form>
  </div>
</div>
