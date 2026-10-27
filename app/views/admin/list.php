<?php
/**
 * Generic list (used by crud()).
 * @var array $rows @var array $cols @var string $pk @var callable $self
 * @var bool $can_create @var bool $can_delete @var bool $sortable @var bool $has_active @var string $hint @var ?array $hint_link
 */
$cl = content_lang();
$n = count($rows);
$titleIdx = null;
foreach ($cols as $i => $c) { if (!in_array($c['type'], ['thumb', 'icon'], true)) { $titleIdx = $i; break; } }
$small = function (string $do, $id, string $label, string $cls = 'ib', array $extra = [], string $confirm = '') {
    $h = '<form method="post" class="inl"' . ($confirm ? ' data-confirm="' . e($confirm) . '"' : '') . '>' . csrf_field()
        . '<input type="hidden" name="do" value="' . e($do) . '"><input type="hidden" name="id" value="' . e((string)$id) . '">';
    foreach ($extra as $k => $v) $h .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    return $h . '<button type="submit" class="' . $cls . '" title="' . e(strip_tags($label)) . '">' . $label . '</button></form>';
};
?>
<?php if ($hint): ?><div class="note"><?= icon('spark') ?><span><?= e($hint) ?><?php if (!empty($hint_link)): ?> <a href="<?= e($hint_link[0]) ?>"><?= e($hint_link[1]) ?> →</a><?php endif; ?></span></div><?php endif; ?>
<div class="panel">
  <div class="panel-h">
    <b><?= e(__('total')) ?>: <?= $n ?></b>
    <?php if ($can_create): ?><a class="btn" href="<?= e($self(['action' => 'new'])) ?>">+ <?= e($new_label) ?></a><?php endif; ?>
  </div>
  <?php if (!$rows): ?>
    <p class="empty"><?= e(__('empty')) ?></p>
  <?php else: ?>
  <div class="tablewrap"><table class="tbl">
    <thead><tr>
      <?php foreach ($cols as $c): ?><th<?= in_array($c['type'], ['thumb', 'icon'], true) ? ' class="w-thumb"' : '' ?>><?= e($c['label'] ?? '') ?></th><?php endforeach; ?>
      <?php if ($has_active): ?><th class="w-st"><?= e(__('f_active')) ?></th><?php endif; ?>
      <th class="right"><?= e(__('actions')) ?></th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $ri => $r):
      $id = $r[$pk];
      $edit = $self(['id' => $id]); ?>
      <tr class="<?= $has_active && empty($r['active']) ? 'off' : '' ?>">
        <?php foreach ($cols as $i => $c):
          $k = $c['key'];
          switch ($c['type']) {
              case 'thumb':
                  $v = $r[$k] !== '' ? '<img class="thumb' . (!empty($c['wide']) ? ' thumb-w' : '') . '" src="' . e(media($r[$k])) . '" alt="" loading="lazy">' : '<span class="thumb thumb-empty"></span>';
                  break;
              case 'icon':
                  $v = '<span class="iconcell">' . icon((string)$r[$k]) . '</span>';
                  break;
              case 'i18n':
                  $t = L($r, $k, $cl);
                  $own = (string)($r[$k . '_' . $cl] ?? '');
                  $v = e(mb_strimwidth($t, 0, 120, '…')) . ($own === '' && $t !== '' ? ' <span class="lp lp-miss">' . strtoupper($cl) . ' —</span>' : '');
                  break;
              case 'fn':
                  $v = ($c['fn'])($r);
                  break;
              default:
                  $v = e((string)$r[$k]);
          }
          if ($i === $titleIdx && $c['type'] !== 'fn') $v = '<a class="rowlink" href="' . e($edit) . '">' . ($v !== '' ? $v : '—') . '</a>';
          elseif ($i === $titleIdx) $v = '<a class="rowlink" href="' . e($edit) . '">' . $v . '</a>';
        ?>
          <td<?= in_array($c['type'], ['thumb', 'icon'], true) ? ' class="w-thumb"' : '' ?>><?= $v ?></td>
        <?php endforeach; ?>
        <?php if ($has_active): ?>
          <td class="w-st"><?= $small('toggle', $id, '<span class="sw' . (!empty($r['active']) ? ' on' : '') . '"></span>', 'swbtn') ?></td>
        <?php endif; ?>
        <td class="right nowrap">
          <?php if ($sortable): ?>
            <?= $ri > 0 ? $small('move', $id, '↑', 'ib', ['dir' => 'up']) : '<span class="ib ib-ghost">↑</span>' ?>
            <?= $ri < $n - 1 ? $small('move', $id, '↓', 'ib', ['dir' => 'down']) : '<span class="ib ib-ghost">↓</span>' ?>
          <?php endif; ?>
          <a class="ib" href="<?= e($edit) ?>" title="<?= e(__('edit')) ?>"><?= icon('sliders') ?></a>
          <?php if ($can_delete): ?><?= $small('delete', $id, icon('x'), 'ib ib-del', [], __('confirm_delete')) ?><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>
