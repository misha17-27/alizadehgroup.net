<?php
/**
 * Alizadeh Group — admin panel entry point: admin/index.php?section=...
 */
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
require APP_DIR . '/database/migrate.php';
require APP_DIR . '/admin/admin.php';

try {
    admin_run();
} catch (Throwable $e) {
    log_line('app.log', 'admin: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><title>Admin</title><p style="font:15px system-ui;padding:40px">Server error. Details: storage/logs/app.log</p>';
}
