<?php

function modfarm_render_book_page_sales_links_block($attributes, $content, $block) {
    $post_id = get_the_ID();
    if (get_post_type($post_id) !== 'book') {
        return '';
    }

    $retailers = [
        'kindle_url'       => 'Kindle',
        'amazon_paper'     => 'Paperback',
        'amazon_hard'      => 'Hardcover',
        'amazon_audio'     => 'Audio',
        'audible_url'      => 'Audible',
        'nook'             => 'Nook',
        'barnes_paper'     => 'Paperback',
        'barnes_hard'      => 'Hardcover',
        'barnes_audio'     => 'Audio',
        'ibooks'           => 'iBooks',
        'itunes'           => 'iTunes',
        'kobo'             => 'eBook',
        'kobo_audio'       => 'Audio',
        'googleplay'       => 'eBook',
        'googleplay_audio' => 'Audio',
        'bookshop_ebook'   => 'eBook',
        'bookshop_paper'   => 'Paperback',
        'bookshop_hard'    => 'Hardcover',
        'bam_paper'        => 'Paperback',
        'bam_hard'         => 'Hardcover',
        'indigo'           => 'Indigo',
        'waterstones'      => 'Waterstones',
        'brokenbinding'    => 'Broken Binding',
        'librofm'          => 'Libro.fm',
        'downpour'         => 'Downpour',
        'target'           => 'Target',
        'walmart'          => 'Walmart',
        'audiobooks_com'   => 'Audiobooks',
        'spotify'          => 'Spotify',
    ];

    // ===== Attributes =====
    $introText   = $attributes['introText']   ?? '';
    $textColor   = trim($attributes['textColor'] ?? '');
    $fontWeight  = (int)($attributes['fontWeight'] ?? 600);
    $fontSize    = (int)($attributes['fontSize']   ?? 18);

    // Alignment (fallback to legacy `buttonAlign`, default center)
    $linksAlign  = $attributes['linksAlign']  ?? ($attributes['buttonAlign'] ?? 'center');
    $linksAlign  = in_array($linksAlign, ['left','center','right'], true) ? $linksAlign : 'center';

    $buttonSize  = (int)($attributes['buttonSize'] ?? 50);
    $radius      = (int)($attributes['borderRadius'] ?? 4);
    $autoDetect  = !empty($attributes['autoDetect']);
    $showLabels  = !empty($attributes['showLabels']);
    $buttonPath  = rtrim((string)($attributes['buttonPath'] ?? ''), '/') . '/';

    // Fallback to default icon path in theme
    $default_path = trailingslashit(get_template_directory_uri()) . 'blocks/book-page-sales-links/images/';

    // ===== Begin output =====
    ob_start();

    $wrapper_classes = [
        'mf-sales-links',
        'mfsales',
        'mfsales--align-' . $linksAlign,
    ];

    printf(
        '<div class="%s" style="text-align:%s;">',
        esc_attr(implode(' ', $wrapper_classes)),
        esc_attr($linksAlign)
    );

    if ($introText !== '') {
        $intro_styles = [];
        if ($textColor !== '') {
            $intro_styles[] = 'color:' . esc_attr($textColor);
        }
        $intro_styles[] = 'font-size:' . (int)$fontSize . 'px';
        $intro_styles[] = 'font-weight:' . (int)$fontWeight;
        $intro_styles[] = 'text-align:' . esc_attr($linksAlign);

        printf(
            '<div class="intro" style="%s">%s</div>',
            esc_attr(implode('; ', $intro_styles)),
            esc_html($introText)
        );
    }

    // ===== Build button list =====
    $buttons = [];

    if ($autoDetect) {
        foreach ($retailers as $meta_key => $label) {
            $url = trim((string)get_post_meta($post_id, $meta_key, true));
            if ($url !== '') {
                $buttons[] = [
                    'url'   => $url,        // raw
                    'label' => $label,
                    'key'   => $meta_key,
                ];
            }
        }
    } else {
        for ($i = 1; $i <= 6; $i++) {
            $key = (string)($attributes["retailer{$i}"] ?? '');
            if ($key && isset($retailers[$key])) {
                $url = trim((string)get_post_meta($post_id, $key, true));
                if ($url !== '') {
                    $buttons[] = [
                        'url'   => $url,      // raw
                        'label' => $retailers[$key],
                        'key'   => $key,
                    ];
                }
            }
        }
    }

    if (empty($buttons)) {
        echo '<div></div>';
        return ob_get_clean();
    }

    echo '<div class="mf-retailer-icon-row mfsales__row">';

    foreach ($buttons as $btn) {
        $icon_filename    = $btn['key'] . '.jpg';
        $custom_icon_url  = $buttonPath . $icon_filename;

        $custom_icon_path = '';
        $parsed           = wp_parse_url($custom_icon_url);
        if (!empty($parsed['path'])) {
            $custom_icon_path = ABSPATH . ltrim($parsed['path'], '/');
        }

        $default_icon_url = $default_path . $icon_filename;
        $final_icon_url   = ( $custom_icon_path && file_exists($custom_icon_path) )
            ? $custom_icon_url
            : $default_icon_url;

        $label_html = '';
        if ($showLabels) {
            $style = ($textColor !== '') ? ' style="color:' . esc_attr($textColor) . ';"' : '';
            $label_html = '<span class="retailer-label"' . $style . '>' . esc_html($btn['label']) . '</span>';
        }

        $size = max(16, $buttonSize);

        // --- URLs ---
        $meta_key      = isset($btn['key']) ? (string)$btn['key'] : '';
        $destination   = isset($btn['url']) ? (string)$btn['url'] : '';   // raw destination
        $href          = $destination;                                    // what we actually link to
        $smart_wrapped = 0;

        if ($destination !== '' && function_exists('mfc_smartlinks_wrap_url')) {
            $maybe = mfc_smartlinks_wrap_url($destination, $meta_key);
            if (is_string($maybe) && $maybe !== '' && $maybe !== $destination) {
                $href = $maybe;
                $smart_wrapped = 1;
            }
        }

        // --- Event payload (for ModFarm Core click tracking) ---
        // This is what mfc-events.js reads from data-mf-event
        $event_payload = [
            'event_type'     => 'click',
            'event_category' => 'book_sales',
            'origin'         => 'book_page_sales_links',
            'book_id'        => $post_id,
            'meta_key'       => $meta_key,
            'label'          => (string)($btn['label'] ?? ''),
            'smartlinks'     => $smart_wrapped ? 'genius_quickbuild' : 'none',
            // Leave clicked_href/destination_url for JS to fill from data attributes
        ];

        $data_mf_event = esc_attr(wp_json_encode($event_payload));

        echo '<a class="retailer-square-button"'
            . ' data-mf-event="' . $data_mf_event . '"'
            . ' data-mf-href="' . esc_attr($href) . '"'
            . ' data-mf-destination="' . esc_attr($destination) . '"'
            . ' href="' . esc_url($href) . '"'
            . ' target="_blank" rel="noopener noreferrer"'
            . ' title="' . esc_attr($btn['label']) . '">';

        echo '<img src="' . esc_url($final_icon_url) . '" alt="' . esc_attr($btn['label']) . '" style="width:' . (int)$size . 'px; height:' . (int)$size . 'px; border-radius:' . (int)$radius . 'px;" />';
        echo $label_html;
        echo '</a>';
    }

    echo '</div>'; // row
    echo '</div>'; // wrapper

    return ob_get_clean();
}