<?php
/**
 * WebP copies of the images that ship with the site (assets/img, assets/img/partners).
 * Run after adding or replacing such an image:  php app/tools/make-webp.php
 * (admin uploads get their copies on upload, older uploads on their first view — see make_webp()).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__) . '/bootstrap.php';

$files = array_merge(glob(APP_ROOT . '/assets/img/*.{jpg,jpeg,png}', GLOB_BRACE), glob(APP_ROOT . '/assets/img/partners/*.{jpg,jpeg,png}', GLOB_BRACE));
foreach ($files as $f) {
    if (basename($f) === 'favicon.png') continue;
    foreach (webp_paths($f) as $copy) if (is_file($copy)) unlink($copy);
    echo (make_webp($f) ? 'ok    ' : 'FAIL  ') . substr($f, strlen(APP_ROOT) + 1) . "\n";
}
