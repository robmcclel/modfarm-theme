<?php
// Standalone hook/render regression checks. No WordPress database required.
$hooks=[]; $meta=[10=>['_mfs_menu_image_id'=>21]]; $can_edit=true; $preview=false;
function add_filter($name,$callback,$priority=10,$count=1){global $hooks;$hooks[$name][$priority][]=[$callback,$count];}
function add_action(...$args){add_filter(...$args);}
function apply_filters($name,$value,...$args){global $hooks;$list=$hooks[$name]??[];ksort($list);foreach($list as $group)foreach($group as [$cb,$count])$value=$cb(...array_slice([$value,...$args],0,$count));return $value;}
function do_action($name,...$args){global $hooks;$list=$hooks[$name]??[];ksort($list);foreach($list as $group)foreach($group as [$cb,$count])$cb(...array_slice($args,0,$count));}
function sanitize_hex_color($v){return preg_match('/^#(?:[a-f0-9]{3}|[a-f0-9]{6})$/i',(string)$v)?$v:null;}
function get_current_user_id(){return 1;}
function get_user_meta($id,$key,$single){global $user_meta;return $user_meta[$id][$key]??'';}
function update_user_meta($id,$key,$value){global $user_meta;$user_meta[$id][$key]=$value;}
function absint($v){return abs((int)$v);}
function wp_attachment_is_image($id){return in_array($id,[21,22]);}
function get_post_meta($id,$key,$single){global $meta;return $meta[$id][$key]??0;}
function update_post_meta($id,$key,$value){global $meta;$meta[$id][$key]=$value;}
function current_user_can($cap){global $can_edit;return $can_edit;}
function is_customize_preview(){global $preview;return $preview;}
function wp_unslash($v){return $v;}
function wp_verify_nonce($v,$action){return $v==='valid';}
function esc_attr($v){return htmlspecialchars((string)$v,ENT_QUOTES);}
function esc_html($v){return esc_attr($v);}
function esc_url($v){return esc_attr($v);}
function wp_strip_all_tags($v){return strip_tags($v);}
function __($v,$domain=''){return $v;}
function esc_html__($v,$domain=''){return esc_html($v);}
function esc_attr__($v,$domain=''){return esc_attr($v);}
function get_theme_mod($key,$default=false){global $test_search;return $test_search??$default;}
function get_option($key,$default=[]){global $test_options;return $test_options??$default;}
function wp_unique_id($prefix){static $id=0;return $prefix.(++$id);}
function get_block_wrapper_attributes(){return 'class="wp-block-modfarm-navigation-menu"';}
function modfarm_font_css_value($v){return $v;}
function modfarm_effective_font_family($v){return $v;}
function get_bloginfo($key){return 'Author Library';}
function home_url($v){return 'https://example.test/';}
function wp_get_attachment_image($id,$size,$icon,$attrs){
 $svg='<svg xmlns="http://www.w3.org/2000/svg" width="80" height="120"><rect width="80" height="120" fill="#305265"/><text x="40" y="55" fill="white" font-size="12" text-anchor="middle">BOOK</text></svg>';
 return '<img class="'.esc_attr($attrs['class']??'').'" src="data:image/svg+xml;base64,'.base64_encode($svg).'" alt="" width="80" height="120">';
}
class WP_Customize_Nav_Menu_Item_Setting {
 public $id='nav_menu_item[-1]',$post_id=51,$update_status='inserted',$manager;
 function post_value(){return apply_filters('customize_sanitize_'.$this->id,[], $this);}
}
require __DIR__.'/../inc/menu-images.php';
require __DIR__.'/../blocks/navigation-menu/render.php';
function check($pass,$message){if(!$pass)throw new Exception($message);fwrite(STDERR,"PASS $message\n");}
if(!in_array('--fixture',$argv)){
 $hidden=apply_filters('hidden_columns',[],(object)['id'=>'nav-menus']);check(in_array('description',$hidden)&&in_array('mfs-image',$hidden),'Optional fields hidden even with old saved preferences');
 apply_filters('update_user_metadata',null,1,'managenav-menuscolumnshidden',['description']);
 $hidden=apply_filters('hidden_columns',['description'],(object)['id'=>'nav-menus']);check(!in_array('mfs-image',$hidden)&&in_array('description',$hidden),'Explicit image opt-in remembered independently');
 check(mfs_menu_image_id(999)===0,'Reject non-image attachments');
 $item=apply_filters('wp_setup_nav_menu_item',(object)['ID'=>10]);check($item->mfs_image_id===21,'Load persisted image');
 $item=apply_filters('wp_setup_nav_menu_item',(object)['ID'=>10,'mfs_image_id'=>0]);check($item->mfs_image_id===0,'Preview removal overrides persisted image');
 $_POST=['mfs-menu-image'=>[10=>22],'mfs-menu-image-nonce'=>[10=>'invalid']];do_action('wp_update_nav_menu_item',1,10);check($meta[10]['_mfs_menu_image_id']===21,'Reject bad admin nonce');
 $_POST['mfs-menu-image-nonce'][10]='valid';$can_edit=false;do_action('wp_update_nav_menu_item',1,10);check($meta[10]['_mfs_menu_image_id']===21,'Reject unauthorized save');
 $can_edit=true;do_action('wp_update_nav_menu_item',1,10);check($meta[10]['_mfs_menu_image_id']===22,'Save valid media choice');
 $setting=new WP_Customize_Nav_Menu_Item_Setting();$manager=new class($setting){public $setting,$raw;function __construct($s){$this->setting=$s;$this->raw=[$s->id=>['mfs_image_id'=>21]];}function settings(){return [$this->setting->id=>$this->setting];}function unsanitized_post_values(){return $this->raw;}};
 $setting->manager=$manager;do_action('customize_register',$manager);check($setting->post_value()['mfs_image_id']===21,'Retain image in sanitized changeset');check(!isset($meta[51]),'Preview does not save metadata');
 do_action('customize_save_after',$manager);check($meta[51]['_mfs_menu_image_id']===21,'Publish writes remapped new item ID');
 $manager->raw[$setting->id]['mfs_image_id']=0;do_action('customize_save_after',$manager);check($meta[51]['_mfs_menu_image_id']===0,'Publish removal');
 $setting->update_status='error';$manager->raw[$setting->id]['mfs_image_id']=22;do_action('customize_save_after',$manager);check($meta[51]['_mfs_menu_image_id']===0,'Failed menu save leaves media unchanged');
 $args=(object)['mfs_enhanced'=>true,'mfs_descriptions'=>true];$item=(object)['mfs_image_id'=>21,'description'=>'A <b>book</b> & story'];
 $html=apply_filters('nav_menu_item_title','Title',$item,$args,0);check(str_contains($html,'mfs-menu-image')&&str_contains($html,'A book &amp; story'),'Render image and escaped description');
 $item->classes=['menu-icon-only'];$html=apply_filters('nav_menu_item_title','Title',$item,$args,0);check(str_contains($html,'mfs-menu-copy--hidden')&&str_contains($html,'Title'),'Icon-only keeps accessible label');
 $item->mfs_image_id=0;$html=apply_filters('nav_menu_item_title','Title',$item,$args,0);check(!str_contains($html,'mfs-menu-copy--hidden'),'Missing icon restores visible label');
 $test_options=['mobile_nav_bg_color'=>'#123456','mobile_nav_text_color'=>'#ffffff'];
 $html=modfarm_render_navigation_menu_block(['leftMenu'=>1]);check(str_contains($html,'--mfs-mobile-bg:#123456'),'Global mobile background applied');
 $html=modfarm_render_navigation_menu_block(['leftMenu'=>1,'localStyle'=>true,'mobileBg'=>'#abcdef']);check(str_contains($html,'--mfs-mobile-bg:#abcdef'),'Local mobile background overrides global');
 $html=modfarm_render_navigation_menu_block(['leftMenu'=>1,'localStyle'=>false,'mobileBg'=>'#abcdef']);check(str_contains($html,'--mfs-mobile-bg:#123456'),'Disabled override follows global');
 $test_options=[];
 require __DIR__.'/../inc/navigation-search.php';
 $search_manager=new class {
   public $sections=[],$settings=[],$controls=[];
   function add_section($id,$args){$this->sections[$id]=$args;}
   function add_setting($id,$args){$this->settings[$id]=$args;}
   function add_control($id,$args){$this->controls[$id]=$args;}
 };
 $search_register=end($hooks['customize_register'][20]);$search_register[0]($search_manager);
 check($search_manager->settings['mfs_navigation_search']['default']===false&&$search_manager->sections['mfs_navigation_search']['panel']==='nav_menus','Customizer checkbox is opt-in under Menus');
 $html=modfarm_render_navigation_menu_block(['leftMenu'=>1]);check(!str_contains($html,'role="search"'),'Search defaults off');
 $test_search=true;
 $html=modfarm_render_navigation_menu_block(['leftMenu'=>1]);check(str_contains($html,'role="search"')&&str_contains($html,'name="s"')&&str_contains($html,'method="get"'),'Customizer enables native search');
 preg_match_all('/id="(mfs-search-[^"]+)"/',$html,$ids);check(count($ids[1])===2&&count(array_unique($ids[1]))===2,'Unique search input labels');
 $html=modfarm_render_navigation_menu_block(['leftMenu'=>1,'searchMode'=>'hide']);check(!str_contains($html,'role="search"'),'Block hide overrides global');
 $html=modfarm_render_navigation_menu_block(['leftMenu'=>1,'mode'=>'footer']);check(!str_contains($html,'role="search"'),'Footer does not inherit header search');
 $test_search=false;
 $html=modfarm_render_navigation_menu_block(['leftMenu'=>1,'searchMode'=>'show']);check(str_contains($html,'role="search"'),'Block show overrides global');
 check(apply_filters('nav_menu_item_title','Title',$item,(object)[],0)==='Title','Other menu renderers unchanged');
}
function wp_nav_menu($args){
 $a=(object)$args;
 $link=function($title,$class='',$desc='',$children='')use($a){$item=(object)['mfs_image_id'=>$class?21:0,'description'=>$desc,'classes'=>explode(' ',$class)];$classes=apply_filters('nav_menu_css_class',array_filter(explode(' ','menu-item '.$class.($children?' menu-item-has-children':''))),$item,$a);return '<li class="'.esc_attr(implode(' ',$classes)).'"><a href="#destination">'.apply_filters('nav_menu_item_title',$title,$item,$a,0).'</a>'.$children.'</li>';};
 $books='';for($i=1;$i<=16;$i++)$books.=$link('Book '.$i,'menu-cover','An adventure with a long description that wraps comfortably.');
 $series=$link('The Greatcoats','menu-cover','The swashbuckling fantasy quartet','<ul class="sub-menu">'.$books.'</ul>');
 return '<ul id="'.$args['menu_id'].'" class="'.$args['menu_class'].'">'.$link('About').$link('Books','','','<ul class="sub-menu">'.$series.$link('Spellslinger','menu-cover','Magic, tricks, traps, and a talking squirrel cat.').'</ul>').$link('News','menu-icon-only').$link('Contact').'</ul>';
}
if(in_array('--fixture',$argv)){
 require __DIR__.'/../inc/navigation-search.php';
 $mode=$argv[2]??'drawer';$attrs=['leftMenu'=>1,'mobilePresentation'=>$mode,'showDescriptions'=>true,'drawerSide'=>$argv[3]??'right'];
 if($mode==='no-collapse')$attrs['noCollapse']=true;
 if(in_array('search',$argv))$attrs['searchMode']='show';
 echo '<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{margin:0;font-family:Arial;background:#eee;color:#203040} .mfs-nav{--submenu-bg:#fff;--submenu-color:#203040;--mf-nav-bg:#fff;--mf-nav-color:#203040} main{padding:40px;min-height:1600px}</style><style>'.file_get_contents(__DIR__.'/../blocks/navigation-menu/style.css').'</style></head><body><header>'.modfarm_render_navigation_menu_block($attrs).'</header><main><h1>Stories worth exploring</h1><a id="destination" href="#home">Explore the library</a></main><script>'.file_get_contents(__DIR__.'/../assets/js/navigation-toggle.js').'</script></body></html>';
}
