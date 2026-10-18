<?php
/** Run: php tests/hero-presentation.php /path/to/wordpress */
define('ABSPATH', rtrim($argv[1] ?? '', '/') . '/');
function add_action(...$args) {}
function add_filter($name, $callback, ...$args) { $GLOBALS['filters'][$name] = $callback; }
function __($value, ...$args) { return $value; }
function absint($value) { return abs((int) $value); }
function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES); }
function esc_url_raw($value) { return preg_match('~^https?://~', $value) ? $value : ''; }
function esc_url($value) { return esc_url_raw($value); }
function sanitize_hex_color($value) { return preg_match('/^#(?:[a-f0-9]{3}|[a-f0-9]{6})$/i', $value) ? $value : null; }
function get_option(...$args) { return $GLOBALS['settings']; }
function get_the_ID() { return 10; }
function get_post_meta($id, $key, ...$args) { return $GLOBALS['post_meta'][$id][$key] ?? ''; }
function get_term_meta($id, $key, ...$args) { return $GLOBALS['term_meta'][$id][$key] ?? ''; }
function get_the_post_thumbnail_url($id, ...$args) { return $GLOBALS['featured'][$id] ?? ''; }
function wp_get_attachment_image_url($id, ...$args) { return $id === 999 ? false : 'https://example.test/media/' . $id . '.jpg'; }
function is_category() { return false; }
function is_tag() { return false; }
function is_tax() { return ($GLOBALS['view'] ?? '') === 'term'; }
function is_archive() { return in_array($GLOBALS['view'] ?? '', ['term', 'cpt', 'date', 'author']); }
function is_home() { return ($GLOBALS['view'] ?? '') === 'home'; }
function is_search() { return ($GLOBALS['view'] ?? '') === 'search'; }
function is_post_type_archive() { return ($GLOBALS['view'] ?? '') === 'cpt'; }
function get_queried_object() { return (object) ['term_id' => 5]; }
function modfarm_get_current_collection_archive_post_type() { return 'custom_collection'; }
function modfarm_get_collection_archive_media(...$args) { return $GLOBALS['collection_hero'] ?? ''; }
function get_block_wrapper_attributes($attrs) {
    $attrs['class'] = 'wp-block-modfarm-hero-cover ' . ($attrs['class'] ?? '');
    return implode(' ', array_map(fn($key) => $key . '="' . esc_attr($attrs[$key]) . '"', array_keys($attrs)));
}
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
function _doing_it_wrong(...$args) { throw new RuntimeException(implode(' ', $args)); }
function wp_kses_uri_attributes() { return ['src', 'href']; }
function wp_kses_bad_protocol($value, ...$args) { return $value; }
function wp_allowed_protocols() { return ['http', 'https']; }
function _wp_specialchars($value, ...$args) { return htmlspecialchars($value, ENT_QUOTES); }
spl_autoload_register(function ($class) {
    $file = 'class-' . str_replace('_', '-', strtolower($class)) . '.php';
    foreach (['wp-includes/html-api/', 'wp-includes/'] as $directory) {
        if (is_file(ABSPATH . $directory . $file)) { require_once ABSPATH . $directory . $file; return; }
    }
});
require __DIR__ . '/../modfarm-theme/modfarm-theme/inc/hero-presentation.php';
require __DIR__ . '/../modfarm-theme/modfarm-theme/blocks/hero-cover/render.php';
$GLOBALS['settings'] = ['hero_default_image' => 1];
$block = (object) ['context' => ['postId' => 10]];
$default = 'https://example.test/media/1.jpg';
check(modfarm_resolve_hero_image([], $block) === $default, 'Missing local image did not use site default');
$GLOBALS['post_meta'][10]['hero_image'] = 2;
$GLOBALS['featured'][10] = 'https://example.test/featured.jpg';
check(modfarm_resolve_hero_image([], $block) === 'https://example.test/media/2.jpg', 'Featured image replaced local hero while disabled');
check(modfarm_resolve_hero_image(['useFeaturedImage' => true], $block) === $GLOBALS['featured'][10], 'Featured override failed');
check(modfarm_resolve_hero_image(['mode' => 'manual', 'manualUrl' => 'https://example.test/manual.jpg', 'useFeaturedImage' => true], $block) === $GLOBALS['featured'][10], 'Featured override did not override block image');
$GLOBALS['featured'][10] = '';
check(modfarm_resolve_hero_image(['useFeaturedImage' => true], $block) === 'https://example.test/media/2.jpg', 'Missing featured image lost local hero');
foreach (['term', 'cpt', 'date', 'author', 'home', 'search'] as $view) {
    $GLOBALS['view'] = $view;
    $GLOBALS['featured'][10] = 'https://example.test/first-loop-post.jpg';
    check(modfarm_resolve_hero_image(['useFeaturedImage' => true], $block) === $default, 'Archive leaked a queried post image: ' . $view);
}
$GLOBALS['view'] = 'term';
$GLOBALS['term_meta'][5]['archive_hero_image'] = 3;
check(modfarm_resolve_hero_image([], $block) === 'https://example.test/media/3.jpg', 'Explicit archive hero ignored');
$GLOBALS['term_meta'][5]['archive_hero_image'] = 999;
check(modfarm_resolve_hero_image([], $block) === $default, 'Deleted archive attachment did not fall back');
$GLOBALS['view'] = 'cpt';
$GLOBALS['collection_hero'] = 'https://example.test/collection.jpg';
check(modfarm_resolve_hero_image([], $block) === $GLOBALS['collection_hero'], 'Collection explicit hero ignored');
$GLOBALS['view'] = '';
$GLOBALS['settings'] = [];
$GLOBALS['post_meta'] = [];
$html = modfarm_render_hero_cover_block([], '<nav>Navigation survives</nav><h1>Title survives</h1>', $block);
check(str_contains($html, 'Navigation survives') && str_contains($html, 'Title survives') && !str_contains($html, 'No image found'), 'Missing image removed content');
$GLOBALS['settings'] = ['hero_default_image' => 1, 'hero_edge_preset' => 'bottom-deep', 'hero_edge_color' => 'primary', 'primary_color' => '#123456'];
check(str_contains(modfarm_hero_edge_markup([]), '--mfs-primary,#123456'), 'Color token missing');
$GLOBALS['settings']['primary_color'] = '#abcdef';
check(str_contains(modfarm_hero_edge_markup([]), '#abcdef'), 'Settings color change did not propagate');
check(modfarm_hero_edge_markup(['edgePreset' => 'none']) === '', 'Explicit clean edge did not override default');
check(modfarm_hero_transition(['edgePreset' => 'invalid'])['preset'] === 'none', 'Invalid preset accepted');
check(modfarm_hero_transition(['edgeColor' => 'invalid'])['color'] === 'background', 'Invalid color accepted');
check(substr_count(modfarm_render_hero_cover_block(['__preview' => true], '<h1>Hidden in background</h1>', $block), 'mf-hero-edge--') === 0, 'Preview doubles the fade');

