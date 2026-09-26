<?php
/** Lightweight Google tag validation/output tests. Run: php tests/google-tag.php */

$source = file_get_contents(dirname(__DIR__) . '/modfarm-theme/modfarm-theme/inc/modfarm-settings.php');

function extract_function(string $source, string $name): string {
    $start = strpos($source, 'function ' . $name . '(');
    if ($start === false) {
        throw new RuntimeException("Missing {$name}");
    }
    $brace = strpos($source, '{', $start);
    $depth = 0;
    $length = strlen($source);
    for ($position = $brace; $position < $length; $position++) {
        if ($source[$position] === '{') $depth++;
        if ($source[$position] === '}') $depth--;
        if ($depth === 0) return substr($source, $start, $position - $start + 1);
    }
    throw new RuntimeException("Unterminated {$name}");
}

eval(extract_function($source, 'modfarm_normalize_google_tag_id'));

function is_admin(): bool { return false; }
function get_option($key, $default = []) { return $GLOBALS['settings'] ?? $default; }
function esc_attr($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function wp_json_encode($value): string { return json_encode($value, JSON_UNESCAPED_SLASHES); }
eval(extract_function($source, 'modfarm_output_google_tag'));

function assert_true($condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}

assert_true(modfarm_normalize_google_tag_id(' aw-18474709286 ') === 'AW-18474709286', 'Google Ads ID normalizes');
assert_true(modfarm_normalize_google_tag_id('G-ABC123XYZ') === 'G-ABC123XYZ', 'Analytics ID is accepted');
assert_true(modfarm_normalize_google_tag_id('GTM-ABC123') === '', 'Tag Manager containers are rejected');
assert_true(modfarm_normalize_google_tag_id('<script>alert(1)</script>') === '', 'Script input is rejected');

$GLOBALS['settings'] = ['google_tag_id' => 'AW-18474709286'];
ob_start();
modfarm_output_google_tag();
$output = ob_get_clean();
assert_true(substr_count($output, 'gtag/js?id=AW-18474709286') === 1, 'Loader is emitted once');
assert_true(substr_count($output, "gtag('config', \"AW-18474709286\")") === 1, 'Config is emitted once');
assert_true(strpos($output, '&#') === false, 'No HTML entities corrupt the script or URL');

$GLOBALS['settings'] = ['google_tag_id' => 'not-valid'];
ob_start();
modfarm_output_google_tag();
assert_true(ob_get_clean() === '', 'Invalid IDs emit no markup');

echo "Google tag tests passed.\n";
