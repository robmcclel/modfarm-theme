<?php
/** Run: php tests/taxonomy-grid-genre-hierarchy.php */
define('ABSPATH', __DIR__);
class WP_Term { public function __construct(public $term_id,public $name,public $slug,public $parent=0,public $taxonomy='book-genre',public $count=1){} }
$genres=[1=>new WP_Term(1,'LitRPG','litrpg'),2=>new WP_Term(2,'Dungeon','dungeon',1),3=>new WP_Term(3,'Isekai','isekai',1),4=>new WP_Term(4,'Deep Dungeon','deep',2)];
$series=[10=>new WP_Term(10,'A','a',0,'book-series'),11=>new WP_Term(11,'B','b',0,'book-series')];
$profiles=[10=>['primary_genre'=>'Deep Dungeon','primary_genre_slug'=>'deep','accepted_genres'=>[['slug'=>'deep'],['slug'=>'isekai']]],11=>['primary_genre'=>'LitRPG','primary_genre_slug'=>'litrpg','accepted_genres'=>[['slug'=>'litrpg']]]];
function get_template_directory(){return dirname(__DIR__).'/modfarm-theme/modfarm-theme';}
function get_term($id,$tax='book-genre'){global $genres;return $genres[$id]??null;}
function get_term_by($field,$value,$tax){global $genres;foreach($genres as $t)if($t->$field===$value)return $t;return null;}
function get_ancestors($id,...$args){$out=[];while(($t=get_term($id)) && $t->parent){$out[]=$t->parent;$id=$t->parent;}return $out;}
function get_terms($args){global $series,$genres;$terms=$args['taxonomy']==='book-series'?$series:$genres;return array_values(array_filter($terms,fn($t)=>!isset($args['parent']) || $args['parent']===$t->parent));}
function modfarm_get_series_genre_profile($id){global $profiles;return $profiles[$id]??[];}
function sanitize_key($v){return preg_replace('/[^a-z0-9_-]/','',strtolower((string)$v));}
function sanitize_title($v){return sanitize_key($v);}
function absint($v){return abs((int)$v);}
function taxonomy_exists($v){return true;}
function is_wp_error($v){return false;}
function is_taxonomy_hierarchical($v){return true;}
function __($v,...$args){return $v;}
function check($ok,$message){if(!$ok)throw new RuntimeException($message);}
require get_template_directory().'/blocks/taxonomy-grid/render.php';
$r=modfarm_taxonomy_grid_resolve_terms(['seriesGenreSlug'=>'litrpg','groupMode'=>'series_by_genre']);
check(count($r['terms'])===2,'Parent includes descendant series, once each');
$r=modfarm_taxonomy_grid_resolve_terms(['seriesGenreSlug'=>'dungeon']);
check(count($r['terms'])===1,'Specific branch includes deeper descendants');
$r=modfarm_taxonomy_grid_resolve_terms(['seriesGenreSlug'=>'dungeon','includeSubgenres'=>false]);
check(count($r['terms'])===0,'Exact matching excludes deeper descendants');
$groups=modfarm_taxgrid_genre_groups(array_values($series),['seriesGenreSlug'=>'litrpg','groupBySubgenre'=>true]);
check(isset($groups['dungeon'],$groups['isekai'],$groups['other-litrpg']),'Subgenre grouping rolls up deeper branches and preserves broad-only series');
check(count($groups['dungeon']['items'])===1 && count($groups['isekai']['items'])===1,'Multiple memberships supported');
check(modfarm_taxgrid_book_genre_query(['seriesGenreSlug'=>'dungeon'])===[],'Complete series default');
$q=modfarm_taxgrid_book_genre_query(['seriesGenreSlug'=>'dungeon','matchingBooksOnly'=>true,'includeSubgenres'=>false]);
check($q[0]['terms']===['dungeon'] && !$q[0]['include_children'],'Strict book query respects selected scope');
$groups=modfarm_taxgrid_genre_groups([$series[10]],[]);
check(isset($groups['deep']),'Existing primary grouping preserved');
echo "Taxonomy Grid genre hierarchy tests passed.\n";
