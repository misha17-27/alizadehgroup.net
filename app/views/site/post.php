<?php
/** one blog post: @var array $r */
$p = page('blog');
$body = L($r, 'body');
$ph_title = L($r, 'title');
$ph_text = L($r, 'short');
$ph_image = $r['image'] ?: ($p['image'] ?? '');
$ph_crumbs = [[tx('blog.nav'), route('blog')], [L($r, 'title'), null]];
$ph_chip = '<span class="dot"></span>' . e(date_text($r['published_at']) ?: tx('blog.nav'));
include __DIR__ . '/partials/page_hero.php';
?>
<?php if ($body !== ''): ?>
<section class="sec sec-sm"><div class="wrap"><div class="prose"><?= $body ?></div></div></section>
<?php endif; ?>
<section class="sec sec-sm"><div class="wrap"><?php include __DIR__ . '/partials/cta.php'; ?></div></section>
