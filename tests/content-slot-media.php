<?php
/** Regression: ReadVault's image-only sample gallery must render and survive PPB storage. */
define('ABSPATH', __DIR__);
$stored = [];
$fallback_reads = 0;
function add_action(...$args) {}
function wp_parse_args($args, $defaults) { return array_merge($defaults, $args); }
function esc_attr($value) { return htmlspecialchars($value, ENT_QUOTES); }
function esc_html($value) { return htmlspecialchars($value, ENT_QUOTES); }
function esc_url($value) { return htmlspecialchars($value, ENT_QUOTES); }
function sanitize_key($value) { return $value; }
function wp_generate_uuid4() { return 'gallery-test'; }
function wp_register_script(...$args) {}
function wp_add_inline_script(...$args) {}
function wp_enqueue_script(...$args) {}
function wp_get_attachment_image($id, ...$args) { return '<img src="preview-'.$id.'.jpg" alt="">'; }
function wp_get_attachment_image_url($id, ...$args) { return 'preview-'.$id.'.jpg'; }
function get_the_ID() { return 171; }
function is_admin() { return false; }
function get_post_meta($id, $key, ...$args) {
    $GLOBALS['fallback_reads']++;
    return $key === '_modfarm_ppb_slot_payloads' ? $GLOBALS['stored'] : '';
}
function do_blocks($markup) { return $GLOBALS['gallery']; }
// WordPress serialization is represented by the exact saved self-closing fixture.
function serialize_blocks($blocks) { return $blocks ? $GLOBALS['serialized_gallery'] : ''; }
require __DIR__.'/../modfarm-theme/modfarm-theme/inc/content-slot-payloads.php';
require __DIR__.'/../modfarm-theme/modfarm-theme/blocks/content-slot/render.php';
require __DIR__.'/../modfarm-theme/modfarm-theme/blocks/simple-gallery/render.php';
function check($value, $label) {
    if (!$value) throw new RuntimeException($label);
    echo "PASS: $label\n";
}
$attributes = ['images' => [['id' => 293], ['id' => 294], ['id' => 295]], 'responsive' => false, 'visibleCount' => 3];
$gallery = modfarm_render_simple_gallery_block($attributes);
$slot_attributes = ['slot'=>'gallery', 'autofillPostContent'=>false, 'fallbackToExcerpt'=>false];
$rendered = modfarm_render_content_slot_block($slot_attributes, $gallery);
check(substr_count($rendered, '<img ') === 3, 'All three preview images render inside the gallery slot');
check(str_contains($rendered, 'data-slot="gallery"') && str_contains($rendered, 'data-linkmode="lightbox"'), 'Slot wrapper and gallery lightbox markup survive');
check($fallback_reads === 0, 'Authored gallery wins before stored payload or autofill is consulted');

check(modfarm_ppb_is_slot_markup_empty(''), 'Only an absent payload is empty');
foreach ([' ', '<!-- ordinary comment -->', '<p></p>', '<p>&nbsp;</p>', "<div><p>\xC2\xA0</p></div>", '<!-- wp:paragraph --><p>&nbsp;</p><!-- /wp:paragraph -->', '<figure><img src="sample.jpg" alt=""></figure>', '<video src="sample.mp4"></video>', '<iframe src="player"></iframe>', '<svg><path d="M0 0"/></svg>', '<custom-widget></custom-widget>', '<!-- wp:modfarm/simple-gallery {"images":[{"id":293}]} /-->'] as $content) {
    check(!modfarm_ppb_is_slot_markup_empty($content), 'Payload is preserved without interpreting its contents: '.json_encode($content));
    check(modfarm_render_content_slot_block($slot_attributes, $content) === '<div class="mf-content-slot" data-slot="gallery">'.$content.'</div>', 'Rendered content passes through unchanged');
}
$authored_empty_block = (object) ['parsed_block'=>['innerBlocks'=>[['blockName'=>'custom/dynamic']], 'innerHTML'=>"\n\n"]];
check(modfarm_render_content_slot_block($slot_attributes, '', $authored_empty_block) === '<div class="mf-content-slot" data-slot="gallery"></div>' && $fallback_reads === 0, 'A dynamic child with no output still owns the slot and does not trigger fallback');
$empty_slot = (object) ['parsed_block'=>['innerBlocks'=>[], 'innerHTML'=>"\n\n"]];
check(modfarm_render_content_slot_block($slot_attributes, "\n\n", $empty_slot) === '<div class="mf-content-slot" data-slot="gallery"></div>', 'Serialization whitespace around a slot with no children permits fallback');

$serialized_gallery = '<!-- wp:modfarm/simple-gallery '.json_encode($attributes).' /-->';
$block = ['blockName'=>'modfarm/content-slot', 'attrs'=>$slot_attributes, 'innerBlocks'=>[['blockName'=>'modfarm/simple-gallery', 'attrs'=>$attributes, 'innerBlocks'=>[], 'innerHTML'=>'', 'innerContent'=>[]]]];
$harvested = modfarm_ppb_extract_slot_payloads_from_blocks([$block], 'body');
check(($harvested['gallery']['blocks'] ?? '') === $serialized_gallery, 'Dynamic gallery is harvested into a portable payload');
$stored = modfarm_ppb_merge_slot_payloads([], $harvested);
check(modfarm_ppb_get_slot_payload_for_post(171, 'gallery')['blocks'] === $serialized_gallery, 'Stored gallery payload is accepted when read back');
check(substr_count(modfarm_render_content_slot_block($slot_attributes, ''), '<img ') === 3, 'Empty active slot rehydrates the stored gallery');
$fallback_reads = 0;
check(modfarm_render_content_slot_block($slot_attributes, '<img src="new.jpg">') === '<div class="mf-content-slot" data-slot="gallery"><img src="new.jpg"></div>' && $fallback_reads === 0, 'Current media is never replaced by an older saved payload');
check(modfarm_ppb_merge_slot_payloads($stored, []) === $stored, 'Removing the visible slot does not delete its saved payload');

$fallback_reads = 0;
check(modfarm_render_content_slot_block($slot_attributes, '', $authored_empty_block) === '<div class="mf-content-slot" data-slot="gallery"></div>' && $fallback_reads === 0, 'An authored child that renders nothing is never replaced by an older payload');
$serialized_gallery = '<!-- wp:paragraph --><p></p><!-- /wp:paragraph -->';
$blank_paragraph_slot = ['blockName'=>'modfarm/content-slot', 'attrs'=>$slot_attributes, 'innerBlocks'=>[['blockName'=>'core/paragraph', 'attrs'=>[], 'innerBlocks'=>[], 'innerHTML'=>'<p></p>', 'innerContent'=>['<p></p>']]]];
$harvested_blank = modfarm_ppb_extract_slot_payloads_from_blocks([$blank_paragraph_slot]);
check($harvested_blank['gallery']['blocks'] === $serialized_gallery, 'Even an intentionally blank paragraph is harvested and preserved');
$changed = false;
$hydrated = modfarm_ppb_hydrate_empty_slots_in_blocks([$blank_paragraph_slot], $stored, $changed);
check(!$changed && $hydrated[0]['innerBlocks'][0]['blockName'] === 'core/paragraph', 'Saved gallery does not overwrite an authored blank paragraph during hydration');
