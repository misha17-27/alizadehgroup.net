<?php
/**
 * Admin form fields: rendering, collecting POST data, image uploads and repeaters.
 *
 * Field spec: ['name','type','label','i18n'=>bool,'required'=>bool,'help','col'=>'half|third|full','options'=>[],'sub'=>[]]
 * Types: section, text, textarea, html (rich text), number, email, url, date, password, select, checkbox, image, gallery, icon, link, service, repeater
 * Translatable fields are stored in "<name>_<lang>" columns; the form edits one language at a time.
 */
declare(strict_types=1);

function col_name(array $f, string $cl): string
{
    return !empty($f['i18n']) ? $f['name'] . '_' . $cl : $f['name'];
}

/** JSON key of a repeater sub-field for a language: ('', az) → az, ('t', az) → t_az */
function jkey(string $base, string $cl): string
{
    return $base === '' ? $cl : $base . '_' . $cl;
}

function lang_pill(array $f, string $cl): string
{
    return !empty($f['i18n']) ? ' <span class="lp">' . strtoupper($cl) . '</span>' : '';
}

/** link targets for slides / buttons */
function link_options(): array
{
    $o = ['' => '—', 'home' => __('page_home'), 'about' => __('page_about'), 'services' => __('page_services'),
        'partners' => __('page_partners'), 'contact' => __('page_contact')];
    foreach (SECTIONS as $k) if (section_state($k) !== 'hidden') $o[$k] = __('nav_' . $k);
    foreach (services_list(false) as $s) $o['service:' . $s['skey']] = __('service') . ': ' . $s['title_az'];
    return $o;
}

/** id → title of the product categories, also the switched-off ones */
function category_options(bool $empty = true): array
{
    $o = $empty ? ['' => '—'] : [];
    foreach (rows('SELECT id, title_az, title_en FROM product_categories ORDER BY sort, id') as $c) $o[$c['id']] = L($c, 'title', content_lang());
    return $o;
}

function service_options(bool $empty = true): array
{
    $o = $empty ? ['' => '—'] : [];
    foreach (services_list(false) as $s) $o[$s['skey']] = $s['title_az'];
    return $o;
}

function select_html(string $name, array $opts, string $value, string $attrs = ''): string
{
    $h = '<select name="' . e($name) . '" ' . $attrs . '>';
    foreach ($opts as $v => $t) $h .= '<option value="' . e((string)$v) . '"' . ((string)$v === $value ? ' selected' : '') . '>' . e($t) . '</option>';
    return $h . '</select>';
}

function icon_select(string $name, string $value): string
{
    $h = '<div class="iconpick"><span class="iconpick-prev" data-icon-prev>' . icon($value ?: 'check') . '</span><select name="' . e($name) . '" data-icon-select><option value="">—</option>';
    foreach (icon_names() as $n) $h .= '<option value="' . e($n) . '"' . ($n === $value ? ' selected' : '') . '>' . e($n) . '</option>';
    return $h . '</select></div>';
}

/* ------------------------------------------------------------ render */

