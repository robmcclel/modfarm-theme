<?php
/** Content-owned archive layout contexts. Shared pattern lanes, independent choices. */
defined('ABSPATH') || exit;

function modfarm_archive_layout_contexts(): array {
    $contexts = [
        '' => ['label' => 'Other Archive Defaults', 'owner' => 'archive'],
        'book' => ['label' => 'Books Archive', 'owner' => 'book'],
        'post_index' => ['label' => 'Posts Index', 'owner' => 'post'],
        'mf_offer' => ['label' => 'Offers Archive', 'owner' => post_type_exists('offer') ? 'offer' : 'mf_offer'],
        'mf_update' => ['label' => 'Updates Archive', 'owner' => 'mf_update'],
        'modfarm_event' => ['label' => 'Events Archive', 'owner' => post_type_exists('modfarm_event') ? 'modfarm_event' : 'mf_event'],
        'author' => ['label' => 'Post Authors', 'owner' => 'post'],
        'date' => ['label' => 'Date Archives', 'owner' => 'post'],
        'search' => ['label' => 'Search Results', 'owner' => 'archive'],
    ];
    $aliases = ['book-series' => 'book_series', 'book-genre' => 'book_genre', 'book-author' => 'book_authors', 'book-authors' => 'book_authors'];
    foreach (get_taxonomies(['public' => true], 'objects') as $taxonomy => $object) {
        $owner = '';
        foreach (['book', 'post', 'offer', 'mf_offer', 'mf_update', 'modfarm_event', 'mf_event'] as $type) {
            if (in_array($type, $object->object_type, true)) { $owner = $type; break; }
        }
        if ($owner === '') {
            foreach ($object->object_type as $type) {
                if (post_type_exists($type)) { $owner = 'mfc_collection_single__' . $type; break; }
            }
        }
        if ($owner === '') continue;
        $key = $aliases[$taxonomy] ?? (in_array($taxonomy, ['category', 'post_tag'], true) ? $taxonomy : 'tax__' . $taxonomy);
        $taxonomies = $contexts[$key]['taxonomies'] ?? [];
        $taxonomies[] = $taxonomy;
        $contexts[$key] = ['label' => $object->labels->name, 'owner' => $owner, 'taxonomy' => $taxonomy, 'taxonomies' => $taxonomies];
    }
    if (function_exists('modfarm_get_collection_type_defs_for_settings')) {
        foreach (modfarm_get_collection_type_defs_for_settings() as $type => $def) {
            $contexts['cpt__' . $type] = ['label' => modfarm_get_collection_type_label_for_settings($type, $def) . ' Archive', 'owner' => 'mfc_collection_single__' . $type, 'post_type' => $type];
        }
    }
    return apply_filters('modfarm_archive_layout_contexts', $contexts);
}

function modfarm_current_archive_layout_context(): string {
    if (is_home()) return 'post_index';
    if (is_search()) return 'search';
    if (is_author()) return 'author';
    if (is_date()) return 'date';
    $object = get_queried_object();
    foreach (modfarm_archive_layout_contexts() as $key => $context) {
        if (!empty($context['taxonomy']) && (is_tax() || is_category() || is_tag()) && isset($object->taxonomy) && in_array($object->taxonomy, $context['taxonomies'] ?? [$context['taxonomy']], true)) return $key;
    }
    if (is_post_type_archive('book')) return 'book';
    if (is_post_type_archive(['offer', 'mf_offer'])) return 'mf_offer';
    if (is_post_type_archive('mf_update')) return 'mf_update';
    if (is_post_type_archive(['modfarm_event', 'mf_event'])) return 'modfarm_event';
    foreach (modfarm_archive_layout_contexts() as $key => $context) {
        if (!empty($context['post_type']) && is_post_type_archive($context['post_type'])) return $key;
    }
    return '';
}

function modfarm_archive_layout_field(string $context, string $zone): string {
    return 'archive_' . $zone . '_pattern' . ($context !== '' ? '_' . $context : '');
}

