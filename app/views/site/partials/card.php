<?php /** list card of the optional sections: @var string $c_href @var ?string $c_img @var string $c_meta @var string $c_title @var string $c_text @var string $c_go */ ?>
<a class="cd glass rv" href="<?= e($c_href) ?>">
  <span class="cd-img"><?= picture($c_img, '', 'alt="" loading="lazy"', '(max-width: 640px) 100vw, (max-width: 960px) 50vw, 33vw') ?></span>
  <span class="cd-in">
    <?php if ($c_meta !== ''): ?><small class="cd-meta"><?= e($c_meta) ?></small><?php endif; ?>
    <h3><?= e($c_title) ?></h3>
    <?php if ($c_text !== ''): ?><p><?= e($c_text) ?></p><?php endif; ?>
    <span class="cd-go"><?= e($c_go) ?><?= icon('arrow') ?></span>
  </span>
</a>
