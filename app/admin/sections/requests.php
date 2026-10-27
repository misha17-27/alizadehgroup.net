<?php
/** Requests from the site form: list, filters, detail, status, notes, attachments, CSV export. */
$statuses = status_labels();

/* ---------- attachment download ---------- */
if (isset($_GET['file'], $_GET['id'])) {
    $r = row('SELECT files FROM requests WHERE id=?', [(int)$_GET['id']]);
    $files = $r ? jlist($r['files']) : [];
    $f = $files[(int)$_GET['file']] ?? null;
    $path = $f ? realpath(STORAGE_DIR . '/' . $f['path']) : false;
    $root = realpath(STORAGE_DIR . '/requests');
    if (!$f || !$path || !$root || strpos($path, $root) !== 0 || !is_file($path)) {
        flash(__('file_missing'), 'err');
        redirect(admin_url('requests', ['id' => (int)$_GET['id']]));
    }
    $mime = function_exists('mime_content_type') ? (mime_content_type($path) ?: 'application/octet-stream') : 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($f['name']));
    readfile($path);
    exit;
}

/* ---------- actions ---------- */
/** deletes requests together with their attached files */
$deleteRequests = function (array $ids): void {
    $in = implode(',', array_map('intval', $ids));
    foreach (rows('SELECT files FROM requests WHERE id IN (' . $in . ')') as $r) {
        foreach (jlist($r['files']) as $f) {
            $p = STORAGE_DIR . '/' . $f['path'];
            if (is_file($p) && strpos($f['path'], '..') === false) @unlink($p);
        }
    }
    q('DELETE FROM requests WHERE id IN (' . $in . ')');
};
if (is_post() && admin_post_ok()) {
    $id = (int)($_POST['id'] ?? 0);
    $do = (string)($_POST['do'] ?? '');
    if ($do === 'update' && $id) {
        $st = (string)($_POST['status'] ?? 'new');
        update('requests', ['status' => isset($statuses[$st]) ? $st : 'new', 'note' => mb_substr(trim((string)($_POST['note'] ?? '')), 0, 5000), 'updated_at' => now()], 'id=?', [$id]);
        flash(__('saved'));
        redirect(admin_url('requests', ['id' => $id]));
    }
    if ($do === 'delete' && $id) {
        $deleteRequests([$id]);
        flash(__('deleted'));
        redirect(admin_url('requests'));
    }
    if ($do === 'bulk') {
        // list actions: the × of one row, "delete selected", or a new status for the ticked rows
        $one = (int)($_POST['del'] ?? 0);
        $ids = $one ? [$one] : array_filter(array_map('intval', (array)($_POST['ids'] ?? [])));
        $st = (string)($_POST['bulk_status'] ?? '');
        if (!$ids) {
            flash(__('none_selected'), 'err');
        } elseif ($one || !empty($_POST['bulk_delete'])) {
            $deleteRequests($ids);
            flash(__('deleted'));
        } elseif (isset($statuses[$st])) {
            q('UPDATE requests SET status=?, updated_at=? WHERE id IN (' . implode(',', $ids) . ')', [$st, now()]);
            flash(__('saved'));
        }
        redirect(admin_url('requests', array_intersect_key($_GET, array_flip(['status', 'service', 'q', 'p']))));
    }
}

/* ---------- detail ---------- */
if (isset($_GET['id'])) {
    $r = row('SELECT * FROM requests WHERE id=?', [(int)$_GET['id']]);
    if (!$r) redirect(admin_url('requests'));
    if ($r['status'] === 'new') {
        update('requests', ['status' => 'progress', 'updated_at' => now()], 'id=?', [$r['id']]);
        $r['status'] = 'progress';
    }
    admin_render('request', ['title' => __('request') . ' #' . $r['id'], 'r' => $r, 'statuses' => $statuses]);
}

/* ---------- filters ---------- */
$where = ['1=1'];
$params = [];
$fStatus = (string)($_GET['status'] ?? '');
$fService = (string)($_GET['service'] ?? '');
$fQ = trim((string)($_GET['q'] ?? ''));
if (isset($statuses[$fStatus])) { $where[] = 'status=?'; $params[] = $fStatus; }
if ($fService !== '') { $where[] = 'service_key=?'; $params[] = $fService; }
if ($fQ !== '') {
    $where[] = '(name LIKE ? OR company LIKE ? OR phone LIKE ? OR email LIKE ? OR message LIKE ?)';
    array_push($params, ...array_fill(0, 5, '%' . $fQ . '%'));
}
$sqlWhere = implode(' AND ', $where);

/* ---------- CSV export ---------- */
if (($_GET['export'] ?? '') === 'csv') {
    $list = rows('SELECT * FROM requests WHERE ' . $sqlWhere . ' ORDER BY id DESC', $params);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="sorgular-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    // plain RFC 4180 quoting, as Excel reads it (no backslash escape; PHP 8.4+ wants this argument spelled out)
    fputcsv($out, ['ID', __('date'), __('f_service'), __('status'), __('c_name'), __('c_company'), __('c_phone'), __('c_email'), __('details'), __('c_message'), __('note')], ';', '"', '');
    // visitors fill these fields: a value starting with = @ or +/- followed by more than a number would run as a formula in Excel
    $cell = function ($v): string {
        $v = (string)$v;
        return preg_match('~^[=@\t\r]|^[+\-](?![\d\s().-]*$)~', $v) ? "'" . $v : $v;
    };
    foreach ($list as $r) {
        $det = implode(' | ', array_map(function ($d) { return tx($d[0], 'az') . ': ' . $d[1]; }, jlist($r['details'])));
        fputcsv($out, array_map($cell, [$r['id'], $r['created_at'], $r['service_title'], $statuses[$r['status']] ?? $r['status'], $r['name'],
            $r['company'], $r['phone'], $r['email'], $det, $r['message'], $r['note']]), ';', '"', '');
    }
    fclose($out);
    exit;
}

/* ---------- list ---------- */
$per = 25;
$total = (int)val('SELECT COUNT(*) FROM requests WHERE ' . $sqlWhere, $params);
$pages = max(1, (int)ceil($total / $per));
$p = min($pages, max(1, (int)($_GET['p'] ?? 1)));
$list = rows('SELECT * FROM requests WHERE ' . $sqlWhere . ' ORDER BY id DESC LIMIT ' . $per . ' OFFSET ' . (($p - 1) * $per), $params);
$counts = [];
foreach (rows('SELECT status, COUNT(*) AS n FROM requests GROUP BY status') as $c) $counts[$c['status']] = (int)$c['n'];

admin_render('requests', [
    'title' => __('nav_requests'), 'list' => $list, 'statuses' => $statuses, 'counts' => $counts,
    'total' => $total, 'p' => $p, 'pages' => $pages,
    'filters' => ['status' => $fStatus, 'service' => $fService, 'q' => $fQ],
]);
