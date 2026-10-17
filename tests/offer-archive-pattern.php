<?php
$source = file_get_contents(__DIR__ . '/../modfarm-theme/modfarm-theme/functions.php');
$start = strpos($source, 'function modfarm_resolve_archive_body_pattern_slug(');
$end = strpos($source, '/**', $start);
eval(substr($source, $start, $end - $start));
function is_home() { return false; }
function is_post_type_archive($type) { return false; }
function is_category() { return false; }
function is_tag() { return false; }
function is_author() { return false; }
function is_search() { return false; }
function is_date() { return false; }
function is_tax() { return true; }
function get_queried_object() { return (object)['taxonomy'=>$GLOBALS['taxonomy']]; }
function modfarm_ppb_normalize_slug($value) { return (string)$value; }
function modfarm_ppb_resolve_pattern_slug($key, $value, $options) { return $key . ':' . $value; }
foreach (['mf_offer_type', 'mf_offer_collection'] as $taxonomy) {
  $GLOBALS['taxonomy'] = $taxonomy;
  $options = ['archive_body_pattern_mf_offer'=>'user/store'];
  if (modfarm_resolve_archive_body_pattern_slug($options) !== 'archive_body_pattern_mf_offer:user/store') throw new RuntimeException('Offer taxonomy ignored saved Store pattern');
  $options['archive_body_pattern__' . $taxonomy]='user/override';
  if (modfarm_resolve_archive_body_pattern_slug($options) !== 'archive_body_pattern:user/override') throw new RuntimeException('Term taxonomy override lost');
}
echo "PASS: both Offer taxonomies inherit saved Offers pattern and retain explicit overrides\n";
