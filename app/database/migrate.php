<?php
/**
 * Database schema, initial content (seed.json) and text sync.
 * Loaded only by install.php and the admin panel.
 */
declare(strict_types=1);

function schema_sql(string $driver): array
{
    $my = $driver === 'mysql';
    $id = $my ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $tail = $my ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';
    $t = [];
    $t[] = "CREATE TABLE IF NOT EXISTS settings (k VARCHAR(64) NOT NULL PRIMARY KEY, v TEXT)$tail";
    $t[] = "CREATE TABLE IF NOT EXISTS texts (k VARCHAR(80) NOT NULL PRIMARY KEY, grp VARCHAR(32) NOT NULL DEFAULT '', az TEXT, en TEXT)$tail";
    $t[] = "CREATE TABLE IF NOT EXISTS pages (k VARCHAR(32) NOT NULL PRIMARY KEY, title_az VARCHAR(255) DEFAULT '', title_en VARCHAR(255) DEFAULT '',
        description_az TEXT, description_en TEXT, image VARCHAR(255) DEFAULT '')$tail";
    $t[] = "CREATE TABLE IF NOT EXISTS services (id $id, skey VARCHAR(40) NOT NULL, slug_az VARCHAR(120) NOT NULL, slug_en VARCHAR(120) NOT NULL,
        icon VARCHAR(40) DEFAULT '', form_type VARCHAR(20) DEFAULT '', image VARCHAR(255) DEFAULT '', image2 VARCHAR(255) DEFAULT '',
        sort INT NOT NULL DEFAULT 0, active TINYINT NOT NULL DEFAULT 1,
        title_az VARCHAR(255) DEFAULT '', title_en VARCHAR(255) DEFAULT '', short_az TEXT, short_en TEXT,
        page_title_az VARCHAR(255) DEFAULT '', page_title_en VARCHAR(255) DEFAULT '', page_desc_az TEXT, page_desc_en TEXT,
        meta_title_az VARCHAR(255) DEFAULT '', meta_title_en VARCHAR(255) DEFAULT '', meta_desc_az TEXT, meta_desc_en TEXT,
        extra_style VARCHAR(20) DEFAULT '', extra_title_az VARCHAR(255) DEFAULT '', extra_title_en VARCHAR(255) DEFAULT '',
        extra_q_az VARCHAR(255) DEFAULT '', extra_q_en VARCHAR(255) DEFAULT '',
        targets TEXT, offers TEXT, steps TEXT, faq TEXT, extra_items TEXT, updated_at DATETIME NULL)$tail";
    $t[] = "CREATE TABLE IF NOT EXISTS items (id $id, grp VARCHAR(20) NOT NULL, icon VARCHAR(40) DEFAULT '',
        title_az VARCHAR(255) DEFAULT '', title_en VARCHAR(255) DEFAULT '', text_az TEXT, text_en TEXT,
        link VARCHAR(255) DEFAULT '', sort INT NOT NULL DEFAULT 0, active TINYINT NOT NULL DEFAULT 1)$tail";
    $t[] = "CREATE TABLE IF NOT EXISTS slides (id $id, image VARCHAR(255) DEFAULT '', eyebrow_az VARCHAR(255) DEFAULT '', eyebrow_en VARCHAR(255) DEFAULT '',
        title_az VARCHAR(255) DEFAULT '', title_en VARCHAR(255) DEFAULT '', text_az TEXT, text_en TEXT,
        btn_az VARCHAR(80) DEFAULT '', btn_en VARCHAR(80) DEFAULT '', link VARCHAR(255) DEFAULT '',
        sort INT NOT NULL DEFAULT 0, active TINYINT NOT NULL DEFAULT 1)$tail";
    $t[] = "CREATE TABLE IF NOT EXISTS partners (id $id, name VARCHAR(160) DEFAULT '', logo VARCHAR(255) DEFAULT '', url VARCHAR(255) DEFAULT '',
        sort INT NOT NULL DEFAULT 0, active TINYINT NOT NULL DEFAULT 1)$tail";
    $t[] = "CREATE TABLE IF NOT EXISTS requests (id $id, service_key VARCHAR(40) DEFAULT '', service_title VARCHAR(255) DEFAULT '',
        name VARCHAR(160) DEFAULT '', company VARCHAR(160) DEFAULT '', phone VARCHAR(60) DEFAULT '', email VARCHAR(160) DEFAULT '',
        message TEXT, details TEXT, files TEXT, lang VARCHAR(5) DEFAULT 'az', ip VARCHAR(45) DEFAULT '', ua VARCHAR(255) DEFAULT '',
        status VARCHAR(20) NOT NULL DEFAULT 'new', note TEXT, created_at DATETIME NULL, updated_at DATETIME NULL)$tail";
    $t[] = "CREATE TABLE IF NOT EXISTS users (id $id, name VARCHAR(120) DEFAULT '', email VARCHAR(160) NOT NULL, pass VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'editor', ui_lang VARCHAR(5) NOT NULL DEFAULT 'az', active TINYINT NOT NULL DEFAULT 1,
        last_login DATETIME NULL, created_at DATETIME NULL)$tail";
    $t[] = "CREATE TABLE IF NOT EXISTS login_attempts (id $id, ip VARCHAR(45) DEFAULT '', email VARCHAR(160) DEFAULT '', ok TINYINT NOT NULL DEFAULT 0,
        created_at DATETIME NULL)$tail";
    $t[] = "CREATE UNIQUE INDEX " . ($my ? '' : 'IF NOT EXISTS ') . "ux_services_skey ON services (skey)";
    $t[] = "CREATE UNIQUE INDEX " . ($my ? '' : 'IF NOT EXISTS ') . "ux_users_email ON users (email)";
    $t[] = "CREATE INDEX " . ($my ? '' : 'IF NOT EXISTS ') . "ix_requests_status ON requests (status, created_at)";
    $t[] = "CREATE INDEX " . ($my ? '' : 'IF NOT EXISTS ') . "ix_items_grp ON items (grp, sort)";
    $t[] = "CREATE INDEX " . ($my ? '' : 'IF NOT EXISTS ') . "ix_login_ip ON login_attempts (ip, created_at)";
    return $t;
}

