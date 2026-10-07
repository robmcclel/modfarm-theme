<?php
/** Run: php tests/taxonomy-grid-group-mode.php */
define('ABSPATH', __DIR__);
function get_template_directory() { return dirname(__DIR__) . '/modfarm-theme/modfarm-theme'; }
function sanitize_key($value) { return preg_replace('/[^a-z0-9_-]/', '', strtolower((string)$value)); }
function sanitize_title($value) { return sanitize_key($value); }
function taxonomy_exists($taxonomy) { return true; }
function get_terms($args) { return []; }
function is_wp_error($value) { return false; }
function absint($value) { return abs((int)$value); }
set_error_handler(function($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
require get_template_directory() . '/blocks/taxonomy-grid/render.php';
$cases = [
  [[], 'terms', 'book-series'],
  [['groupMode' => null], 'terms', 'book-series'],
  [['groupMode' => 'invalid'], 'terms', 'book-series'],
  [['groupMode' => 'terms', 'taxonomy' => 'book-author'], 'terms', 'book-author'],
  [['groupMode' => 'series_by_genre', 'taxonomy' => 'book-author'], 'series_by_genre', 'book-series'],
  [['groupMode' => 'books_by_series', 'taxonomy' => 'book-author'], 'books_by_series', 'book-series'],
];
foreach ($cases as [$attributes, $expected_mode, $expected_taxonomy]) {
  $resolved = modfarm_taxonomy_grid_resolve_terms($attributes);
  if ($resolved['group_mode'] !== $expected_mode || $resolved['taxonomy'] !== $expected_taxonomy) throw new RuntimeException('Unexpected mode or taxonomy: ' . json_encode($attributes));
}
restore_error_handler();
echo "Taxonomy Grid group mode: 6 cases passed without warnings.\n";
