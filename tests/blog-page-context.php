<?php
/** Run: php tests/blog-page-context.php (isolated renderer regression test). */
class WP_Post {
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
    // Model WordPress's default block context, sourced from global $post.
    $GLOBALS['renders'][] = [$content, $GLOBALS['post']?->ID];
    if ($content === ($GLOBALS['throw_on'] ?? null)) {
        throw new RuntimeException('Rendering failed');
    }
    return '';
}
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
echo "Blog page context tests passed.\n";
