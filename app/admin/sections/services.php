<?php
/** Services: list + full editor (texts per language, images, repeaters). */
$formTypes = ['' => __('ft_none'), 'transport' => __('ft_transport'), 'event' => __('ft_event'), 'hotel' => __('ft_hotel'), 'supply' => __('ft_supply')];

crud([
    'section' => 'services', 'table' => 'services', 'title' => __('nav_services'),
    'defaults' => ['active' => 1, 'icon' => 'briefcase', 'form_type' => '', 'extra_style' => '',
        'targets' => '[]', 'offers' => '[]', 'steps' => '[]', 'faq' => '[]', 'extra_items' => '[]'],
    'hint' => __('hint_services'),
    'fields' => [
        ['type' => 'section', 'label' => 'sec_main'],
        ['name' => 'title', 'type' => 'text', 'label' => 'f_title', 'i18n' => true, 'required' => true, 'max' => 255],
        ['name' => 'slug', 'type' => 'text', 'label' => 'f_slug', 'i18n' => true, 'max' => 120, 'help' => 'help_slug', 'col' => 'half'],
        ['name' => 'skey', 'type' => 'text', 'label' => 'f_key', 'required' => true, 'max' => 40, 'help' => 'help_key', 'col' => 'half'],
        ['name' => 'icon', 'type' => 'icon', 'label' => 'f_icon', 'col' => 'third'],
        ['name' => 'form_type', 'type' => 'select', 'label' => 'f_form_type', 'options' => $formTypes, 'help' => 'help_form_type', 'col' => 'third'],
        ['name' => 'sort', 'type' => 'number', 'label' => 'f_sort', 'col' => 'third'],
        ['name' => 'active', 'type' => 'checkbox', 'label' => 'f_active', 'default_on' => true],
        ['name' => 'short', 'type' => 'textarea', 'label' => 'f_short', 'i18n' => true, 'help' => 'help_short'],

        ['type' => 'section', 'label' => 'sec_page'],
        ['name' => 'page_title', 'type' => 'text', 'label' => 'f_page_title', 'i18n' => true, 'max' => 255],
        ['name' => 'page_desc', 'type' => 'textarea', 'label' => 'f_page_desc', 'i18n' => true],
        ['name' => 'image', 'type' => 'image', 'label' => 'f_image', 'col' => 'half', 'help' => 'help_service_image'],
        ['name' => 'image2', 'type' => 'image', 'label' => 'f_image2', 'col' => 'half'],

        ['type' => 'section', 'label' => 'sec_blocks', 'help' => 'help_blocks'],
        ['name' => 'targets', 'type' => 'repeater', 'label' => 'rep_targets', 'sub' => [
            ['key' => 'icon', 'type' => 'icon', 'label' => 'f_icon'],
            ['key' => '', 'type' => 'text', 'i18n' => true, 'label' => 'f_text'],
        ]],
        ['name' => 'extra_style', 'type' => 'select', 'label' => 'f_extra_style', 'col' => 'third',
            'options' => ['' => __('xs_none'), 'chips' => __('xs_chips'), 'cards' => __('xs_cards')], 'help' => 'help_extra'],
        ['name' => 'extra_title', 'type' => 'text', 'label' => 'f_extra_title', 'i18n' => true, 'col' => 'third', 'max' => 255],
        ['name' => 'extra_q', 'type' => 'text', 'label' => 'f_extra_q', 'i18n' => true, 'col' => 'third', 'max' => 255],
        ['name' => 'extra_items', 'type' => 'repeater', 'label' => 'rep_extra', 'sub' => [
            ['key' => 'icon', 'type' => 'icon', 'label' => 'f_icon'],
            ['key' => 't', 'type' => 'text', 'i18n' => true, 'label' => 'f_title'],
            ['key' => 'd', 'type' => 'textarea', 'i18n' => true, 'label' => 'f_text'],
        ]],
        ['name' => 'offers', 'type' => 'repeater', 'label' => 'rep_offers', 'sub' => [
            ['key' => '', 'type' => 'text', 'i18n' => true, 'label' => 'f_text'],
        ]],
        ['name' => 'steps', 'type' => 'repeater', 'label' => 'rep_steps', 'sub' => [
            ['key' => 't', 'type' => 'text', 'i18n' => true, 'label' => 'f_title'],
            ['key' => 'd', 'type' => 'textarea', 'i18n' => true, 'label' => 'f_text'],
        ]],
        ['name' => 'faq', 'type' => 'repeater', 'label' => 'rep_faq', 'sub' => [
            ['key' => 'q', 'type' => 'text', 'i18n' => true, 'label' => 'f_question'],
            ['key' => 'a', 'type' => 'textarea', 'i18n' => true, 'label' => 'f_answer'],
        ]],

        ['type' => 'section', 'label' => 'sec_seo'],
        ['name' => 'meta_title', 'type' => 'text', 'label' => 'f_meta_title', 'i18n' => true, 'max' => 255, 'help' => 'help_meta_title'],
        ['name' => 'meta_desc', 'type' => 'textarea', 'label' => 'f_meta_desc', 'i18n' => true, 'help' => 'help_meta_desc'],
    ],
    'cols' => [
        ['key' => 'image', 'type' => 'thumb'],
        ['key' => 'title', 'type' => 'i18n', 'label' => __('f_title')],
        ['key' => 'form_type', 'type' => 'fn', 'label' => __('f_form_type'), 'fn' => function (array $r) use ($formTypes): string {
            return '<span class="pill">' . e($formTypes[$r['form_type']] ?? '—') . '</span>';
        }],
    ],
    'before_save' => function (array $data, array $row, array $errors, bool $isNew): array {
        $cl = content_lang();
        // stable key: lowercase latin, used by the request form and links
        $key = slugify((string)($data['skey'] ?? ''));
        if ($key === '') $key = slugify((string)($data['title_' . $cl] ?? ''));
        $data['skey'] = $key;
        $dup = val('SELECT COUNT(*) FROM services WHERE skey=? AND id<>?', [$key, (int)($row['id'] ?? 0)]);
        if ($key === '' || $dup) $errors['skey'] = __('err_key');
        // URL slug for the edited language (auto from the title when empty)
        $slug = slugify((string)($data['slug_' . $cl] ?? ''));
        if ($slug === '') $slug = slugify((string)($data['title_' . $cl] ?? '')) ?: $key;
        $data['slug_' . $cl] = $slug;
        $dupSlug = val('SELECT COUNT(*) FROM services WHERE slug_' . $cl . '=? AND id<>?', [$slug, (int)($row['id'] ?? 0)]);
        if ($dupSlug) $errors['slug_' . $cl] = __('err_slug');
        if ($isNew) {
            // the other language gets a provisional slug until it is edited
            foreach (LANGS as $l) if ($l !== $cl && empty($data['slug_' . $l])) $data['slug_' . $l] = $slug;
        }
        $data['updated_at'] = now();
        return [$data, $errors];
    },
    'before_delete' => function (array $r): ?string {
        return (int)val("SELECT COUNT(*) FROM items WHERE grp='info' AND link=?", [$r['skey']]) ? __('err_service_used') : null;
    },
    'preview' => function (array $r): string {
        return route('service', ['service' => $r], content_lang());
    },
]);
