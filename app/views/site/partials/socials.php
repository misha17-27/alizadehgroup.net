<?php
$soc = array_filter([
    'Instagram' => [setting('instagram'), 'ig'],
    'Facebook' => [setting('facebook'), 'fb'],
    'LinkedIn' => [setting('linkedin'), 'in'],
    'WhatsApp' => [whatsapp_url(), 'wa'],
], function ($x) { return $x[0] !== ''; });
if ($soc): ?>
<div class="soc"><?php foreach ($soc as $label => [$href, $ic]): ?><a href="<?= e($href) ?>" target="_blank" rel="noopener" aria-label="<?= e($label) ?>"><?= icon($ic) ?></a><?php endforeach; ?></div>
<?php endif;
