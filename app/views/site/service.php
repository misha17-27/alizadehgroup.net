<?php
/** @var array $s service row */
/* rows without text in the current language (and no AZ fallback) are skipped */
$filled = function (array $list, string $field) { return array_values(array_filter($list, function ($r) use ($field) { return jl($r, $field) !== ''; })); };
$targets = $filled(jlist($s['targets']), '');
$offers = $filled(jlist($s['offers']), '');
$steps = $filled(jlist($s['steps']), 't');
$faq = array_values(array_filter($filled(jlist($s['faq']), 'q'), function ($r) { return jl($r, 'a') !== ''; }));
$extra = $filled(jlist($s['extra_items']), 't');
$no = 1;
foreach (services_list() as $i => $x) { if ((int)$x['id'] === (int)$s['id']) { $no = $i + 1; break; } }
$contactHref = route('contact') . '?xidmet=' . rawurlencode($s['skey']) . '#form';

$ph_title = L($s, 'page_title') ?: L($s, 'title');
$ph_text = L($s, 'page_desc');
$ph_image = $s['image'];
$ph_crumbs = [[tx('nav.services'), route('services')], [L($s, 'title'), null]];
$ph_chip = icon($s['icon']) . '<span>' . sprintf('%02d', $no) . ' · </span>' . e(L($s, 'title'));
$ph_actions = '<a class="btn btn-lime" href="' . e($contactHref) . '">' . e(tx('ui.write')) . icon('arrow') . '</a>'
    . '<a class="btn btn-glass" href="' . e(route('services')) . '">' . e(tx('ui.all_services')) . '</a>';
$ph_summary = L($s, 'short') !== '' ? icon($s['icon']) . '<p>' . e(L($s, 'short')) . '</p>' : '';
include __DIR__ . '/partials/page_hero.php';

$nav = [];
if ($targets) $nav['hedef'] = tx('inner.tg_eyebrow');
if ($extra) $nav['elave'] = L($s, 'extra_title') ?: tx('nav.services');
if ($offers) $nav['xidmetler'] = tx('inner.of_title');
if ($steps) $nav['proses'] = tx('inner.pr_eyebrow');
if ($faq) $nav['faq'] = tx('inner.faq_eyebrow');
$nav['yazin'] = tx('ui.write');
?>
<div class="wrap inner">
  <aside class="side"><nav class="side-nav glass" data-spy aria-label="Sections">
    <?php foreach ($nav as $id => $label): ?><a href="#<?= $id ?>"><?= e($label) ?></a><?php endforeach; ?>
  </nav></aside>
  <div class="inner-main">
    <?php if ($targets): ?>
    <section class="blk" id="hedef">
      <p class="chip"><span class="dot"></span><?= e(tx('inner.tg_eyebrow')) ?></p>
      <h2 class="h3x"><?= e(tx('inner.tg_title')) ?></h2>
      <ul class="targets"><?php foreach ($targets as $t): ?><li class="glass"><?= icon($t['icon'] ?? '') ?><span><?= e(jl($t)) ?></span></li><?php endforeach; ?></ul>
    </section>
    <?php endif; ?>

    <?php if ($extra): ?>
    <section class="blk" id="elave">
      <?php if (L($s, 'extra_title') !== ''): ?><p class="chip"><span class="dot"></span><?= e(L($s, 'extra_title')) ?></p><?php endif; ?>
      <?php if (L($s, 'extra_q') !== ''): ?><h2 class="h3x"><?= e(L($s, 'extra_q')) ?></h2><?php else: ?><h2 class="h3x"><?= e(L($s, 'extra_title')) ?></h2><?php endif; ?>
      <?php if ($s['extra_style'] === 'cards'): ?>
        <div class="cats"><?php foreach ($extra as $x): ?><div class="cat glass"><span class="why-ic"><?= icon($x['icon'] ?? '') ?></span><h3><?= e(jl($x, 't')) ?></h3><?php if (jl($x, 'd') !== ''): ?><p><?= e(jl($x, 'd')) ?></p><?php endif; ?></div><?php endforeach; ?></div>
      <?php else: ?>
        <ul class="types"><?php foreach ($extra as $x): ?><li class="glass"><span class="dot"></span><span><?= e(jl($x, 't')) ?></span></li><?php endforeach; ?></ul>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if ($offers): ?>
    <section class="blk" id="xidmetler">
      <h2 class="h3x"><?= e(tx('inner.of_title')) ?></h2>
      <ul class="offers glass"><?php foreach ($offers as $o): ?><li><?= icon('check') ?><span><?= e(jl($o)) ?></span></li><?php endforeach; ?></ul>
    </section>
    <?php endif; ?>

    <?php if ($steps): ?>
    <section class="blk" id="proses">
      <p class="chip"><span class="dot"></span><?= e(tx('inner.pr_eyebrow')) ?></p>
      <h2 class="h3x"><?= e(tx('inner.pr_title')) ?></h2>
      <ol class="tl"><?php foreach ($steps as $i => $st): ?><li class="tl-i rv"><span class="tl-n"><?= sprintf('%02d', $i + 1) ?></span><div class="glass"><h3><?= e(jl($st, 't')) ?></h3><p><?= e(jl($st, 'd')) ?></p></div></li><?php endforeach; ?></ol>
    </section>
    <?php endif; ?>

    <?php if ($faq): ?>
    <section class="blk" id="faq">
      <p class="chip"><span class="dot"></span><?= e(tx('inner.faq_eyebrow')) ?></p>
      <h2 class="h3x"><?= e(tx('inner.faq_title')) ?></h2>
      <div class="accs"><?php foreach ($faq as $i => $q): ?>
        <details class="acc glass"<?= $i === 0 ? ' open' : '' ?>><summary><span><?= e(jl($q, 'q')) ?></span><span class="acc-ico" aria-hidden="true"><?= icon('plus') ?></span></summary><div class="acc-body"><p><?= e(jl($q, 'a')) ?></p></div></details>
      <?php endforeach; ?></div>
    </section>
    <?php endif; ?>

    <section class="blk" id="yazin">
      <?php $cta_service = $s['skey']; include __DIR__ . '/partials/cta.php'; ?>
    </section>
  </div>
</div>

<?php $others = array_values(array_filter(services_list(), function ($x) use ($s) { return (int)$x['id'] !== (int)$s['id']; })); ?>
<?php if ($others): ?>
<section class="sec sec-sm">
  <div class="wrap"><p class="chip"><span class="dot"></span><?= e(tx('ui.other_services')) ?></p>
    <div class="others"><?php foreach ($others as $o): ?>
      <a class="oth" href="<?= e(route('service', ['service' => $o])) ?>"><?= picture($o['image'], '', 'alt="" loading="lazy"', '(max-width: 960px) 50vw, 33vw') ?><span><b><?= e(L($o, 'title')) ?></b><?= icon('arrow-ur') ?></span></a>
    <?php endforeach; ?></div>
  </div>
</section>
<?php endif; ?>
