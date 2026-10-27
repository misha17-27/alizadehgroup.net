<?php
/**
 * Copy to app/config.php (or run /install.php, which writes it for you).
 * app/config.php is ignored by git, so deploys never overwrite it.
 */
return [
    'db' => [
        'driver' => 'mysql',          // 'mysql' (cPanel) or 'sqlite' (local development)
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'cpaneluser_site',
        'user' => 'cpaneluser_site',
        'pass' => 'change-me',
        // 'path' => __DIR__ . '/../storage/database.sqlite',   // for sqlite
    ],
    // URL prefix when the site lives in a sub-folder, e.g. '/yeni'. null = detect automatically.
    'base_path' => null,
];
