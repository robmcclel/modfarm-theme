<?php
/** Site defaults, persisted values and local overrides must resolve consistently. */
define('ABSPATH', __DIR__);
$settings = []; $filters = [];
function get_option($key, $default = []) { return $GLOBALS['settings']; }
function add_filter($name, $callback, ...$args) { $GLOBALS['filters'][$name] = $callback; }
function add_action(...$args) {}
require __DIR__ . '/../modfarm-theme/modfarm-theme/inc/layout-widths.php';
function check($result, $label) { if (!$result) throw new RuntimeException($label); echo "PASS $label\n"; }
check(modfarm_layout_widths() === ['contentSize'=>'1100px', 'wideSize'=>'1300px'], '1100/1300 defaults');
$settings = ['content_width'=>'800px', 'wide_width'=>'1250px'];
check(modfarm_layout_widths() === ['contentSize'=>'800px', 'wideSize'=>'1250px'], 'Saved site widths');
$theme_json = new class {
    public $data;
    public function update_with($data) { $this->data = $data; return $this; }
};
$filters['wp_theme_json_data_theme']($theme_json);
check($theme_json->data['settings']['layout'] === modfarm_layout_widths(), 'Native editor receives the saved site widths');
check(modfarm_group_layout_widths(['type'=>'constrained', 'contentSize'=>'800px']) === ['--mf-layout-content-width'=>'800px'], 'Narrowing text leaves wide width inherited');
check(modfarm_group_layout_widths(['type'=>'constrained', 'contentSize'=>'800px', 'wideSize'=>'1200px']) === ['--mf-layout-content-width'=>'800px', '--mf-layout-wide-width'=>'1200px'], 'Both local Group overrides');
check(modfarm_group_layout_widths(['type'=>'flex', 'contentSize'=>'800px']) === [], 'Rows keep their own layout');
foreach (['0px','-20px','red','1300px;color:red', [], ''] as $value) check(modfarm_sanitize_layout_width($value) === '', 'Reject invalid width: '.json_encode($value));
foreach (['800px','90%','65rem','80vw'] as $value) check(modfarm_sanitize_layout_width($value) === $value, 'Accept width: '.$value);
$settings = ['content_width'=>'', 'wide_width'=>'invalid'];
check(modfarm_layout_widths() === ['contentSize'=>'1100px', 'wideSize'=>'1300px'], 'Blank/invalid settings fall back safely');
