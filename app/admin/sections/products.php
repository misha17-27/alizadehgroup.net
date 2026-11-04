<?php
/** Product catalog (optional section "products"): every product belongs to a category. */
require_section('products');

$categories = category_options(false);
$cat = (int)($_GET['cat'] ?? 0);
if (!isset($categories[$cat])) $cat = 0;
$filter = '<form method="get" class="inl list-filter"><input type="hidden" name="section" value="products">'
    . select_html('cat', [0 => __('f_all_categories')] + $categories, (string)$cat, 'aria-label="' . e(__('f_category')) . '" onchange="this.form.submit()"') . '</form>';

crud([
    'section' => 'products', 'table' => 'products', 'title' => __('nav_products'),
    'where' => $cat ? 'category_id=?' : '1=1', 'wp' => $cat ? [$cat] : [], 'extra_params' => $cat ? ['cat' => $cat] : [],
    'defaults' => ['active' => 1, 'category_id' => $cat ?: ''],
    'hint' => __('hint_products'),
    'hint_link' => [admin_url('categories'), __('nav_categories')],
    'form_hint' => $categories ? '' : __('hint_no_categories'),
    'toolbar' => $filter,
    'fields' => [
        ['name' => 'category_id', 'type' => 'select', 'label' => 'f_category', 'options' => category_options(), 'required' => true],
        ['name' => 'title', 'type' => 'text', 'label' => 'f_title', 'i18n' => true, 'required' => true, 'max' => 255],
        ['name' => 'slug', 'type' => 'text', 'label' => 'f_slug', 'i18n' => true, 'max' => 120, 'help' => 'help_slug'],
        ['name' => 'short', 'type' => 'textarea', 'label' => 'f_short', 'i18n' => true, 'help' => 'help_item_short'],
        ['name' => 'body', 'type' => 'html', 'label' => 'f_body', 'i18n' => true],
        ['name' => 'image', 'type' => 'image', 'label' => 'f_image'],
        ['name' => 'sort', 'type' => 'number', 'label' => 'f_sort', 'col' => 'half'],
        ['name' => 'active', 'type' => 'checkbox', 'label' => 'f_active', 'col' => 'half', 'default_on' => true],
        ['type' => 'section', 'label' => 'sec_seo'],
        ['name' => 'meta_title', 'type' => 'text', 'label' => 'f_meta_title', 'i18n' => true, 'max' => 255, 'help' => 'help_meta_title'],
        ['name' => 'meta_desc', 'type' => 'textarea', 'label' => 'f_meta_desc', 'i18n' => true, 'help' => 'help_meta_desc'],
    ],
    'cols' => [
        ['key' => 'image', 'type' => 'thumb'],
        ['key' => 'title', 'type' => 'i18n', 'label' => __('f_title')],
        ['key' => 'category_id', 'type' => 'fn', 'label' => __('f_category'), 'fn' => function (array $r) use ($categories): string {
            return '<span class="pill">' . e($categories[$r['category_id']] ?? '—') . '</span>';
        }],
    ],
    'before_save' => function (array $data, array $row, array $errors, bool $isNew) use ($categories): array {
        if (!isset($categories[(int)($data['category_id'] ?? 0)])) $errors['category_id'] = __('err_required');
        [$data, $errors] = crud_slug('products', $data, $row, $errors, $isNew);
        $data['updated_at'] = now();
        return [$data, $errors];
    },
    'preview' => function (array $r): string {
        $c = row('SELECT slug_az AS cat_slug_az, slug_en AS cat_slug_en FROM product_categories WHERE id=?', [$r['category_id']]);
        return $c ? route('product', ['row' => $r + $c], content_lang()) : '';
    },
]);
