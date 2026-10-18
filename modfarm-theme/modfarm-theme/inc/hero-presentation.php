<?php
/** Shared Hero/Cover settings and rendering. Store semantic color keys, not hex snapshots. */

function modfarm_hero_presentation_options(): array {
    return [
        'presets' => [
            'none' => __('Clean edge', 'modfarm'),
            'border' => __('Border', 'modfarm'),
            'bottom-soft' => __('Short bottom fade', 'modfarm'),
            'bottom-deep' => __('Deep bottom fade', 'modfarm'),
            'both' => __('Top and bottom fade', 'modfarm'),
        ],
        'colors' => [
            'background' => __('Page background', 'modfarm'),
            'primary' => __('Primary', 'modfarm'),
            'secondary' => __('Secondary', 'modfarm'),
            'button' => __('Button', 'modfarm'),
            'button-text' => __('Button text', 'modfarm'),
            'heading' => __('Header text', 'modfarm'),
            'body' => __('Body text', 'modfarm'),
            'link' => __('Link', 'modfarm'),
        ],
    ];
}

function modfarm_hero_image_url($image): string {
    if (!is_scalar($image) || $image === '') {
        return '';
    }
    return is_numeric($image)
        ? (string) (wp_get_attachment_image_url(absint($image), 'full') ?: '')
        : esc_url_raw((string) $image);
}

/** Archives never use the first queried post as their image source. */
function modfarm_resolve_hero_image(array $attributes, $block = null): string {
    $settings = get_option('modfarm_theme_settings', []);
    $default = modfarm_hero_image_url($settings['hero_default_image'] ?? '');
    $archive = is_archive() || is_home() || is_search();
    $post_id = (int) ($block->context['postId'] ?? get_the_ID());
    if (!$archive && !empty($attributes['useFeaturedImage']) && $post_id) {
        $featured = get_the_post_thumbnail_url($post_id, 'full');
        if ($featured) {
            return (string) $featured;
        }
    }
    if (($attributes['mode'] ?? 'auto') === 'manual') {
        return modfarm_hero_image_url($attributes['manualUrl'] ?? '') ?: $default;
    }
    if (is_category() || is_tag() || is_tax()) {
        $term = get_queried_object();
        $local = !empty($term->term_id)
            ? get_term_meta((int) $term->term_id, $attributes['termMetaKey'] ?? 'archive_hero_image', true)
            : '';
        return modfarm_hero_image_url($local) ?: $default;
    }
    if (is_archive() || is_home() || is_search()) {
        $local = '';
        if (is_post_type_archive() && function_exists('modfarm_get_current_collection_archive_post_type') && function_exists('modfarm_get_collection_archive_media')) {
            $type = modfarm_get_current_collection_archive_post_type();
            if ($type) {
                $local = modfarm_get_collection_archive_media($type, 'hero', 'full');
            }
        }
        return modfarm_hero_image_url($local) ?: $default;
    }
    if (!$post_id) {
        return $default;
    }
    $local = modfarm_hero_image_url(get_post_meta($post_id, $attributes['bookMetaKey'] ?? 'hero_image', true));
    if ($local) {
        return $local;
    }
    // Compatibility for saved blocks that explicitly requested the former fallback.
    if (!empty($attributes['fallbackFeatured'])) {
        $featured = get_the_post_thumbnail_url($post_id, 'full');
        if ($featured) {
            return (string) $featured;
        }
    }
    return $default;
}

