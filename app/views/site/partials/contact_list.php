<?php $ph = setting('phone'); $em = setting('email'); ?>
<ul class="cl">
  <?php if ($ph): ?><li><a href="<?= e(tel_href($ph)) ?>"><span class="cl-ic"><?= icon('phone') ?></span><span><small><?= e(tx('contact.phone')) ?></small><b><?= e($ph) ?></b></span></a></li><?php endif; ?>
  <?php if ($em): ?><li><a href="mailto:<?= e($em) ?>"><span class="cl-ic"><?= icon('mail') ?></span><span><small><?= e(tx('contact.email')) ?></small><b><?= e($em) ?></b></span></a></li><?php endif; ?>
  <li><span class="cl-row"><span class="cl-ic"><?= icon('pin') ?></span><span><small><?= e(tx('contact.address')) ?></small><b><?= e(setting('address_' . lang(), setting('address_az'))) ?></b></span></span></li>
  <li><span class="cl-row"><span class="cl-ic"><?= icon('clock') ?></span><span><small><?= e(tx('contact.hours_label')) ?></small><b><?= e(tx('contact.hours')) ?></b></span></span></li>
</ul>