function render_field(array $f, array $row, string $cl, array $errors = []): string
{
    $type = $f['type'] ?? 'text';
    if ($type === 'section') {
        return '<h4 class="fsec">' . e(__($f['label'])) . (!empty($f['help']) ? '<small>' . e(__($f['help'])) . '</small>' : '') . '</h4>';
    }
    if ($type === 'repeater') return render_repeater($f, $row, $cl, $errors[$f['name']] ?? '');

    $col = col_name($f, $cl);
    $val = (string)($row[$col] ?? '');
    $id = 'f_' . $col;
    $req = !empty($f['required']);
    $err = $errors[$col] ?? '';
    $cls = 'fld fld-' . ($f['col'] ?? 'full') . ($err ? ' has-err' : '');
    $label = '<label for="' . $id . '">' . e(__($f['label'])) . lang_pill($f, $cl) . ($req ? ' <i class="req">*</i>' : '') . '</label>';
    $ra = $req ? ' required' : '';
    $ph = isset($f['placeholder']) ? ' placeholder="' . e($f['placeholder']) . '"' : '';
    $max = isset($f['max']) ? ' maxlength="' . (int)$f['max'] . '"' : '';

    switch ($type) {
        case 'textarea':
            $in = '<textarea id="' . $id . '" name="' . e($col) . '" rows="' . (int)($f['rows'] ?? 3) . '"' . $ra . $ph . '>' . e($val) . '</textarea>';
            break;
        case 'html':
            $tools = [['bold', '', '<b>B</b>', 'rte_bold'], ['italic', '', '<i>I</i>', 'rte_italic'], ['formatBlock', 'h2', 'H2', ''],
                ['formatBlock', 'h3', 'H3', ''], ['formatBlock', 'p', '¶', 'rte_p'], ['insertUnorderedList', '', '• ' . e(__('rte_ul')), ''],
                ['insertOrderedList', '', '1. ' . e(__('rte_ol')), ''], ['createLink', '', e(__('rte_link')), ''],
                ['unlink', '', e(__('rte_unlink')), ''], ['removeFormat', '', e(__('rte_clear')), '']];
            $bar = '';
            foreach ($tools as [$cmd, $arg, $lab, $tip]) {
                $bar .= '<button type="button" data-cmd="' . $cmd . '"' . ($arg !== '' ? ' data-arg="' . $arg . '"' : '')
                    . ($tip !== '' ? ' title="' . e(__($tip)) . '" aria-label="' . e(__($tip)) . '"' : '') . '>' . $lab . '</button>';
            }
            $in = '<div class="rte" data-rte data-link-prompt="' . e(__('rte_link_prompt')) . '"><div class="rte-bar">' . $bar . '</div>'
                . '<div class="rte-area" id="' . $id . '" contenteditable="true" role="textbox" aria-multiline="true" data-rte-area>' . clean_html($val) . '</div>'
                . '<textarea name="' . e($col) . '" hidden data-rte-src>' . e($val) . '</textarea></div>';
            break;
        case 'number':
            $in = '<input id="' . $id . '" type="number" name="' . e($col) . '" value="' . e($val) . '"' . $ra . '>';
            break;
        case 'password':
            $in = '<input id="' . $id . '" type="password" name="' . e($col) . '" value="" autocomplete="new-password"' . $ra . '>';
            break;
        case 'email': case 'url': case 'date':
            $in = '<input id="' . $id . '" type="' . $type . '" name="' . e($col) . '" value="' . e($val) . '"' . $ra . $ph . $max . '>';
            break;
        case 'select':
            $in = select_html($col, $f['options'] ?? [], $val, 'id="' . $id . '"' . $ra);
            break;
        case 'service':
            $in = select_html($col, service_options(), $val, 'id="' . $id . '"');
            break;
        case 'checkbox':
            return '<div class="' . $cls . '"><label class="check"><input type="checkbox" name="' . e($col) . '" value="1"' . ($val === '1' || $val === '' && !empty($f['default_on']) ? ' checked' : '') . '> ' . e(__($f['label'])) . '</label></div>';
        case 'icon':
            $in = icon_select($col, $val);
            break;
        case 'link':
            $opts = link_options();
            $custom = $val !== '' && !isset($opts[$val]);
            $opts['__custom'] = __('custom_url');
            $in = '<div class="linkpick">' . select_html($col, $opts, $custom ? '__custom' : $val, 'id="' . $id . '" data-link-select')
                . '<input type="text" name="' . e($col) . '__url" value="' . e($custom ? $val : '') . '" placeholder="https://… / #anchor" data-link-url' . ($custom ? '' : ' hidden') . '></div>';
            break;
        case 'image':
            $prev = $val !== '' ? '<img src="' . e(media($val)) . '" alt="">' : '<span class="noimg">' . icon('upload') . '</span>';
            $in = '<div class="imgfield"><div class="imgprev" data-img-prev>' . $prev . '</div><div class="imgctl">'
                . '<input id="' . $id . '" type="file" name="img_' . e($col) . '" accept=".jpg,.jpeg,.png,.webp,.gif" data-img-input>'
                . '<small class="muted">' . e(__('img_hint')) . '</small>'
                . ($val !== '' && empty($f['required']) ? '<label class="check sm"><input type="checkbox" name="rm_' . e($col) . '" value="1"> ' . e(__('img_remove')) . '</label>' : '')
                . '</div></div>';
            break;
        case 'gallery':
            $photos = '';
            foreach (jlist($val) as $p) {
                $p = (string)$p;
                $photos .= '<div class="galf-i" data-gal-item><img src="' . e(media($p)) . '" alt="" loading="lazy"><input type="hidden" name="' . e($col) . '[]" value="' . e($p) . '">'
                    . '<div class="galf-t"><button type="button" class="ib" data-gal-left title="' . e(__('move_up')) . '">←</button><button type="button" class="ib" data-gal-right title="' . e(__('move_down')) . '">→</button>'
                    . '<label class="check sm"><input type="checkbox" name="rm_' . e($col) . '[]" value="' . e($p) . '"> ' . e(__('img_remove')) . '</label></div></div>';
            }
            $in = '<div class="galf">' . ($photos !== '' ? '<div class="galf-list">' . $photos . '</div>' : '')
                . '<input id="' . $id . '" type="file" name="img_' . e($col) . '[]" accept=".jpg,.jpeg,.png,.webp,.gif" multiple>'
                . '<small class="muted">' . e(__('gallery_hint')) . '</small></div>';
            break;
        default:
            $in = '<input id="' . $id . '" type="text" name="' . e($col) . '" value="' . e($val) . '"' . $ra . $ph . $max . '>';
    }
    $help = !empty($f['help']) ? '<small class="muted">' . e(__($f['help'])) . '</small>' : '';
    $errh = $err ? '<small class="ferr">' . e($err) . '</small>' : '';
    return '<div class="' . $cls . '">' . $label . $in . $help . $errh . '</div>';
}