/** Preserve old selections only when a new field has never been saved. */
function modfarm_archive_layout_raw(string $context, string $zone, array $options) {
    $key = modfarm_archive_layout_field($context, $zone);
    if (array_key_exists($key, $options)) return $options[$key];
    $definition = modfarm_archive_layout_contexts()[$context] ?? [];
    if ($zone === 'body') {
        $taxonomy = $definition['taxonomy'] ?? '';
        if ($taxonomy && array_key_exists('archive_body_pattern__' . $taxonomy, $options)) return $options['archive_body_pattern__' . $taxonomy];
        if (in_array($taxonomy, ['mf_offer_type', 'mf_offer_collection'], true)) return $options['archive_body_pattern_mf_offer'] ?? 'modfarm-store/offers-archive';
        if (!empty($definition['post_type']) && function_exists('modfarm_get_collection_patterns')) {
            return modfarm_get_collection_patterns($definition['post_type'], 'archive')['body'] ?? null;
        }
        $defaults = modfarm_ppb_canonical_defaults();
        if (isset($defaults[$key])) return $defaults[$key];
    }
    return $options['archive_' . $zone . '_pattern'] ?? null;
}

function modfarm_resolve_archive_zone_pattern(string $zone, ?array $options = null, ?string $context = null): string {
    $options = $options ?? get_option('modfarm_theme_settings', []);
    $context = $context ?? modfarm_current_archive_layout_context();
    $raw = modfarm_archive_layout_raw($context, $zone, $options);
    if ((is_tax() || is_category() || is_tag()) && ($object = get_queried_object()) && !empty($object->term_id)) {
        $local = get_term_meta($object->term_id, 'archive_' . $zone . '_pattern', true);
        if (modfarm_ppb_normalize_slug($local) !== '') $raw = $local;
    }
    $key = modfarm_archive_layout_field($context, $zone);
    $defaults = modfarm_ppb_canonical_defaults();
    // Default means this archive's canonical layout, independent of old globals.
    if ($zone === 'body' && modfarm_ppb_normalize_slug($raw) === '' && !isset($defaults[$key])) {
        $owner = modfarm_archive_layout_contexts()[$context]['owner'] ?? '';
        if (in_array($owner, ['offer', 'mf_offer'], true)) $raw = $defaults['archive_body_pattern_mf_offer'];
        elseif (in_array($owner, ['post', 'mf_update', 'mf_event', 'modfarm_event'], true)) $raw = $defaults['archive_body_pattern_post_index'];
    }
    return modfarm_ppb_resolve_pattern_slug(isset($defaults[$key]) ? $key : 'archive_' . $zone . '_pattern', $raw, []);
}

function modfarm_render_content_archive_layout_settings(): void {
    foreach (modfarm_archive_layout_contexts() as $context => $definition) {
        echo '<div class="mf-settings-group mf-ppb-layout-panel" data-ppb-layout-type="' . esc_attr($definition['owner']) . '">';
        echo '<details><summary><strong>' . esc_html($definition['label']) . '</strong></summary><p class="description">Independent archive layout. Individual term overrides take precedence.</p><table class="form-table mf-form-table"><tbody>';
        foreach (['header', 'body', 'footer'] as $zone) {
            echo '<tr><th scope="row">' . esc_html(ucfirst($zone) . ' Pattern') . '</th><td>';
            modfarm_pattern_dropdown(['id' => modfarm_archive_layout_field($context, $zone), 'archive_context' => $context, 'archive_zone' => $zone]);
            echo '</td></tr>';
        }
        echo '</tbody></table><button type="button" class="button" data-archive-layout-preview="archive_context__' . esc_attr($context) . '">Preview archive layout</button><p class="description">Layout preview uses placeholder context. Open the actual archive to review its saved content and images.</p></details></div>';
    }
}

/** Native term fields are also consumed by the ModFarmOS Presentation rail. */
function modfarm_render_archive_term_pattern_fields($term): void {
    foreach (['header', 'body', 'footer'] as $zone) {
        $key = 'archive_' . $zone . '_pattern';
        $value = get_term_meta($term->term_id, $key, true);
        echo '<tr class="form-field"><th scope="row">' . esc_html(ucfirst($zone) . ' Pattern') . '</th><td><select name="' . esc_attr($key) . '">';
        echo '<option value="">Use archive default from ModFarm Settings</option>';
        foreach (modfarm_get_registered_patterns_by_category('modfarm-archive-' . $zone) as $slug => $title) {
            echo '<option value="' . esc_attr($slug) . '"' . selected($value, $slug, false) . '>' . esc_html($title) . '</option>';
        }
        echo '</select><p class="description">Overrides only this archive. Clear to follow its content-type defaults.</p></td></tr>';
    }
}
