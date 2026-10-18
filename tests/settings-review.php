<?php
/** Identity writes and appearance previews must respect permissions and stay site-local. */
define('ABSPATH', __DIR__);
$writes = []; $filters = []; $allowed = true; $valid_nonce = true;
function add_action(...$args) {}
function add_filter($name, $callback) { global $filters; $filters[$name] = $callback; }
function remove_filter($name, $callback) { global $filters; unset($filters[$name]); }
function current_user_can($cap) { global $allowed; return $allowed; }
function update_option($key, $value) { global $writes; $writes[$key] = $value; }
function set_theme_mod($key, $value) { update_option('theme:' . $key, $value); }
function sanitize_text_field($value) { return strip_tags((string) $value); }
function absint($value) { return abs((int) $value); }
function wp_attachment_is_image($id) { return $id === 42; }
function get_option($key, $default = []) { return ['nav_text_color' => '#fff']; }
function wp_unslash($value) { return $value; }
function modfarm_sanitize_navigation_profiles($value) { return $value; }
function check_ajax_referer(...$args) { global $valid_nonce; if (!$valid_nonce) throw new Exception('bad nonce'); }
function wp_send_json_error($value, $status) { throw new Exception('denied:' . $status); }
function wp_send_json_success($value) { throw new Exception('success:' . json_encode($value)); }
function modfarm_render_navigation_menu_block($attrs) {
    global $filters;
    $options = $filters['pre_option_modfarm_theme_settings']();
    if (($options['nav_text_color'] ?? '') !== '#123456') throw new Exception('unsaved color not applied');
    return $attrs['mode'];
}
function modfarm_nav_token_css() { return ''; }
function modfarm_ppb_visualizer_inline_token_styles() { return '<style></style>'; }
require __DIR__ . '/../modfarm-theme/modfarm-theme/inc/settings-review.php';
function check($value, $label) { if (!$value) throw new Exception($label); echo "PASS $label\n"; }
modfarm_save_site_identity(['blogname'=>'<b>Author</b>', 'site_icon'=>42, 'custom_logo'=>99]);
check($writes === ['blogname'=>'Author', 'site_icon'=>42], 'Identity sanitizes text and rejects non-image attachments');
$allowed = false; $writes = [];
modfarm_save_site_identity(['blogname'=>'Changed']);
check(!$writes, 'Unauthorized identity saves cannot write');
try { modfarm_ajax_settings_appearance(); } catch (Exception $error) { check($error->getMessage() === 'denied:403', 'Unauthorized previews rejected'); }
$allowed = true; $valid_nonce = false;
try { modfarm_ajax_settings_appearance(); } catch (Exception $error) { check($error->getMessage() === 'bad nonce', 'Preview nonce required'); }
$valid_nonce = true;
$_POST = ['settings'=>json_encode(['nav_text_color'=>'#123456','mobile_nav_bg_color'=>'','navigation_profiles'=>['header'=>['enabled'=>true]]])];
try { modfarm_ajax_settings_appearance(); } catch (Exception $error) { check(str_starts_with($error->getMessage(), 'success:'), 'Preview renders unsaved appearance settings'); }
check(!$writes, 'Preview makes no persistent writes');
check(!$filters, 'Preview removes temporary option and identity filters');
