<?php
/**
 * Local development router for PHP's built-in server (Apache uses .htaccess instead):
 *   php -S 127.0.0.1:8000 -t . app/dev-router.php
 */
$root = dirname(__DIR__);
$path = (string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if (preg_match('~^/(app|storage)(/|$)|/\.~', $path)) {
    http_response_code(403);
    exit('Forbidden');
}

$target = realpath($root . $path);
if ($target && strpos($target, $root) === 0 && is_dir($target) && is_file($target . '/index.php')) {
    $target .= '/index.php';
    $path = rtrim($path, '/') . '/index.php';
}
if ($target && strpos($target, $root) === 0 && is_file($target)) {
    if (substr($target, -4) !== '.php') return false;         // static file: let the server send it
    $_SERVER['SCRIPT_NAME'] = $path;
    $_SERVER['SCRIPT_FILENAME'] = $target;
    chdir(dirname($target));
    require $target;
    return true;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
chdir($root);
require $root . '/index.php';
