<?php
/** @var array $stats @var array $latest @var array $byService @var ?array $server */
$st = status_labels();
$max = max(1, ...array_map(function ($r) { return (int)$r['n']; }, $byService ?: [['n' => 1]]));
?>
<div class="cards">
  <a class="card card-accent" href="<?= e(admin_url('requests', ['status' => 'new'])) ?>"><div class="n"><?= $stats['new'] ?></div><div class="l"><?= e(__('stat_new')) ?></div></a>
  <a class="card" href="<?= e(admin_url('requests')) ?>"><div class="n"><?= $stats['month'] ?></div><div class="l"><?= e(__('stat_month')) ?></div></a>
  <a class="card" href="<?= e(admin_url('requests')) ?>"><div class="n"><?= $stats['total'] ?></div><div class="l"><?= e(__('stat_total')) ?></div></a>
  <a class="card" href="<?= e(admin_url('services')) ?>"><div class="n"><?= $stats['services'] ?></div><div class="l"><?= e(__('nav_services')) ?></div></a>
  <a class="card" href="<?= e(admin_url('partners')) ?>"><div class="n"><?= $stats['partners'] ?></div><div class="l"><?= e(__('nav_partners')) ?></div></a>
</div>

<div class="grid2">
  <div class="panel">
    <div class="panel-h"><h3><?= e(__('latest_requests')) ?></h3><a class="btn ghost sm" href="<?= e(admin_url('requests')) ?>"><?= e(__('all')) ?> →</a></div>
    <?php if (!$latest): ?><p class="empty"><?= e(__('no_requests')) ?></p><?php else: ?>
    <div class="tablewrap"><table class="tbl">
      <thead><tr><th>#</th><th><?= e(__('date')) ?></th><th><?= e(__('f_service')) ?></th><th><?= e(__('c_name')) ?></th><th><?= e(__('status')) ?></th></tr></thead>
      <tbody><?php foreach ($latest as $r): ?>
        <tr class="<?= $r['status'] === 'new' ? 'is-new' : '' ?>">
          <td><a class="rowlink" href="<?= e(admin_url('requests', ['id' => $r['id']])) ?>"><?= (int)$r['id'] ?></a></td>
          <td class="nowrap muted"><?= e(fmt_date($r['created_at'])) ?></td>
          <td><?= e($r['service_title']) ?></td>
          <td><a href="<?= e(admin_url('requests', ['id' => $r['id']])) ?>"><b><?= e($r['name']) ?></b></a><?= $r['company'] ? '<br><span class="muted">' . e($r['company']) . '</span>' : '' ?></td>
          <td><span class="st st-<?= e($r['status']) ?>"><?= e($st[$r['status']] ?? $r['status']) ?></span></td>
        </tr>
      <?php endforeach; ?></tbody>
    </table></div>
    <?php endif; ?>
  </div>
  <div>
    <div class="panel">
      <h3><?= e(__('by_service')) ?></h3>
      <?php if (!$byService): ?><p class="empty"><?= e(__('no_requests')) ?></p><?php endif; ?>
      <?php foreach ($byService as $b): ?>
        <div class="bar-row"><span><?= e($b['service_title'] ?: '—') ?></span><b><?= (int)$b['n'] ?></b><i style="width:<?= round((int)$b['n'] / $max * 100) ?>%"></i></div>
      <?php endforeach; ?>
    </div>
    <div class="panel">
      <h3><?= e(__('quick_links')) ?></h3>
      <div class="quick">
        <a href="<?= e(admin_url('slides')) ?>"><?= icon('layers') ?><?= e(__('nav_slides')) ?></a>
        <a href="<?= e(admin_url('services')) ?>"><?= icon('briefcase') ?><?= e(__('nav_services')) ?></a>
        <a href="<?= e(admin_url('partners', ['action' => 'new'])) ?>"><?= icon('plus') ?><?= e(__('add_partner')) ?></a>
        <a href="<?= e(admin_url('texts')) ?>"><?= icon('sliders') ?><?= e(__('nav_texts')) ?></a>
        <?php if (is_admin()): ?><a href="<?= e(admin_url('settings')) ?>"><?= icon('gear') ?><?= e(__('nav_settings')) ?></a><?php endif; ?>
        <a href="<?= e(url('')) ?>" target="_blank" rel="noopener"><?= icon('arrow-ur') ?><?= e(__('view_site')) ?></a>
      </div>
    </div>
    <?php if ($server): ?>
    <div class="panel">
      <h3><?= e(__('server')) ?></h3>
      <dl class="dl dl-sm dl-srv">
        <dt>PHP</dt><dd><?= e($server['php']) ?></dd>
        <dt><?= e(__('srv_db')) ?></dt><dd><?= e($server['db']) ?></dd>
        <dt><?= e(__('srv_upload')) ?></dt><dd><?= e($server['upload']) ?></dd>
      </dl>
      <?php foreach ($server['warn'] as $w): ?><p class="srv-warn"><?= e($w) ?></p><?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
