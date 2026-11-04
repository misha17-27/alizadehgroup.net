<?php
/** page numbers of a list: @var int $pg @var int $pages @var string $base address of the first page */
if ($pages < 2) return;
$pageUrl = function (int $n) use ($base) { return $base . ($n > 1 ? '?p=' . $n : ''); };
?>
<nav class="pager" aria-label="<?= e(tx('sections.pages')) ?>">
  <?php if ($pg > 1): ?><a href="<?= e($pageUrl($pg - 1)) ?>" rel="prev" aria-label="<?= e(tx('sections.prev')) ?>"><?= icon('arrow-l') ?></a><?php endif; ?>
  <?php for ($n = 1; $n <= $pages; $n++): ?><a href="<?= e($pageUrl($n)) ?>"<?= $n === $pg ? ' class="is-on" aria-current="page"' : '' ?>><?= $n ?></a><?php endfor; ?>
  <?php if ($pg < $pages): ?><a href="<?= e($pageUrl($pg + 1)) ?>" rel="next" aria-label="<?= e(tx('sections.next')) ?>"><?= icon('arrow') ?></a><?php endif; ?>
</nav>