function render_fields(array $fields, array $row, string $cl, array $errors = []): string
{
    $h = '';
    foreach ($fields as $f) $h .= render_field($f, $row, $cl, $errors);
    return '<div class="fgrid">' . $h . '</div>';
}

function render_repeater(array $f, array $row, string $cl, string $err = ''): string
{
    $rows = jlist($row[$f['name']] ?? '');
    $name = $f['name'];
    $rowHtml = function (array $r, string $idx) use ($f, $cl, $name) {
        $u = e((string)($r['u'] ?? ''));
        $h = '<div class="rep-row" data-rep-row><input type="hidden" name="rep[' . $name . '][' . $idx . '][u]" value="' . $u . '"><div class="rep-fields">';
        foreach ($f['sub'] as $s) {
            $k = !empty($s['i18n']) ? jkey($s['key'], $cl) : $s['key'];
            $n = 'rep[' . $name . '][' . $idx . '][' . $k . ']';
            $v = (string)($r[$k] ?? '');
            $lab = '<span class="rep-lab">' . e(__($s['label'])) . (!empty($s['i18n']) ? ' <span class="lp">' . strtoupper($cl) . '</span>' : '') . '</span>';
            if ($s['type'] === 'icon') $in = icon_select($n, $v);
            elseif ($s['type'] === 'textarea') $in = '<textarea name="' . e($n) . '" rows="2">' . e($v) . '</textarea>';
            else $in = '<input type="text" name="' . e($n) . '" value="' . e($v) . '">';
            $h .= '<label class="rep-f rep-' . e($s['type']) . '">' . $lab . $in . '</label>';
        }
        $h .= '</div><div class="rep-tools"><button type="button" class="ib" data-rep-up title="' . e(__('move_up')) . '">↑</button>'
            . '<button type="button" class="ib" data-rep-down title="' . e(__('move_down')) . '">↓</button>'
            . '<button type="button" class="ib ib-del" data-rep-del title="' . e(__('delete')) . '">✕</button></div></div>';
        return $h;
    };
    $list = '';
    foreach ($rows as $i => $r) $list .= $rowHtml($r, (string)$i);
    $empty = ['u' => ''];
    return '<div class="rep fld fld-full" data-rep><div class="rep-head"><b>' . e(__($f['label'])) . '</b><span class="muted">' . count($rows) . '</span>'
        . '<button type="button" class="btn sm ghost" data-rep-add>+ ' . e(__('add_row')) . '</button></div>'
        . (!empty($f['help']) ? '<small class="muted">' . e(__($f['help'])) . '</small>' : '')
        . ($err !== '' ? '<small class="ferr">' . e($err) . '</small>' : '')
        . '<div class="rep-list" data-rep-list>' . $list . '</div>'
        . '<template data-rep-tpl>' . $rowHtml($empty, '__i__') . '</template></div>';
}

/* ------------------------------------------------------------ collect */

function delete_upload(?string $path): void
{
    $path = (string)$path;
    if ($path === '' || !starts_with($path, 'uploads/') || strpos($path, '..') !== false) return;
    $file = APP_ROOT . '/' . $path;
    if (is_file($file)) @unlink($file);
    foreach (webp_paths($path) as $copy) {
        if ($copy !== $path && is_file(APP_ROOT . '/' . $copy)) @unlink(APP_ROOT . '/' . $copy);
    }
}

/**
 * Build the DB data array from POST for the given fields.
 * @return array{0: array, 1: array} [data, errors]
 */
