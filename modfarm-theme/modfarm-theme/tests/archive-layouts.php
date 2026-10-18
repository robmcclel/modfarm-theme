<?php
/** Exercise real archive resolution and settings sanitization without a database. */
define('ABSPATH', __DIR__);
if (in_array('--fixture', $argv, true)) ob_start();
$options = []; $term_meta = []; $query = []; $taxonomies = [];
function get_option($key, $default = []) { global $options; return $key === 'modfarm_theme_settings' ? $options : $default; }
function get_taxonomies(...$args) { global $taxonomies; return $taxonomies; }
function post_type_exists($type) { return in_array($type, ['book', 'post', 'page', 'mf_offer', 'mf_update', 'modfarm_event', 'comic']); }
function apply_filters($name, $value) { return $value; }
function get_queried_object() { global $query; return $query['object'] ?? null; }
function get_term_meta($id, $key, $single) { global $term_meta; return $term_meta[$id][$key] ?? ''; }
function is_home() { global $query; return ($query['kind'] ?? '') === 'home'; }
function is_search() { global $query; return ($query['kind'] ?? '') === 'search'; }
function is_author() { global $query; return ($query['kind'] ?? '') === 'author'; }
function is_date() { global $query; return ($query['kind'] ?? '') === 'date'; }
function is_tax() { global $query; return ($query['kind'] ?? '') === 'tax'; }
function is_category() { global $query; return ($query['object']->taxonomy ?? '') === 'category'; }
function is_tag() { return false; }
function is_post_type_archive($types = null) { global $query; return ($query['kind'] ?? '') === 'cpt' && ($types === null || in_array($query['type'], (array) $types)); }
function sanitize_text_field($value) { return is_scalar($value) ? strip_tags((string) $value) : ''; }
function modfarm_sanitize_navigation_profiles($value) { return []; }
function modfarm_save_collection_ppb_settings_from_request() {}
function modfarm_get_collection_type_defs_for_settings() { return ['comic' => ['labels' => ['plural' => 'Comics']]]; }
function modfarm_get_collection_type_label_for_settings($slug, $def) { return $def['labels']['plural']; }
function modfarm_get_collection_patterns($type, $context) { return ['body' => 'test/comic-body']; }
function modfarm_ppb_pattern_exists($slug) { return $slug !== 'missing/pattern' && $slug !== ''; }
function load_function($file, $name) {
    $tokens = token_get_all(file_get_contents($file)); $capture = false; $code = ''; $depth = 0; $opened = false;
    foreach ($tokens as $i => $token) {
        if (!$capture && is_array($token) && $token[0] === T_FUNCTION) {
            $next = $i + 1; while (is_array($tokens[$next]) && $tokens[$next][0] === T_WHITESPACE) $next++;
            if (is_array($tokens[$next]) && $tokens[$next][1] === $name) $capture = true;
        }
        if (!$capture) continue;
        $code .= is_array($token) ? $token[1] : $token;
        if ($token === '{') { $depth++; $opened = true; }
        if ($token === '}' && --$depth === 0 && $opened) { eval($code); return; }
    }
    throw new Exception('Function not found: ' . $name);
}
foreach (['modfarm_ppb_canonical_defaults', 'modfarm_ppb_legacy_slug_map', 'modfarm_ppb_normalize_slug', 'modfarm_ppb_resolve_pattern_slug'] as $name) load_function(__DIR__ . '/../functions.php', $name);
require __DIR__ . '/../inc/archive-layouts.php';
load_function(__DIR__ . '/../inc/modfarm-settings.php', 'modfarm_sanitize_settings');
function check($condition, $message) { if (!$condition) throw new Exception($message); echo "PASS $message\n"; }
foreach (['book-series' => 'book', 'book-language' => 'book', 'book-tags' => 'book', 'mf_offer_type' => 'mf_offer', 'mf_offer_collection' => 'mf_offer', 'category' => 'post', 'comic-world' => 'comic'] as $slug => $owner) {
    $taxonomies[$slug] = (object) ['object_type' => [$owner], 'labels' => (object) ['name' => $slug]];
}
$contexts = modfarm_archive_layout_contexts();
check(isset($contexts['tax__book-language'], $contexts['tax__book-tags'], $contexts['cpt__comic']), 'Languages, tags and future collection archives are registered');
check($contexts['tax__comic-world']['owner'] === 'mfc_collection_single__comic', 'Future taxonomy belongs to its content section');
$options = ['archive_header_pattern' => 'test/old-header', 'archive_footer_pattern' => 'test/old-footer', 'archive_body_pattern_mf_offer' => 'test/old-offers'];
$query = ['kind' => 'tax', 'object' => (object) ['taxonomy' => 'mf_offer_type', 'term_id' => 7]];
check(modfarm_resolve_archive_zone_pattern('header') === 'test/old-header', 'Existing shared header retained before first save');
check(modfarm_resolve_archive_zone_pattern('body') === 'test/old-offers', 'Offer taxonomy retains legacy Offer body');
$options['archive_body_pattern__mf_offer_type'] = 'user/legacy-taxonomy';
check(modfarm_resolve_archive_zone_pattern('body') === 'user/legacy-taxonomy', 'Legacy generic taxonomy selections remain compatible');
$type = 'archive_header_pattern_tax__mf_offer_type';
$collection = 'archive_header_pattern_tax__mf_offer_collection';
$clean = modfarm_sanitize_settings([$type => 'test/types', $collection => 'test/collections', 'archive_body_pattern_tax__book-language' => 'test/languages']);
check($clean[$type] === 'test/types' && $clean[$collection] === 'test/collections', 'Independent taxonomy settings survive sanitizer');
$options = $clean;
check(modfarm_resolve_archive_zone_pattern('header') === 'test/types', 'Offer Type uses its own header');
$query['object']->taxonomy = 'mf_offer_collection';
check(modfarm_resolve_archive_zone_pattern('header') === 'test/collections', 'Offer Collection uses a different header');
$term_meta[7]['archive_header_pattern'] = 'user/local';
check(modfarm_resolve_archive_zone_pattern('header') === 'user/local', 'Local term override takes precedence');
$term_meta[7]['archive_header_pattern'] = '';
check(modfarm_resolve_archive_zone_pattern('header') === 'test/collections', 'Clearing local override follows archive default');
$options[$collection] = '';
check(modfarm_resolve_archive_zone_pattern('header') === 'modfarm/archive-header-basic', 'Explicit Default does not rejoin legacy shared header');
$options['archive_body_pattern_tax__mf_offer_collection'] = '';
check(modfarm_resolve_archive_zone_pattern('body') === 'modfarm-store/offers-archive', 'Reset Offer taxonomy keeps domain-appropriate body');
$query = ['kind' => 'cpt', 'type' => 'comic'];
check(modfarm_resolve_archive_zone_pattern('body') === 'test/comic-body', 'External collection archive body remains compatible');
$query = ['kind' => 'home'];
check(modfarm_resolve_archive_zone_pattern('body') === 'modfarm/post-archive-layout', 'Posts index keeps its feed default');
$query = ['kind' => 'search'];
check(modfarm_resolve_archive_zone_pattern('body') === 'modfarm/search-results-layout', 'Search keeps its search default');

if (in_array('--fixture', $argv, true)) {
    ob_end_clean();
    function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES); }
    function esc_html($value) { return esc_attr($value); }
    function __($value, $domain = '') { return $value; }
    function esc_html__($value, $domain = '') { return esc_html($value); }
    function selected($value, $expected, $echo = true) { $out = $value === $expected ? ' selected' : ''; if ($echo) echo $out; return $out; }
    function modfarm_get_registered_patterns_by_category($category, $user = true) { return ['test/' . $category => $category]; }
    load_function(__DIR__ . '/../inc/modfarm-settings.php', 'modfarm_ppb_pattern_category_map');
    load_function(__DIR__ . '/../inc/modfarm-settings.php', 'modfarm_get_registered_patterns_for_field');
    load_function(__DIR__ . '/../inc/modfarm-settings.php', 'modfarm_ppb_normalize_admin_pattern_value');
    load_function(__DIR__ . '/../inc/modfarm-settings.php', 'modfarm_pattern_dropdown');
    modfarm_render_content_archive_layout_settings();
}
