<?php
define('ABSPATH', '/tmp/');
function esc_html__($v,...$a) {return esc_html($v);}
function add_filter(...$a) {}
$root=dirname(__DIR__) . '/modfarm-theme/modfarm-theme';
function add_action(...$a) {} function get_option($k,$d=[]) {return match($k){'blogname'=>'Test Site','blogdescription'=>'Reader relationships',default=>$d};}
function get_theme_mod($k,$d=0){return $d;} function wp_get_nav_menus(){return [(object)['term_id'=>1,'name'=>'Main menu']];}
function esc_attr($v){return htmlspecialchars((string)$v,ENT_QUOTES);} function esc_html($v){return esc_attr($v);}function esc_html_e($v,...$a){echo esc_html($v);} function esc_url($v){return esc_attr($v);}
function __($v,...$a){return $v;} function admin_url($v=''){return 'https://test.invalid/wp-admin/'.$v;} function home_url($v=''){return 'https://test.invalid'.$v;} function get_bloginfo($k){return 'Test Site';} function get_template_directory(){global $root;return $root;} function get_template_directory_uri(){return 'https://theme.invalid';}
function selected($a,$b,$echo=true){$s=$a==$b?'selected':'';if($echo)echo $s;return $s;}function checked($a,$b,$echo=true){$s=$a==$b?'checked':'';if($echo)echo $s;return $s;}
function wp_nonce_field(...$a){}function settings_fields(...$a){}function submit_button($label,...$a){echo '<button>'.$label.'</button>';}
function wp_json_encode($v){return json_encode($v);}function add_query_arg(...$a){return '#';} function apply_filters($n,$v){return $v;}function absint($v){return abs((int)$v);}
function get_taxonomies(...$a){return ['book_series'=>(object)['object_type'=>['book'],'labels'=>(object)['name'=>'Series']]];}
function post_type_exists($t){return in_array($t,['book','post','page','offer','mf_update','modfarm_event']);}
function modfarm_get_collection_type_defs_for_settings(){return [];}function modfarm_render_ppb_run_log_markup(){return '';}
function modfarm_pattern_dropdown($a){echo '<select name="modfarm_theme_settings['.$a['id'].']"><option value="default">Default</option><option value="test/pattern">Test Pattern</option></select>';}
function modfarm_font_dropdown($a){echo '<select name="modfarm_theme_settings['.$a['id'].']"><option value="">Default</option><option value="Arial">Arial</option></select>';}
function load_function($file,$name){$tokens=token_get_all(file_get_contents($file));$on=false;$code='';$depth=0;$opened=false;foreach($tokens as $i=>$t){if(!$on&&is_array($t)&&$t[0]===T_FUNCTION){$n=$i+1;while(is_array($tokens[$n])&&$tokens[$n][0]===T_WHITESPACE)$n++;if(is_array($tokens[$n])&&$tokens[$n][1]===$name)$on=true;}if(!$on)continue;$code.=is_array($t)?$t[1]:$t;if($t==='{'){$depth++;$opened=true;}if($t==='}'&&--$depth===0&&$opened){eval($code);return;}}}
require $root.'/inc/settings-review.php';require $root.'/inc/navigation-settings.php';require $root.'/inc/archive-layouts.php';require $root.'/inc/hero-presentation.php';
foreach(['modfarm_render_settings_page','modfarm_text_field','modfarm_color_field','modfarm_select_field','modfarm_checkbox_field','modfarm_get_ppb_visualizer_content_types','modfarm_get_ppb_apply_all_content_types'] as $name)load_function($root.'/inc/modfarm-settings.php',$name);
modfarm_render_settings_page();