function collect_fields(array $fields, array $row, string $cl): array
{
    $data = [];
    $errors = [];
    foreach ($fields as $f) {
        $type = $f['type'] ?? 'text';
        if (in_array($type, ['section', 'password'], true)) continue;
        if ($type === 'repeater') { $data[$f['name']] = collect_repeater($f, $row, $cl); continue; }
        $col = col_name($f, $cl);
        switch ($type) {
            case 'number':
                $v = (int)($_POST[$col] ?? 0);
                break;
            case 'checkbox':
                $v = !empty($_POST[$col]) ? 1 : 0;
                break;
            case 'image':
                $v = (string)($row[$col] ?? '');
                $file = $_FILES['img_' . $col] ?? null;
                if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    $saved = save_image_upload($file);
                    if ($saved) { if ($v !== $saved) delete_upload($v); $v = $saved; }
                    else $errors[$col] = __('img_error');
                } elseif (!empty($_POST['rm_' . $col])) {
                    delete_upload($v);
                    $v = '';
                }
                break;
            case 'link':
                $v = trim((string)($_POST[$col] ?? ''));
                if ($v === '__custom') {
                    $v = trim((string)($_POST[$col . '__url'] ?? ''));
                    if ($v !== '' && !safe_link($v)) $errors[$col] = __('err_link');
                }
                break;
            case 'html':
                $v = clean_html((string)($_POST[$col] ?? ''));
                break;
            case 'gallery':
                [$v, $err] = collect_gallery($col, $row);
                if ($err !== '') $errors[$col] = $err;
                break;
            default:
                $v = trim(str_replace("\0", '', (string)($_POST[$col] ?? '')));
                if (isset($f['max'])) $v = mb_substr($v, 0, (int)$f['max']);
                if ($type === 'email' && $v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) $errors[$col] = __('err_email');
                if ($type === 'url' && $v !== '' && !preg_match('~^https?://~i', $v)) $errors[$col] = __('err_url');
                if ($type === 'date' && $v !== '' && !(preg_match('~^(\d{4})-(\d{2})-(\d{2})$~', $v, $m) && checkdate((int)$m[2], (int)$m[3], (int)$m[1]))) $errors[$col] = __('form_errors');
        }
        if (!empty($f['required']) && ($v === '' || $v === null)) $errors[$col] = __('err_required');
        $data[$col] = $v;
    }
    return [$data, $errors];
}

/**
 * The photo list of a gallery field after the form: the order comes from the form, only files of this row are accepted,
 * ticked photos go (their files are deleted by crud() once the row is saved), new uploads are added at the end.
 * @return array{0: string, 1: string} [JSON list, error]
 */
function collect_gallery(string $col, array $row): array
{
    $old = array_map('strval', jlist($row[$col] ?? ''));
    $posted = array_map('strval', (array)($_POST[$col] ?? []));
    $remove = array_map('strval', (array)($_POST['rm_' . $col] ?? []));
    $list = [];
    foreach (array_merge(array_intersect($posted, $old), array_diff($old, $posted)) as $p) {
        if (in_array($p, $remove, true)) $GLOBALS['__gallery_removed'][] = $p;
        else $list[] = $p;
    }
    $err = '';
    foreach (uploaded_files('img_' . $col) as $file) {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
        $saved = save_image_upload($file);
        if ($saved) $list[] = $saved;
        else $err = __('img_error');
    }
    return [json_encode(array_values($list), JSON_UNESCAPED_UNICODE), $err];
}

/** merge a submitted repeater (current language + shared keys) into the stored JSON list */
function collect_repeater(array $f, array $row, string $cl): string
{
    $old = [];
    foreach (jlist($row[$f['name']] ?? '') as $r) if (!empty($r['u'])) $old[$r['u']] = $r;
    $allowed = [];
    $allKeys = [];
    foreach ($f['sub'] as $s) {
        if (!empty($s['i18n'])) {
            $allowed[] = jkey($s['key'], $cl);
            foreach (LANGS as $l) $allKeys[] = jkey($s['key'], $l);
        } else {
            $allowed[] = $s['key'];
            $allKeys[] = $s['key'];
        }
    }
    $out = [];
    $posted = $_POST['rep'][$f['name']] ?? [];
    if (!is_array($posted)) $posted = [];
    foreach ($posted as $r) {
        if (!is_array($r)) continue;
        $u = preg_replace('~[^a-z0-9]~', '', (string)($r['u'] ?? ''));
        $isNew = $u === '' || !isset($old[$u]);
        if ($u === '') $u = uid();
        $base = $old[$u] ?? (['u' => $u] + array_fill_keys($allKeys, ''));
        $filled = false;
        foreach ($allowed as $k) {
            $v = trim(str_replace("\0", '', (string)($r[$k] ?? '')));
            $base[$k] = $v;
            if ($v !== '' && $k !== 'icon') $filled = true;
        }
        if ($isNew && !$filled) continue;   // ignore empty new rows
        $out[] = $base;
    }
    return json_encode($out, JSON_UNESCAPED_UNICODE);
}
