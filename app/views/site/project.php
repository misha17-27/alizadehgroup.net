<?php
/** one project: text, photo gallery, YouTube videos: @var array $r */
$p = page('projects');
$body = L($r, 'body');
$photos = array_values(array_filter(array_map('strval', jlist($r['gallery']))));
$videos = array_values(array_filter(array_map(function ($v) { return youtube_id((string)($v['url'] ?? '')); }, jlist($r['videos']))));
$ph_title = L($r, 'title');
$ph_text = L($r, 'short');
$ph_image = $r['image'] ?: ($p['image'] ?? '');
$ph_crumbs = [[tx('projects.nav'), route('projects')], [L($r, 'title'), null]];
$ph_chip = '<span class="dot"></span>' . e(tx('projects.nav'));
include __DIR__ . '/partials/page_hero.php';
?>
<?php if ($body !== ''): ?>
<section class="sec sec-sm"><div class="wrap"><div class="prose"><?= $body ?></div></div></section>
<?php endif; ?>
<?php if ($photos): ?>
<section class="sec sec-sm" id="photos">
  <div class="wrap">
    <h2 class="h3x"><?= e(tx('projects.photos')) ?></h2>
    <div class="gal" data-gal>
      <?php foreach ($photos as $ph): ?><a href="<?= e(media($ph)) ?>"><?= picture($ph, '', 'alt="" loading="lazy"', '(max-width: 640px) 50vw, (max-width: 960px) 33vw, 25vw') ?></a><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php if ($videos): ?>
<section class="sec sec-sm" id="videos">
  <div class="wrap">
    <h2 class="h3x"><?= e(tx('projects.videos')) ?></h2>
    <div class="vids">
      <?php foreach ($videos as $id): ?>
      <!-- the YouTube player loads after a click (site.js); until then only the preview picture -->
      <div class="vid glass"><a class="yt" href="https://www.youtube.com/watch?v=<?= e($id) ?>" target="_blank" rel="noopener" data-yt="<?= e($id) ?>" aria-label="<?= e(tx('projects.play')) ?>"><img src="https://i.ytimg.com/vi/<?= e($id) ?>/hqdefault.jpg" alt="" loading="lazy" width="480" height="360"><span class="yt-play" aria-hidden="true"></span></a></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<section class="sec sec-sm"><div class="wrap"><?php include __DIR__ . '/partials/cta.php'; ?></div></section>
