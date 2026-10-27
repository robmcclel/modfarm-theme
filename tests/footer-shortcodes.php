<?php
/** Footer rendering must retain inherited alignment and work outside the_content. */
define('ABSPATH', __DIR__);
$filters = []; $logged_in = false;
function add_shortcode(...$args) {}
function doing_filter($name) { return $GLOBALS['in_content_filter'] ?? false; }
function add_filter($name, $fn, ...$rest) { $GLOBALS['filters'][$name] = $fn; }
function shortcode_atts($defaults, $atts, ...$args) { return array_merge($defaults, (array) $atts); }
function is_user_logged_in() { return $GLOBALS['logged_in']; }
function wp_login_url() { return '/login'; }
function wp_logout_url() { return '/logout'; }
function esc_url($text) { return htmlspecialchars($text, ENT_QUOTES); }
function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES); }
function esc_attr($text) { return htmlspecialchars($text, ENT_QUOTES); }
function wp_date($format) { return '2026'; }
function get_bloginfo($key) { return 'Books & Comics'; }
// WordPress owns shortcode parsing; this adapter observes which tokens the block filter expands.
function get_shortcode_regex($tags) { return '\\[(?:'.implode('|', $tags).')(?:[^\\]]*)\\]'; }
function do_shortcode($content) { return str_contains($content,'modfarm_footer_login') ? modfarm_footer_login_shortcode(['align'=>'center']) : 'Copyright'; }
require __DIR__.'/../modfarm-theme/modfarm-theme/inc/footer-shortcodes.php';
function check($value, $label) { if (!$value) throw new RuntimeException($label); echo "PASS: $label\n"; }
$plain = modfarm_footer_login_shortcode();
check(str_contains($plain,'href="/login"') && !str_contains($plain,'text-align'), 'Old shortcode retains inherited paragraph alignment');
check(str_contains($plain,'Books &amp; Comics'), 'Site identity is escaped');
foreach (['left','center','right'] as $align) check(str_contains(modfarm_footer_login_shortcode(['align'=>$align]),'text-align:'.$align), 'Explicit '.$align.' alignment works without a paragraph wrapper');
check(!str_contains(modfarm_footer_login_shortcode(['align'=>'center;background:red']), 'style='), 'Invalid alignment cannot inject CSS');
$logged_in = true;
check(str_contains(modfarm_footer_login_shortcode(), 'href="/logout"'), 'Signed-in users retain Logout');
$markup = '<p class="has-text-align-center">[modfarm_footer_login align="center"]</p>';
$rendered = $filters['render_block_core/paragraph']($markup);
check(str_starts_with($rendered,'<p class="has-text-align-center">') && str_ends_with($rendered,'</p>'), 'Direct archive block rendering preserves the paragraph and its alignment');
check(!str_contains($rendered,'[modfarm_footer_login'), 'Archive paragraph expands the login shortcode');
check(modfarm_render_footer_shortcodes_in_block('[unrelated_shortcode]') === '[unrelated_shortcode]', 'Unrelated shortcodes are left alone');
check($filters['render_block']('[modfarm_footer_login]', ['blockName'=>null]) !== '[modfarm_footer_login]', 'Legacy unwrapped pattern HTML also expands footer shortcodes');
check($filters['render_block']('[[modfarm_footer_login]]', ['blockName'=>'core/group']) === '[[modfarm_footer_login]]', 'Parent containers do not expand child shortcodes a second time');

$in_content_filter = true;
check(modfarm_render_footer_shortcodes_in_block('[[modfarm_footer_login]]') === '[[modfarm_footer_login]]', 'Page content leaves escaped shortcodes for the native single expansion pass');
