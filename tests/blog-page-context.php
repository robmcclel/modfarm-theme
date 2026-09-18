<?php
/** Run: php tests/blog-page-context.php (isolated renderer regression test). */
class WP_Post {
    public string $post_content = '';
    public function __construct(public int $ID, public string $post_type) {}
}
class WP_Block_Patterns_Registry {
    public static function get_instance() { return new self(); }
    public function get_registered($slug) { return ['content' => $slug]; }
}
function get_option($key, $default = false) {
    return $key === 'page_for_posts' ? $GLOBALS['page_id'] : [];
}
function get_post($id) { return $GLOBALS['pages'][$id] ?? null; }
function is_home() { return $GLOBALS['home']; }
function is_front_page() { return $GLOBALS['front']; }
function modfarm_ppb_resolve_pattern_slug($key, $value, $opts) { return $key; }
function modfarm_resolve_archive_body_pattern_slug($opts) { return 'body'; }
function modfarm_ppb_get_effective_hybrid_chrome_slugs_for_post($id, $type, $opts) {
    return ['header' => 'page_header', 'footer' => 'page_footer'];
}
function do_blocks($content) {
    $blocks = parse_blocks($content);
    if ($blocks) {
        foreach ($blocks as $block) {
            $pre_render = null;
            foreach ($GLOBALS['filters']['pre_render_block'] ?? [] as $callback) {
                $pre_render = $callback($pre_render, $block);
            }
            if ($pre_render !== null) continue;
            if (isset($block['label'])) do_blocks($block['label']);
            if (!empty($block['innerBlocks'])) do_blocks(json_encode($block['innerBlocks']));
        }
        return '';
    }
    // Model WordPress's default block context, sourced from global $post.
    $GLOBALS['renders'][] = [$content, $GLOBALS['post']?->ID];
    if ($content === ($GLOBALS['throw_on'] ?? null)) {
        throw new RuntimeException('Rendering failed');
    }
    return '';
}
// Structured fixtures isolate renderer behavior from WordPress's parser.
function parse_blocks($content) { return json_decode($content, true) ?: []; }
function add_filter($hook, $callback, $priority, $args) { $GLOBALS['filters'][$hook][] = $callback; }
function remove_filter($hook, $callback, $priority) {
    $GLOBALS['filters'][$hook] = array_values(array_filter($GLOBALS['filters'][$hook], fn($item) => $item !== $callback));
}
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/modfarm-theme/modfarm-theme/inc/ppb-zone-detector.php';
function same($expected, $actual, $label) {
    if ($expected !== $actual) throw new RuntimeException($label . ': ' . var_export($actual, true));
}

// Load the production renderer without bootstrapping unrelated theme hooks.
$source = file_get_contents(dirname(__DIR__) . '/modfarm-theme/modfarm-theme/functions.php');
$start = strpos($source, 'function modfarm_render_archive_page() {');
$end = strpos($source, "\n/**", $start);
eval(substr($source, $start, $end - $start));

$pages = [42 => new WP_Post(42, 'page')];
$page_id = 42;
$home = true;
$front = false;
$post = new WP_Post(99, 'post');
$original = $post;
$wp_query = (object) ['posts' => [$post], 'paged' => 2];
$original_query = clone $wp_query;
$renders = [];
modfarm_render_archive_page();
same([['page_header', 42], ['body', 99], ['page_footer', 42]], $renders, 'Page chrome and post list use separate contexts');
same($original, $post, 'Original post restored');
same((array) $original_query, (array) $wp_query, 'Posts and pagination unchanged');

$post = null;
$renders = [];
modfarm_render_archive_page();
same([['page_header', 42], ['body', null], ['page_footer', 42]], $renders, 'Empty blog still has its page heading');
same(null, $post, 'Empty post context restored');

$post = $original;
$home = false;
$renders = [];
modfarm_render_archive_page();
same([['archive_header_pattern', 99], ['body', 99], ['archive_footer_pattern', 99]], $renders, 'Other archives unchanged');

$home = true;
$front = true;
$renders = [];
modfarm_render_archive_page();
same([['archive_header_pattern', 99], ['body', 99], ['archive_footer_pattern', 99]], $renders, 'Latest-posts homepage unchanged');

$front = false;
$throw_on = 'page_header';
$buffer_level = ob_get_level();
try {
    modfarm_render_archive_page();
    throw new LogicException('Expected render failure');
} catch (RuntimeException $error) {
    same('Rendering failed', $error->getMessage(), 'Render error propagated');
    same($original, $post, 'Post context restored after render failure');
} finally {
    while (ob_get_level() > $buffer_level) ob_end_clean();
}
$throw_on = null;
$pages[42]->post_content = json_encode([
    ['blockName' => 'core/group', 'label' => 'background-wrapper', 'innerBlocks' => [
        ['blockName' => 'modfarm/zone', 'attrs' => ['slot' => 'header'], 'innerBlocks' => [
            ['blockName' => 'core/post-title', 'label' => 'saved-centered-title'],
        ]],
        ['blockName' => 'modfarm/zone', 'attrs' => [], 'innerBlocks' => [
            ['blockName' => 'core/paragraph', 'label' => 'old-page-body'],
        ]],
        ['blockName' => 'modfarm/zone', 'attrs' => ['slot' => 'footer'], 'innerBlocks' => [
            ['blockName' => 'core/paragraph', 'label' => 'saved-footer'],
        ]],
    ]],
]);
$saved = $pages[42]->post_content;
$renders = [];
modfarm_render_archive_page();
same([['background-wrapper', 42], ['saved-centered-title', 42], ['body', 99], ['saved-footer', 42]], $renders, 'Saved nested layout preserved and only body replaced');
same($saved, $pages[42]->post_content, 'Stored layout unchanged');
same([], $filters['pre_render_block'], 'Temporary filter removed');
same($original, $post, 'Zoned layout restores original post');
same((array) $original_query, (array) $wp_query, 'Zoned layout preserves posts and pagination');

$post = null;
$renders = [];
modfarm_render_archive_page();
same([['background-wrapper', 42], ['saved-centered-title', 42], ['body', null], ['saved-footer', 42]], $renders, 'Empty feed keeps saved layout');
same(null, $post, 'Empty zoned feed restores null context');

$post = $original;
$throw_on = 'body';
try {
    modfarm_render_archive_page();
    throw new LogicException('Expected zoned render failure');
} catch (RuntimeException $error) {
    same('Rendering failed', $error->getMessage(), 'Body error propagated');
    same($original, $post, 'Body error restores original post');
    same([], $filters['pre_render_block'], 'Body error removes temporary filter');
}
echo "Blog page context tests passed.\n";
