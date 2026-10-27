<?php
/**
 * Generic CRUD controller for simple tables (slides, cards, services, partners, pages, users).
 *
 * crud([
 *   'section' => 'slides', 'table' => 'slides', 'title' => 'Slides', 'fields' => [...], 'cols' => [...],
 *   'where' => 'grp=?', 'wp' => ['info'], 'defaults' => [...], 'pk' => 'id',
 *   'can_create' => true, 'can_delete' => true, 'sortable' => true, 'i18n' => true,
 *   'before_save' => fn(array $data, array $row, array $errors, bool $isNew): array [$data, $errors],
 *   'before_delete' => fn(array $row): ?string (error message to stop deletion),
 * ]);
 * List columns: ['key' => 'title', 'label' => '…', 'type' => 'text|i18n|thumb|bool|fn', 'fn' => fn(array $row): string (HTML)]
 */
declare(strict_types=1);

function crud(array $c): void
{
    $c += ['pk' => 'id', 'where' => '1=1', 'wp' => [], 'defaults' => [], 'can_create' => true, 'can_delete' => true,
        'sortable' => true, 'i18n' => true, 'order' => 'sort, id', 'hint' => '', 'cols' => [], 'before_save' => null,
        'before_delete' => null, 'after_save' => null, 'has_active' => true, 'new_label' => __('add_new'), 'extra_params' => [],
        'hint_link' => null];
    $cl = content_lang();
    $pk = $c['pk'];
    $self = function (array $p = []) use ($c) { return admin_url($c['section'], array_merge($c['extra_params'], $p)); };
    $find = function ($id) use ($c, $pk) {
        return row('SELECT * FROM ' . $c['table'] . ' WHERE ' . $pk . '=? AND (' . $c['where'] . ')', array_merge([$id], $c['wp']));
    };

    /* ---------- POST actions ---------- */
    if (is_post()) {
        if (!admin_post_ok()) redirect($self());
        $do = (string)($_POST['do'] ?? 'save');
        $id = $_POST['id'] ?? null;

        if ($do === 'delete' && $c['can_delete'] && $id !== null && ($r = $find($id))) {
            $stop = $c['before_delete'] ? ($c['before_delete'])($r) : null;
            if ($stop) { flash($stop, 'err'); redirect($self()); }
            foreach ($c['fields'] as $f) if (($f['type'] ?? '') === 'image') delete_upload($r[col_name($f, 'az')] ?? '');
            q('DELETE FROM ' . $c['table'] . ' WHERE ' . $pk . '=?', [$id]);
            flash(__('deleted'));
            redirect($self());
        }
        if ($do === 'toggle' && $c['has_active'] && $id !== null && ($r = $find($id))) {
            update($c['table'], ['active' => $r['active'] ? 0 : 1], $pk . '=?', [$id]);
            redirect($self());
        }
        if ($do === 'move' && $c['sortable'] && $id !== null) {
            $list = rows('SELECT ' . $pk . ' FROM ' . $c['table'] . ' WHERE ' . $c['where'] . ' ORDER BY ' . $c['order'], $c['wp']);
            $ids = array_map('strval', array_column($list, $pk));
            $pos = array_search((string)$id, $ids, true);
            $to = $pos === false ? false : $pos + ((($_POST['dir'] ?? '') === 'up') ? -1 : 1);
            if ($pos !== false && $to >= 0 && $to < count($ids)) {
                [$ids[$pos], $ids[$to]] = [$ids[$to], $ids[$pos]];
                foreach ($ids as $i => $rid) update($c['table'], ['sort' => $i + 1], $pk . '=?', [$rid]);
            }
            redirect($self());
        }
        if ($do === 'save') {
            $isNew = $id === null || $id === '';
            $r = $isNew ? $c['defaults'] : $find($id);
            if (!$r && !$isNew) redirect($self());
            if ($isNew && !$c['can_create']) redirect($self());
            [$data, $errors] = collect_fields($c['fields'], $r, $cl);
            if ($c['before_save']) [$data, $errors] = ($c['before_save'])($data, $r, $errors, $isNew);
            if ($errors) {
                flash(__('form_errors'), 'err');
                $GLOBALS['__crud_form'] = ['row' => array_merge($r, $data), 'errors' => $errors];
                $_GET['action'] = $isNew ? 'new' : '';
                if (!$isNew) $_GET['id'] = $id;
            } else {
                if ($isNew) {
                    $data = array_merge($c['defaults'], $data);
                    if ($c['sortable'] && empty($data['sort'])) {
                        $data['sort'] = (int)val('SELECT COALESCE(MAX(sort),0) FROM ' . $c['table'] . ' WHERE ' . $c['where'], $c['wp']) + 1;
                    }
                    $newId = insert($c['table'], $data);
                    $id = $pk === 'id' ? $newId : ($data[$pk] ?? $id);
                } else {
                    update($c['table'], $data, $pk . '=?', [$id]);
                }
                if ($c['after_save']) ($c['after_save'])((int)$id);
                flash(__('saved'));
                redirect(!empty($_POST['stay']) ? $self(['id' => $id]) : $self());
            }
        }
    }

    /* ---------- form ---------- */
    $action = (string)($_GET['action'] ?? '');
    $id = $_GET['id'] ?? null;
    if ($action === 'new' && $c['can_create'] || ($id !== null && $id !== '')) {
        $state = $GLOBALS['__crud_form'] ?? null;
        $isNew = $action === 'new';
        $r = $state['row'] ?? ($isNew ? $c['defaults'] : $find($id));
        if (!$r) redirect($self());
        admin_render('form', [
            'title' => ($isNew ? $c['new_label'] : __('edit')) . ' — ' . $c['title'],
            'cl' => $c['i18n'],
            'fields' => $c['fields'],
            'row' => $r,
            'errors' => $state['errors'] ?? [],
            'id' => $isNew ? '' : (string)$id,
            'action_url' => $self($isNew ? ['action' => 'new'] : ['id' => $id]),
            'back_url' => $self(),
            'can_delete' => !$isNew && $c['can_delete'],
            'hint' => $c['form_hint'] ?? '',
            'preview_url' => isset($c['preview']) && !$isNew ? ($c['preview'])($r) : '',
        ]);
    }

    /* ---------- list ---------- */
    $rows = rows('SELECT * FROM ' . $c['table'] . ' WHERE ' . $c['where'] . ' ORDER BY ' . $c['order'], $c['wp']);
    admin_render('list', [
        'title' => $c['title'],
        'cl' => $c['i18n'],
        'rows' => $rows,
        'cols' => $c['cols'],
        'pk' => $pk,
        'self' => $self,
        'can_create' => $c['can_create'],
        'can_delete' => $c['can_delete'],
        'sortable' => $c['sortable'],
        'has_active' => $c['has_active'],
        'hint' => $c['hint'],
        'hint_link' => $c['hint_link'],
        'new_label' => $c['new_label'],
    ]);
}