function modfarm_hero_transition(array $attributes): array {
    $settings = get_option('modfarm_theme_settings', []);
    $options = modfarm_hero_presentation_options();
    $preset = $attributes['edgePreset'] ?? 'site';
    $color = $attributes['edgeColor'] ?? 'site';
    $preset = $preset === 'site' ? ($settings['hero_edge_preset'] ?? 'none') : $preset;
    $color = $color === 'site' ? ($settings['hero_edge_color'] ?? 'background') : $color;
    $preset = isset($options['presets'][$preset]) ? $preset : 'none';
    $color = isset($options['colors'][$color]) ? $color : 'background';
    $tokens = [
        'background' => ['background_color', '--mfs-background', '#ffffff'],
        'primary' => ['primary_color', '--mfs-primary', '#336699'],
        'secondary' => ['secondary_color', '--mfs-secondary', '#444444'],
        'button' => ['button_color', '--mfs-button-bg', '#222222'],
        'button-text' => ['button_text_color', '--mfs-button-fg', '#ffffff'],
        'heading' => ['header_text_color', '--mfs-heading', '#111111'],
        'body' => ['body_text_color', '--mfs-body', '#1d2327'],
        'link' => ['link_color', '--mfs-link', '#0066cc'],
    ];
    [$key, $token, $fallback] = $tokens[$color];
    $hex = sanitize_hex_color($settings[$key] ?? '') ?: $fallback;
    return ['preset' => $preset, 'color' => $color, 'value' => "var($token,$hex)"];
}

function modfarm_hero_edge_markup(array $attributes): string {
    $edge = modfarm_hero_transition($attributes);
    if ($edge['preset'] === 'none') {
        return '';
    }
    return '<span aria-hidden="true" class="mf-hero-edge mf-hero-edge--' . esc_attr($edge['preset']) . '" style="--mf-hero-edge-color:' . esc_attr($edge['value']) . '"></span>';
}

/** Enhance core Cover at render time; never rewrite its saved HTML. */
add_filter('register_block_type_args', function ($args, $name) {
    if ($name === 'core/cover') {
        $args['attributes']['edgePreset'] = ['type' => 'string', 'default' => 'site'];
        $args['attributes']['edgeColor'] = ['type' => 'string', 'default' => 'site'];
        // Existing Covers keep their original image behavior until explicitly enabled.
        $args['attributes']['modfarmImageDefaults'] = ['type' => 'boolean', 'default' => false];
    }
    return $args;
}, 10, 2);

add_filter('render_block_core/cover', function ($html, $parsed, $block) {
    $attrs = $parsed['attrs'] ?? [];
    if (!empty($attrs['modfarmImageDefaults']) && ($attrs['backgroundType'] ?? 'image') === 'image') {
        $url = modfarm_resolve_hero_image([
            'mode' => empty($attrs['url']) ? 'auto' : 'manual',
            'manualUrl' => $attrs['url'] ?? '',
            'useFeaturedImage' => !empty($attrs['useFeaturedImage']),
        ], $block);
        if ($url) {
            $processor = new WP_HTML_Tag_Processor($html);
            $has_image = false;
            while ($processor->next_tag()) {
                if ($processor->has_class('wp-block-cover__image-background')) {
                    $has_image = true;
                    if ($processor->get_tag() === 'IMG') {
                        $processor->set_attribute('src', $url);
                        $processor->remove_attribute('srcset');
                        $processor->remove_attribute('sizes');
                    } else {
                        // Core uses a div background for fixed/parallax Covers.
                        $style = $processor->get_attribute('style') ?: '';
                        $style = preg_replace('/background-image\s*:[^;]*;?/i', '', $style);
                        $processor->set_attribute('style', $style . ';background-image:url("' . esc_url($url) . '");');
                    }
                    break;
                }
            }
            $html = $processor->get_updated_html();
            if (!$has_image) {
                $pos = strpos($html, '>');
                if ($pos !== false) {
                    $image = '<img class="wp-block-cover__image-background" alt="" src="' . esc_url($url) . '" data-object-fit="cover">';
                    $html = substr_replace($html, $image, $pos + 1, 0);
                }
            }
        }
    }
    $edge = modfarm_hero_edge_markup($attrs);
    if ($edge !== '') {
        $processor = new WP_HTML_Tag_Processor($html);
        if ($processor->next_tag(['class_name' => 'wp-block-cover'])) {
            $processor->add_class('mf-has-hero-edge');
            $html = $processor->get_updated_html();
            $pos = strpos($html, '>');
            $html = substr_replace($html, $edge, $pos + 1, 0);
        }
    }
    return $html;
}, 10, 3);

