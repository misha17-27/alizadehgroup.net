<?php
/** @var array $list @var array $statuses @var array $counts @var int $total @var int $p @var int $pages @var array $filters */
$q = function (array $over = []) use ($filters) { return admin_url('requests', array_filter(array_merge($filters, $over), function ($v) { return $v !== '' && $v !== null; })); };
$all = array_sum($counts);
?>
<div class="tabs">
  <a href="<?= e($q(['status' => '', 'p' => ''])) ?>"<?= $filters['status'] === '' ? ' class="on"' : '' ?>><?= e(__('all')) ?> <em><?= $all ?></em></a>
  <?php foreach ($statuses as $k => $lab): ?>
    <a href="<?= e($q(['status' => $k, 'p' => ''])) ?>"<?= $filters['status'] === $k ? ' class="on"' : '' ?>><?= e($lab) ?> <em><?= (int)($counts[$k] ?? 0) ?></em></a>
  <?php endforeach; ?>
</div>

<form class="panel filters" method="get" action="<?= e(url('admin/index.php')) ?>">
  <input type="hidden" name="section" value="requests">
  <?php if ($filters['status'] !== ''): ?><input type="hidden" name="status" value="<?= e($filters['status']) ?>"><?php endif; ?>
  <div class="fl-f"><label><?= e(__('f_service')) ?></label><?= select_html('service', ['' => __('all')] + service_options(false) + ['diger' => tx('f.other', 'az')], $filters['service']) ?></div>
  <div class="fl-f fl-grow"><label><?= e(__('search')) ?></label><input type="text" name="q" value="<?= e($filters['q']) ?>" placeholder="<?= e(__('search_ph')) ?>"></div>
  <div class="fl-b">
    <button class="btn" type="submit"><?= e(__('filter')) ?></button>
    <a class="btn ghost" href="<?= e($q(['export' => 'csv', 'p' => ''])) ?>"><?= icon('upload') ?>CSV</a>
  </div>
</form>

<form method="post" class="panel" data-bulk>
  <?= csrf_field() ?><input type="hidden" name="do" value="bulk">
  <div class="panel-h">
    <b><?= e(__('total')) ?>: <?= $total ?></b>
    <div class="bulk">
      <?= select_html('bulk_status', ['' => __('bulk_status')] + $statuses, '') ?>
      <button class="btn sm navy" type="submit"><?= e(__('apply')) ?></button>
      <button class="btn sm red" type="submit" name="bulk_delete" value="1" data-confirm="<?= e(__('confirm_delete')) ?>"><?= icon('x') ?><?= e(__('delete_selected')) ?></button>
    </div>
  </div>
  <?php if (!$list): ?><p class="empty"><?= e(__('no_requests')) ?></p><?php else: ?>
  <div class="tablewrap"><table class="tbl tbl-req">
    <thead><tr><th class="w-cb"><input type="checkbox" data-check-all aria-label="all"></th><th>#</th><th><?= e(__('date')) ?></th><th><?= e(__('f_service')) ?></th><th><?= e(__('c_name')) ?></th><th><?= e(__('contacts')) ?></th><th><?= e(__('status')) ?></th><th></th></tr></thead>
    <tbody><?php foreach ($list as $r): $u = admin_url('requests', ['id' => $r['id']]); ?>
      <tr class="<?= $r['status'] === 'new' ? 'is-new' : '' ?>">
        <td class="w-cb"><input type="checkbox" name="ids[]" value="<?= (int)$r['id'] ?>" aria-label="#<?= (int)$r['id'] ?>"></td>
        <td><a class="rowlink" href="<?= e($u) ?>"><?= (int)$r['id'] ?></a></td>
        <td class="nowrap muted"><?= e(fmt_date($r['created_at'])) ?></td>
        <td><?= e($r['service_title']) ?><?= jlist($r['files']) ? ' <span title="files">📎</span>' : '' ?></td>
        <td><a href="<?= e($u) ?>"><b><?= e($r['name']) ?></b></a><?= $r['company'] ? '<br><span class="muted">' . e($r['company']) . '</span>' : '' ?></td>
        <td class="nowrap"><a href="<?= e(tel_href($r['phone'])) ?>"><?= e($r['phone']) ?></a><br><a class="muted" href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a></td>
        <td><span class="st st-<?= e($r['status']) ?>"><?= e($statuses[$r['status']] ?? $r['status']) ?></span></td>
        <td class="right"><button class="ib ib-del" type="submit" name="del" value="<?= (int)$r['id'] ?>" title="<?= e(__('delete')) ?>" aria-label="<?= e(__('delete')) ?> #<?= (int)$r['id'] ?>" data-confirm="<?= e(__('confirm_delete')) ?>"><?= icon('x') ?></button></td>
      </tr>
    <?php endforeach; ?></tbody>
  </table></div>
  <?php endif; ?>
</form>

<?php if ($pages > 1): ?>
<nav class="pager">
  <?php for ($i = 1; $i <= $pages; $i++): ?>
    <a href="<?= e($q(['p' => $i])) ?>"<?= $i === $p ? ' class="on"' : '' ?>><?= $i ?></a>
  <?php endfor; ?>
</nav>
<?php endif; ?>
