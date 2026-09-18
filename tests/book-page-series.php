<?php
require dirname(__DIR__,2).'/modfarm-core/tests/identity-authority.php';
function get_the_ID(){return 101;}
function get_the_terms(...$args){return array((object)array('term_id'=>8,'name'=>'Apprentice Adept'));}
require dirname(__DIR__).'/modfarm-theme/modfarm-theme/blocks/book-page-series/render.php';
$start=$assertions;
$meta[101]['series_position']='1';
$term_meta[8]['modfarm_series_planned_books']=3;
$term_meta[8]['modfarm_series_status']='complete';
function series_text($args){return trim(strip_tags(modfarm_render_book_page_series_block($args)));}
eq('Apprentice Adept Book 1',series_text(array()),'legacy block defaults to Book 1');
eq('Apprentice Adept Book 1 of 3',series_text(array('showTotal'=>true)),'total enabled independently');
eq('Apprentice Adept 1 of 3',series_text(array('showTotal'=>true,'volumeLabel'=>'')),'blank volume label supports number only');
eq('Apprentice Adept Book 1 (Completed)',series_text(array('showStatus'=>true)),'status enabled independently');
eq('Apprentice Adept Book 1 of 3 (Completed)',series_text(array('showTotal'=>true,'showStatus'=>true)),'both enabled');
$term_meta[8]['modfarm_series_planned_books']=0;$term_meta[8]['modfarm_series_status']='';
eq('Apprentice Adept Book 1',series_text(array('showTotal'=>true,'showStatus'=>true)),'unknown facts omitted');
eq('My label',series_text(array('displayMode'=>'custom','customLabel'=>'My label','showTotal'=>true,'showStatus'=>true)),'custom text unchanged');
$level=ob_get_level();eq('',series_text(array('displayMode'=>'none')),'hidden block stays hidden');eq($level,ob_get_level(),'output buffer balanced');
echo 'Book page series passed ('.($assertions-$start)." assertions).\n";
