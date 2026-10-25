<?php
/** Standalone preview assets must include plugin styles without consuming admin assets. */
define('ABSPATH', __DIR__);
class PreviewTestStyles {
    public $queue = ['admin-toolbar'];
    public $done = ['admin-toolbar', 'reader-base'];
    public $to_do = ['admin-toolbar'];
    public $do_concat = true;
    public $concat = 'admin';
    public $concat_version = '1';
    public $print_html = 'admin html';
    public $print_code = 'admin css';
    public $registered = [
        'admin-toolbar' => ['src' => '/admin.css', 'deps' => []],
        'reader-base' => ['src' => '/reader.css?ver=2', 'deps' => [], 'after' => '.reader{max-width:800px}'],
        'reader-stage' => ['src' => '/reader-stage.css?ver=3', 'deps' => ['reader-base']],
        'series' => ['src' => '/series.css?ver=4', 'deps' => ['reader-base']],
    ];
    public function do_items($handles) {
        if ($this->do_concat || $this->concat || $this->print_html || $this->print_code) throw new RuntimeException('Admin concatenation leaked');
        foreach ($handles as $handle) $this->print_style($handle);
    }
    private function print_style($handle) {
        if (in_array($handle, $this->done, true)) return;
        $style = $this->registered[$handle];
        foreach ($style['deps'] as $dep) $this->print_style($dep);
        echo '<link rel="stylesheet" href="'.$style['src'].'">';
        if (isset($style['after'])) echo '<style>'.$style['after'].'</style>';
        $this->done[] = $handle;
    }
}
$styles = new PreviewTestStyles();
function wp_styles() { return $GLOBALS['styles']; }
function check($condition, $label) { if (!$condition) throw new RuntimeException($label); echo "PASS: $label\n"; }
require __DIR__.'/../modfarm-theme/modfarm-theme/inc/ppb-visualizer-assets.php';
$before = serialize($styles);
$episode = modfarm_ppb_visualizer_capture_styles(function () {
    wp_styles()->queue[] = 'reader-stage';
    return '<section class="reader-stage">Episode</section>';
});
check($episode['content'] === '<section class="reader-stage">Episode</section>', 'Episode markup is retained');
check(str_contains($episode['styles'], '/reader.css?ver=2') && str_contains($episode['styles'], '/reader-stage.css?ver=3'), 'Reader style and dependency are printed with versions');
check(strpos($episode['styles'], '/reader.css') < strpos($episode['styles'], '/reader-stage.css'), 'Base styles precede stage styles');
check(str_contains($episode['styles'], '.reader{max-width:800px}'), 'Inline plugin CSS is retained');
check(!str_contains($episode['styles'], '/admin.css'), 'Admin styles stay outside the preview');
check(serialize($styles) === $before, 'Preview leaves the admin queue and printer state unchanged');
$series = modfarm_ppb_visualizer_capture_styles(function () {
    wp_styles()->queue[] = 'series';
    return 'Series';
});
check(str_contains($series['styles'], '/reader.css?ver=2') && str_contains($series['styles'], '/series.css?ver=4'), 'Series preview has its own plugin styles and dependencies');
check(!str_contains($series['styles'], '/reader-stage.css'), 'Repeated previews do not inherit prior block styles');
check(serialize($styles) === $before, 'Repeated previews leave the admin styles untouched');
try {
    modfarm_ppb_visualizer_capture_styles(function () {
        wp_styles()->queue[] = 'series';
        throw new RuntimeException('Render failed');
    });
    throw new LogicException('Expected render failure');
} catch (RuntimeException $error) {
    check($error->getMessage() === 'Render failed', 'Render failures propagate');
}
check(serialize($styles) === $before, 'Render failures restore the admin queue');
