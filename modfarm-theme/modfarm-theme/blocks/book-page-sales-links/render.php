<?php

function modfarm_render_book_page_sales_links_block($attributes, $content, $block) {
    $manual_source = ($attributes['sourceMode'] ?? 'current') === 'manual';
    $post_id = $manual_source
        ? absint($attributes['bookId'] ?? 0)
        : absint($block->context['postId'] ?? get_the_ID());
    if (!$post_id || get_post_type($post_id) !== 'book') {
        return '';
    }
    if ($manual_source && get_post_status($post_id) !== 'publish' && !current_user_can('read_post', $post_id)) {
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

    // Several formats share a retailer logo. Missing artwork remains a text link.
    $monochrome_icons = [
        'kindle_url' => 'kindle',
        'amazon_paper' => 'amazon', 'amazon_hard' => 'amazon', 'amazon_audio' => 'amazon',
        'audible_url' => 'audible', 'nook' => 'nook',
        'barnes_paper' => 'barnes', 'barnes_hard' => 'barnes', 'barnes_audio' => 'nook-audio',
        'ibooks' => 'ibooks', 'itunes' => 'itunes',
        'kobo' => 'kobo', 'kobo_audio' => 'kobo',
        'googleplay' => 'google-play', 'googleplay_audio' => 'google-play',
        'bookshop_ebook' => 'bookshop', 'bookshop_paper' => 'bookshop', 'bookshop_hard' => 'bookshop',
        'bam_paper' => 'bam', 'bam_hard' => 'bam',
        'indigo' => 'indigo', 'waterstones' => 'waterstones',
    ];
    $retailer_names = [
        'kindle_url' => 'Kindle', 'amazon_paper' => 'Amazon Paperback',
        'amazon_hard' => 'Amazon Hardcover', 'amazon_audio' => 'Amazon Audio',
        'audible_url' => 'Audible', 'nook' => 'B&N Nook',
        'barnes_paper' => 'B&N Paperback', 'barnes_hard' => 'B&N Hardcover', 'barnes_audio' => 'B&N Audio',
        'ibooks' => 'Apple Books', 'itunes' => 'iTunes', 'kobo' => 'Kobo eBook', 'kobo_audio' => 'Kobo Audio',
        'googleplay' => 'Google Play eBook', 'googleplay_audio' => 'Google Play Audio',
        'bookshop_ebook' => 'Bookshop eBook', 'bookshop_paper' => 'Bookshop Paperback', 'bookshop_hard' => 'Bookshop Hardcover',
        'bam_paper' => 'Books-A-Million Paperback', 'bam_hard' => 'Books-A-Million Hardcover',
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
    $monochrome  = ($attributes['colorMode'] ?? 'native') === 'monotone';
    $backgroundColor = trim((string)($attributes['monotoneColor'] ?? ''));
    $buttonPath  = rtrim((string)($attributes['buttonPath'] ?? ''), '/') . '/';

    // Fallback to default icon path in theme
    $default_path = trailingslashit(get_template_directory_uri()) . 'blocks/book-page-sales-links/images/';

    // ===== Begin output =====
    ob_start();

    $wrapper_classes = [
        'mf-sales-links',
        'mfsales',
        'mfsales--align-' . $linksAlign,
        $monochrome ? 'mfsales--monotone' : 'mfsales--native',
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
        echo '</div>';
        return ob_get_clean();
    }

    echo '<div class="mf-retailer-icon-row mfsales__row">';

    foreach ($buttons as $btn) {
        $accessible_label = $retailer_names[$btn['key']] ?? $btn['label'];
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
            . ' aria-label="' . esc_attr($accessible_label) . '"'
            . ' title="' . esc_attr($accessible_label) . '">';

        if ($monochrome) {
            $icon_name = $monochrome_icons[$btn['key']] ?? $btn['key'];
            $icon_relative = 'blocks/book-page-sales-links/cbg-images/' . $icon_name . '.png';
            // currentColor paints the tile; the logo mask stays white in CSS.
            $color_style = $backgroundColor !== '' ? safecss_filter_attr('color:' . $backgroundColor) : '';
            if (file_exists(trailingslashit(get_template_directory()) . $icon_relative)) {
                $mask_url = esc_url(trailingslashit(get_template_directory_uri()) . $icon_relative);
                $icon_style = '--mfsales-mask:url(' . wp_json_encode($mask_url) . ');width:' . $size . 'px;height:' . $size . 'px;border-radius:' . max(0, $radius) . 'px;' . $color_style;
                echo '<span class="mfsales__monochrome-icon" aria-hidden="true" style="' . esc_attr($icon_style) . '"></span>';
            } else {
                echo '<span class="mfsales__text-icon" style="' . esc_attr($color_style) . '">' . esc_html($accessible_label) . '</span>';
                $label_html = ''; // The fallback already identifies the retailer.
            }
        } else {
            echo '<img src="' . esc_url($final_icon_url) . '" alt="' . esc_attr($accessible_label) . '" style="width:' . (int)$size . 'px; height:' . (int)$size . 'px; border-radius:' . (int)$radius . 'px;" />';
        }
        echo $label_html;
        echo '</a>';
    }

    echo '</div>'; // row
    echo '</div>'; // wrapper

    return ob_get_clean();
}
