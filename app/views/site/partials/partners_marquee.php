<?php
$ps = partners();
$half = (int)ceil(count($ps) / 2);
$rowsP = [array_slice($ps, 0, $half), array_slice($ps, $half)];
$li = function (array $p, bool $dup) {
    $img = picture($p['logo'], '', 'alt="' . ($dup ? '' : e($p['name'])) . '" width="335" height="210" loading="lazy" decoding="async"');
    $inner = $p['url'] && !$dup ? '<a href="' . e($p['url']) . '" target="_blank" rel="noopener">' . $img . '</a>' : $img;
    return '<li' . ($dup ? ' aria-hidden="true"' : '') . '>' . $inner . '</li>';
};
foreach ($rowsP as $ri => $rp): if (!$rp) continue; ?>
<div class="mq<?= $ri ? ' mq-rev' : '' ?>"><ul class="mq-row"><?php foreach ($rp as $p) echo $li($p, false); foreach ($rp as $p) echo $li($p, true); ?></ul></div>
<?php endforeach;
