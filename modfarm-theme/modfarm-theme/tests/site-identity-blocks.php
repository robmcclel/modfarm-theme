<?php
/** Isolated render regressions: php tests/site-identity-blocks.php */
define('ABSPATH', __DIR__);
class WP_Term { public $term_id=7; public $taxonomy='book-author'; public $name='Existing Author'; }
function get_template_directory() { return dirname(__DIR__); }
function get_template_directory_uri() { return 'https://site.test/theme'; }
function wp_parse_args($args,$defaults) { return array_merge($defaults,$args); }
function esc_html($v) { return htmlspecialchars((string)$v, ENT_QUOTES); }
function esc_attr($v) { return esc_html($v); }
function esc_url($v) { return esc_attr($v); }
function __($v,...$args) { return $v; }
function sanitize_key($v) { return strtolower(preg_replace('/[^a-zA-Z0-9_-]/','',$v)); }
function sanitize_title($v) { return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/','-',$v),'-')); }
function wp_kses_post($v) { return $v; }
function wp_strip_all_tags($v) { return strip_tags($v); }
function wpautop($v) { return $v === '' ? '' : '<p>'.$v.'</p>'; }
function absint($v) { return abs((int)$v); }
function current_user_can(...$args) { return false; }
function is_wp_error($v) { return false; }
function taxonomy_exists($v) { return $v==='book-author'; }
function get_term($id,$tax) { return new WP_Term(); }
function get_queried_object() { return null; }
function get_taxonomy($v) { return (object)['labels'=>(object)['singular_name'=>'Author']]; }
function get_term_link($v) { return 'https://site.test/author/'; }
function get_term_meta(...$args) { return ''; }
function term_description(...$args) { return '<p>Existing author biography.</p>'; }
function modfarm_get_author_socials($id) { return [['label'=>'Facebook','key'=>'facebook','url'=>'https://facebook.com/author']]; }
if (!function_exists('mb_substr')) { function mb_substr($s,$start,$length) { return substr($s,$start,$length); } }
if (!function_exists('mb_strtoupper')) { function mb_strtoupper($s) { return strtoupper($s); } }
function modfarm_get_identity_snapshot($id='') {
    $id = $id ?: $GLOBALS['primary'];
    return $GLOBALS['snapshots'][$id] ?? [];
}
set_error_handler(static function($level,$message,$file,$line) { throw new RuntimeException($message.' at '.$file.':'.$line); });
require dirname(__DIR__).'/blocks/creator-credit/render.php';
require dirname(__DIR__).'/blocks/author-social-links/render.php';
function check($ok,$label) { if (!$ok) throw new RuntimeException($label); echo "PASS $label\n"; }
function defaults($slug) {
    $schema=json_decode(file_get_contents(dirname(__DIR__).'/blocks/'.$slug.'/block.json'),true);
    return array_map(static fn($field)=>$field['default'],$schema['attributes']);
}
$GLOBALS['primary']='aethon';
$GLOBALS['snapshots']=[
    'aethon'=>[
        'identity'=>['name'=>'Aethon Books','type'=>'organization','roles'=>['publisher'],'url'=>'https://site.test/about/'],
        'images'=>['logo'=>'https://site.test/logo.png','profile'=>'https://site.test/portrait.png'],
        'profile'=>['biography'=>'Public publisher biography.','introduction'=>'Publisher introduction.'],
        'external_profiles'=>[['label'=>'Facebook','key'=>'facebook','url'=>'https://facebook.com/aethon']]
    ],
    'sia'=>[
        'identity'=>['name'=>'SIA Writing Challenge','type'=>'organization','roles'=>[],'url'=>'https://site.test/sia/'],
        'images'=>[],
        'profile'=>['introduction'=>'Public writing challenge introduction.'],
        'external_profiles'=>[]
    ],
];
$creator=array_merge(defaults('creator-credit'),['source'=>'identity','showSocialLinks'=>true,'layout'=>'horizontal']);
$html=modfarm_render_creator_credit_block($creator);
check(str_contains($html,'Aethon Books') && str_contains($html,'Public publisher biography.'),'primary identity name and public biography');
check(str_contains($html,'logo.png') && !str_contains($html,'portrait.png'),'organizations prefer logo');
check(str_contains($html,'mfc-layout-horizontal') && str_contains($html,'mfc-card__image'),'existing Creator Credit layout');
check(str_contains($html,'mfas-icon--facebook') && str_contains($html,'https://facebook.com/aethon'),'identity links use shared social icons');
check(str_contains($html,'href="https://site.test/about/"'),'name links to identity destination');
$html=modfarm_render_creator_credit_block(array_merge($creator,['identityId'=>'sia','linkToArchive'=>false]));
check(str_contains($html,'SIA Writing Challenge') && str_contains($html,'Public writing challenge introduction.'),'explicit identity and introduction fallback');
check(!str_contains($html,'Aethon Books') && !str_contains($html,'facebook.com/aethon'),'explicit source cannot fall through to primary');
$html=modfarm_render_creator_credit_block(array_merge($creator,['descriptionOverride'=>'Block-specific copy','showImage'=>false,'showName'=>false,'showRole'=>false,'showSocialLinks'=>false]));
check(str_contains($html,'Block-specific copy') && !str_contains($html,'mfc-card__image') && !str_contains($html,'mfc-card__name') && !str_contains($html,'mfc-card__role') && !str_contains($html,'mfc-card__socials'),'override and element toggles work with identity source');
check(modfarm_render_creator_credit_block(array_merge($creator,['identityId'=>'deleted']))==='','missing identity does not leak another source');
$legacy=modfarm_render_creator_credit_block(array_merge(defaults('creator-credit'),['termId'=>7,'showSocialLinks'=>true]));
check(str_contains($legacy,'Existing Author') && str_contains($legacy,'Existing author biography.') && str_contains($legacy,'facebook.com/author'),'existing author source unchanged');
$social=array_merge(defaults('author-social-links'),['source'=>'identity','align'=>'right','iconSize'=>44,'colorMode'=>'monotone']);
$html=modfarm_render_author_social_links_block($social);
check(str_contains($html,'facebook.com/aethon') && str_contains($html,'--mfas-icon-size:44px') && str_contains($html,'mfas-socials--align-right') && str_contains($html,'mfas-socials--monotone'),'standalone identity links retain presentation settings');
check(modfarm_render_author_social_links_block(array_merge($social,['identityId'=>'deleted']))==='','missing identity never falls back to author');
$GLOBALS['primary']='sia';
check(modfarm_render_author_social_links_block($social)==='','primary identity follows current site selection and omits empty links');
echo "Site Identity block render regressions passed.\n";