if (is_file(ABSPATH . 'wp-includes/html-api/class-wp-html-tag-processor.php')) {
    if (is_file(ABSPATH . 'wp-includes/utf8.php')) require_once ABSPATH . 'wp-includes/utf8.php';
    $cover = $GLOBALS['filters']['render_block_core/cover'];
    $original = '<div class="wp-block-cover"><span class="wp-block-cover__background"></span><div class="wp-block-cover__inner-container"><p>Content</p></div></div>';
    $rendered = $cover($original, ['attrs' => ['modfarmImageDefaults' => true, 'backgroundType' => 'image']], $block);
    check(str_contains($rendered, $default) && str_contains($rendered, '<p>Content</p>'), 'Cover image fallback failed');
    check(substr_count($rendered, 'mf-hero-edge--bottom-deep') === 1, 'Cover fade missing or duplicated');
    $with_image = str_replace('<span', '<img class="wp-block-cover__image-background" src="old.jpg" srcset="old-2.jpg 2x"><span', $original);
    $rendered = $cover($with_image, ['attrs' => ['modfarmImageDefaults' => true]], $block);
    check(str_contains($rendered, $default) && !str_contains($rendered, 'srcset='), 'Stale Cover srcset retained');
    $video = $cover($original, ['attrs' => ['modfarmImageDefaults' => true, 'backgroundType' => 'video']], $block);
    check(!str_contains($video, '<img'), 'Cover video was replaced');
    check($cover($original, ['attrs' => ['edgePreset' => 'none']], $block) === $original, 'Unopted Cover markup changed');
    echo "PASS Cover fallback, stale srcset removal, video preservation, edge rendering and saved HTML compatibility\n";
} else {
    throw new RuntimeException('Supply a WordPress checkout to exercise real HTML Tag Processor behavior.');
}
echo "PASS Hero image precedence, archive isolation, deleted media, content preservation, live color tokens and preview layers\n";