add_action('enqueue_block_assets', function () {
    $path = get_template_directory() . '/assets/css/hero-presentation.css';
    wp_enqueue_style('modfarm-hero-presentation', get_template_directory_uri() . '/assets/css/hero-presentation.css', [], filemtime($path));
});

add_action('enqueue_block_editor_assets', function () {
    $path = get_template_directory() . '/assets/js/hero-presentation.js';
    wp_enqueue_script('modfarm-hero-presentation', get_template_directory_uri() . '/assets/js/hero-presentation.js', ['wp-hooks', 'wp-compose', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-blocks', 'wp-i18n', 'wp-data', 'wp-core-data', 'wp-api-fetch'], filemtime($path), true);
    $settings = get_option('modfarm_theme_settings', []);
    wp_localize_script('modfarm-hero-presentation', 'modfarmHeroPresentation', [
        'options' => modfarm_hero_presentation_options(),
        'preset' => $settings['hero_edge_preset'] ?? 'none',
        'color' => $settings['hero_edge_color'] ?? 'background',
        'defaultImage' => modfarm_hero_image_url($settings['hero_default_image'] ?? ''),
        'settings' => array_intersect_key($settings, array_flip(['background_color', 'primary_color', 'secondary_color', 'button_color', 'button_text_color', 'header_text_color', 'body_text_color', 'link_color'])),
    ]);
});

/** Default Hero image media picker on the theme settings screen. */
add_action('admin_enqueue_scripts', function ($hook) {
    if (strpos($hook, 'modfarm') === false) {
        return;
    }
    wp_enqueue_media();
    $path = get_template_directory() . '/assets/js/hero-settings-media.js';
    wp_enqueue_script('modfarm-hero-settings-media', get_template_directory_uri() . '/assets/js/hero-settings-media.js', [], filemtime($path), true);
});

function modfarm_hero_settings_fields(): void {
    $settings = get_option('modfarm_theme_settings', []);
    $image = $settings['hero_default_image'] ?? '';
    $url = modfarm_hero_image_url($image);
    ?>
    <div class="mf-settings-group">
        <h3 class="mf-group-title"><?php esc_html_e('Hero & Cover defaults', 'modfarm'); ?></h3>
        <p class="description"><?php esc_html_e('Used when no local hero image is available. Featured images override the hero only when enabled on the block. Cover image defaults are opt-in.', 'modfarm'); ?></p>
        <table class="form-table mf-form-table"><tbody>
            <tr><th><label for="mf-hero-default-image"><?php esc_html_e('Default image', 'modfarm'); ?></label></th><td>
                <input type="hidden" id="mf-hero-default-image" name="modfarm_theme_settings[hero_default_image]" value="<?php echo esc_attr($image); ?>">
                <img id="mf-hero-default-preview" src="<?php echo esc_url($url); ?>" alt="" style="max-width:240px;<?php echo $url ? '' : 'display:none;'; ?>">
                <p><button type="button" class="button mf-hero-image-select"><?php esc_html_e('Select image', 'modfarm'); ?></button> <button type="button" class="button mf-hero-image-clear"><?php esc_html_e('Clear', 'modfarm'); ?></button></p>
            </td></tr>
            <?php foreach (modfarm_hero_presentation_options() as $kind => $options) :
                $key = $kind === 'presets' ? 'hero_edge_preset' : 'hero_edge_color';
                $value = $settings[$key] ?? ($kind === 'presets' ? 'none' : 'background'); ?>
                <tr><th><label for="<?php echo esc_attr($key); ?>"><?php echo $kind === 'presets' ? esc_html__('Gradation preset', 'modfarm') : esc_html__('Fade / border color', 'modfarm'); ?></label></th><td>
                    <select id="<?php echo esc_attr($key); ?>" name="modfarm_theme_settings[<?php echo esc_attr($key); ?>]">
                    <?php foreach ($options as $option => $label) : ?>
                        <option value="<?php echo esc_attr($option); ?>" <?php selected($value, $option); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                    </select>
                </td></tr>
            <?php endforeach; ?>
        </tbody></table>
    </div>
    <?php
}