function migrate(): void
{
    foreach (schema_sql(db_driver()) as $sql) {
        try {
            db()->exec($sql);
        } catch (PDOException $e) {
            // MySQL has no "CREATE INDEX IF NOT EXISTS": ignore "duplicate key name"
            if (strpos($e->getMessage(), 'Duplicate key name') === false) throw $e;
        }
    }
}

/** fill an empty database from app/database/seed.json */
function seed(): void
{
    $d = json_decode((string)file_get_contents(APP_DIR . '/database/seed.json'), true);
    if (!$d) throw new RuntimeException('seed.json is missing or invalid');
    $pdo = db();
    $pdo->beginTransaction();
    try {
        if (!val('SELECT COUNT(*) FROM settings')) {
            foreach ($d['settings'] as $k => $v) insert('settings', ['k' => $k, 'v' => (string)$v]);
        }
        if (!val('SELECT COUNT(*) FROM texts')) {
            foreach ($d['texts'] as $t) insert('texts', ['k' => $t['k'], 'grp' => $t['grp'], 'az' => $t['az'], 'en' => $t['en']]);
        }
        if (!val('SELECT COUNT(*) FROM pages')) {
            foreach ($d['pages'] as $p) {
                insert('pages', ['k' => $p['k'], 'title_az' => $p['title']['az'], 'title_en' => $p['title']['en'],
                    'description_az' => $p['description']['az'], 'description_en' => $p['description']['en'], 'image' => $p['image']]);
            }
        }
        if (!val('SELECT COUNT(*) FROM services')) {
            foreach ($d['services'] as $s) {
                $u = function () { return uid(); };
                insert('services', [
                    'skey' => $s['key'], 'slug_az' => $s['slug_az'], 'slug_en' => $s['slug_en'], 'icon' => $s['icon'], 'form_type' => $s['form_type'],
                    'image' => $s['image'], 'image2' => $s['image2'], 'sort' => $s['sort'], 'active' => 1,
                    'title_az' => $s['title']['az'], 'title_en' => $s['title']['en'], 'short_az' => $s['short']['az'], 'short_en' => $s['short']['en'],
                    'page_title_az' => $s['page_title']['az'], 'page_title_en' => $s['page_title']['en'],
                    'page_desc_az' => $s['page_desc']['az'], 'page_desc_en' => $s['page_desc']['en'],
                    'meta_title_az' => $s['meta_title']['az'], 'meta_title_en' => $s['meta_title']['en'],
                    'meta_desc_az' => $s['meta_desc']['az'], 'meta_desc_en' => $s['meta_desc']['en'],
                    'extra_style' => $s['extra_style'], 'extra_title_az' => $s['extra_title']['az'], 'extra_title_en' => $s['extra_title']['en'],
                    'extra_q_az' => $s['extra_q']['az'], 'extra_q_en' => $s['extra_q']['en'],
                    'targets' => json_encode(array_map(function ($x) use ($u) { return ['u' => $u(), 'icon' => $x['icon'], 'az' => $x['text']['az'], 'en' => $x['text']['en']]; }, $s['targets']), JSON_UNESCAPED_UNICODE),
                    'offers' => json_encode(array_map(function ($x) use ($u) { return ['u' => $u(), 'az' => $x['text']['az'], 'en' => $x['text']['en']]; }, $s['offers']), JSON_UNESCAPED_UNICODE),
                    'steps' => json_encode(array_map(function ($x) use ($u) { return ['u' => $u(), 't_az' => $x['title']['az'], 't_en' => $x['title']['en'], 'd_az' => $x['text']['az'], 'd_en' => $x['text']['en']]; }, $s['steps']), JSON_UNESCAPED_UNICODE),
                    'faq' => json_encode(array_map(function ($x) use ($u) { return ['u' => $u(), 'q_az' => $x['q']['az'], 'q_en' => $x['q']['en'], 'a_az' => $x['a']['az'], 'a_en' => $x['a']['en']]; }, $s['faq']), JSON_UNESCAPED_UNICODE),
                    'extra_items' => json_encode(array_map(function ($x) use ($u) { return ['u' => $u(), 'icon' => $x['icon'], 't_az' => $x['title']['az'], 't_en' => $x['title']['en'], 'd_az' => $x['text']['az'], 'd_en' => $x['text']['en']]; }, $s['extra_items']), JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
            }
        }
        if (!val('SELECT COUNT(*) FROM items')) {
            foreach ($d['items'] as $i) {
                insert('items', ['grp' => $i['grp'], 'icon' => $i['icon'], 'title_az' => $i['title']['az'], 'title_en' => $i['title']['en'],
                    'text_az' => $i['text']['az'], 'text_en' => $i['text']['en'], 'link' => $i['link'], 'sort' => $i['sort'], 'active' => 1]);
            }
        }
        if (!val('SELECT COUNT(*) FROM slides')) {
            foreach ($d['slides'] as $s) {
                insert('slides', ['image' => $s['image'], 'eyebrow_az' => $s['eyebrow']['az'], 'eyebrow_en' => $s['eyebrow']['en'],
                    'title_az' => $s['title']['az'], 'title_en' => $s['title']['en'], 'text_az' => $s['text']['az'], 'text_en' => $s['text']['en'],
                    'btn_az' => $s['btn']['az'], 'btn_en' => $s['btn']['en'], 'link' => $s['link'], 'sort' => $s['sort'], 'active' => 1]);
            }
        }
        if (!val('SELECT COUNT(*) FROM partners')) {
            foreach ($d['partners'] as $p) insert('partners', ['name' => $p['name'], 'logo' => $p['logo'], 'url' => $p['url'], 'sort' => $p['sort'], 'active' => 1]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/** add text keys that exist in seed.json but not yet in the database (safe to run after updates) */
function sync_texts(): int
{
    $d = json_decode((string)file_get_contents(APP_DIR . '/database/seed.json'), true);
    $have = array_flip(array_column(rows('SELECT k FROM texts'), 'k'));
    $n = 0;
    foreach ($d['texts'] ?? [] as $t) {
        if (!isset($have[$t['k']])) { insert('texts', ['k' => $t['k'], 'grp' => $t['grp'], 'az' => $t['az'], 'en' => $t['en']]); $n++; }
    }
    foreach ($d['settings'] ?? [] as $k => $v) {
        if (val('SELECT COUNT(*) FROM settings WHERE k=?', [$k]) == 0) { insert('settings', ['k' => $k, 'v' => (string)$v]); $n++; }
    }
    return $n;
}
