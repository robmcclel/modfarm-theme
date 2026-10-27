<?php
/** Read-only content preview planning and replacement with WordPress adapters. */
define('ABSPATH', __DIR__);
$actions = []; $filters = []; $allowed = true; $nonce_ok = true;
function add_action($name, $fn, ...$rest) { $GLOBALS['actions'][$name] = $fn; }
function add_filter($name, $fn, ...$rest) { $GLOBALS['filters'][$name] = $fn; }
function parse_blocks($content) { return json_decode($content, true) ?: []; }
function serialize_blocks($blocks) { return json_encode($blocks); }
function modfarm_get_ppb_zone_summary_for_post(...$args) { return $GLOBALS['summary']; }
function modfarm_ppb_pattern_exists($slug) { return isset($GLOBALS['patterns'][$slug]); }
function modfarm_ppb_get_pattern_content_by_slug($slug) { return $GLOBALS['patterns'][$slug] ?? ''; }
function modfarm_find_zone_block_by_slot($blocks, $zone) {
    foreach ($blocks as $block) {
        if (($block['blockName'] ?? '') === 'modfarm/zone' && ($block['attrs']['slot'] ?? '') === $zone) return $block;
        if ($found = modfarm_find_zone_block_by_slot($block['innerBlocks'] ?? [], $zone)) return $found;
    }
    return null;
}
function modfarm_get_zone_slot_from_block_attrs($attrs) { return $attrs['slot'] ?? ''; }
function modfarm_ppb_extract_slot_payloads_from_blocks($blocks, $zone) {
    $payloads = [];
    foreach ($blocks as $block) if (($block['blockName'] ?? '') === 'modfarm/content-slot') $payloads[$block['attrs']['slot']] = $block['innerBlocks'];
    return $payloads;
}
function modfarm_ppb_hydrate_empty_slots_in_blocks($blocks, $payloads, &$changed) {
    foreach ($blocks as &$block) if (($block['blockName'] ?? '') === 'modfarm/content-slot' && empty($block['innerBlocks'])) {
        $slot = $block['attrs']['slot'];
        if (isset($payloads[$slot])) { $block['innerBlocks'] = $payloads[$slot]; $changed = true; }
    }
    return $blocks;
}
function modfarm_ppb_get_local_chrome_override_slug($id, $zone) { return $GLOBALS['local'][$zone] ?? ''; }
function absint($value) { return abs((int) $value); }
function current_user_can(...$args) { return $GLOBALS['allowed']; }
function wp_verify_nonce(...$args) { return $GLOBALS['nonce_ok']; }
function wp_unslash($value) { return $value; }
function sanitize_text_field($value) { return $value; }
function is_singular() { return true; }
function get_queried_object_id() { return $GLOBALS['query_id']; }
function wp_die($message, ...$args) { throw new RuntimeException($message); }
function nocache_headers() { $GLOBALS['no_cache'] = true; }
function show_admin_bar($show) { $GLOBALS['admin_bar'] = $show; }
function get_post($id) { return $GLOBALS['posts'][$id]; }
function get_the_ID() { return $GLOBALS['render_id']; }
function in_the_loop() { return true; }
function is_main_query() { return true; }
require __DIR__.'/../modfarm-theme/modfarm-theme/inc/settings-content-preview.php';
function check($value, $label) { if (!$value) throw new RuntimeException($label); echo "PASS: $label\n"; }
$paragraph = ['blockName'=>'core/paragraph','attrs'=>[],'innerBlocks'=>[],'innerHTML'=>'Authored copy','innerContent'=>['Authored copy']];
$slot = ['blockName'=>'modfarm/content-slot','attrs'=>['slot'=>'main'],'innerBlocks'=>[$paragraph],'innerContent'=>[null]];
$body = ['blockName'=>'modfarm/zone','attrs'=>['slot'=>'body','pattern'=>'old'],'innerBlocks'=>[$slot,$paragraph],'innerContent'=>[null,null]];
$header = ['blockName'=>'modfarm/zone','attrs'=>['slot'=>'header','pattern'=>'old','locked'=>true],'innerBlocks'=>[$paragraph],'innerContent'=>[null]];
$post = (object) ['ID'=>10,'post_type'=>'page','post_content'=>serialize_blocks([$header,$body,$paragraph])];
$posts = [10=>$post]; $original = $post->post_content;
$summary = ['content_state'=>'Zoned','layout_mode'=>'Full PPB'];
$patterns = ['new'=>serialize_blocks([array_merge($slot,['innerBlocks'=>[]])]),'clear'=>'[]'];
$plan = modfarm_settings_content_preview_plan($post, []);
check(!$plan['replacements'] && !$plan['chrome'], 'Initial selection retains the actual saved layout');
check(modfarm_settings_content_preview_replace($original, []) === $original, 'Baseline content remains byte-for-byte intact');
$plan = modfarm_settings_content_preview_plan($post, ['header'=>'new','body'=>'new']);
check(!isset($plan['replacements']['header']), 'Locked headers remain unchanged');
check(str_contains(implode(' ', $plan['messages']), 'outside matching Content Slots'), 'Body replacement explains possible content removal');
$result = parse_blocks(modfarm_settings_content_preview_replace($original, $plan['replacements']));
check($result[0] === $header && $result[2] === $paragraph, 'Other zones and surrounding blocks are preserved');
check($result[1]['innerBlocks'][0]['innerBlocks'][0] === $paragraph, 'Matching Content Slot keeps authored copy');
check(count($result[1]['innerBlocks']) === 1, 'Preview represents removal of non-slot body blocks honestly');
$clear = modfarm_settings_content_preview_plan($post, ['body'=>'clear']);
$result = parse_blocks(modfarm_settings_content_preview_replace($original, $clear['replacements']));
check(!$result[1]['innerBlocks'], 'Empty replacement visibly clears the proposed body only');
check($post->post_content === $original, 'Preview makes no persistent content changes');
$summary = ['content_state'=>'Plain','layout_mode'=>'Hybrid'];
$plan = modfarm_settings_content_preview_plan($post, ['body'=>'clear','footer'=>'new']);
check(!$plan['replacements'] && $plan['chrome'] === ['footer'=>'new'], 'Hybrid body is retained while footer defaults preview changes');
$local = ['footer'=>'custom'];
$plan = modfarm_settings_content_preview_plan($post, ['footer'=>'new']);
check(!$plan['chrome'], 'Local Hybrid overrides retain precedence');
$local = [];
$_GET = ['mf_settings_preview'=>10,'mf_settings_nonce'=>'valid','mf_settings_patterns'=>json_encode(['footer'=>'new'])];
$query_id = 10; $render_id = 10;
$allowed = false;
try { modfarm_settings_content_preview_request(); throw new LogicException('Expected denial'); } catch (RuntimeException $e) { check(!$filters, 'Unauthorized live previews install no overrides'); }
$allowed = true; $nonce_ok = false;
try { modfarm_settings_content_preview_request(); throw new LogicException('Expected denial'); } catch (RuntimeException $e) { check(!$filters, 'Invalid nonces install no overrides'); }
$nonce_ok = true; $query_id = 20;
try { modfarm_settings_content_preview_request(); throw new LogicException('Expected denial'); } catch (RuntimeException $e) { check(!$filters, 'Preview cannot target a different queried item'); }
$query_id = 10;
modfarm_settings_content_preview_request();
check($no_cache && $admin_bar === false, 'Live preview disables caching and hides admin chrome');
$chrome = $filters['modfarm_ppb_hybrid_chrome_slugs'];
check($chrome(['footer'=>'old'],10)['footer'] === 'new', 'Preview overrides the selected item only');
check($chrome(['footer'=>'old'],20)['footer'] === 'old', 'Other items retain their layout');
