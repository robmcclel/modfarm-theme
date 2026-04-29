<?php
/**
 * Server render for Multi-Tax Block
 * Queries books and builds a $card payload for the unified book card template.
 *
 * Updated:
 * - Adds button.meta_key so ui.php can SmartLinks-wrap (Genius Quick Build Proxy)
 * - Adds card.asin so events can be book-aware (matches Handpicked/Coming Soon fixes)
 * - Resolves external target to _blank when linking to meta key (unless explicitly overridden)
 * - Aligns audio keys with ui.php (audio_sample_url, audio_player_embed, etc.)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'modfarm_render_multi_tax_format_block' ) ) :
function modfarm_render_multi_tax_format_block( $attributes ) {

    // --------------------------------------------------
    // 1. BASIC ATTRIBUTES (query + content)
    // --------------------------------------------------
    $tax_type        = $attributes['tax-type']        ?? '';
    $order_setting   = $attributes['display-order']   ?? 'DESC';
    $books_per_page  = (int) ( $attributes['books-per-page'] ?? 12 );
    $books_per_row   = $attributes['books-in-row']    ?? '25%';
    $image_type      = $attributes['image-type']      ?? 'featured';
    $show_pagination = ! empty( $attributes['show-pagination'] );

    // Primary CTA
    $btn_text   = $attributes['button-text']      ?? __( 'See The Book', 'modfarm' );
    $btn_link   = $attributes['button-link']      ?? 'bookpage'; // 'bookpage' or meta key
    $btn_target = $attributes['button-target']    ?? '_self';
    $tracker    = $attributes['tracker-loc']      ?? '';
    $volume_txt = $attributes['volume-text']      ?? 'Book';

    // Sample button label (kept for legacy compatibility)
    $sample_btn_text = $attributes['samplebtn-text'] ?? __( 'Play Sample', 'modfarm' );

    // --------------------------------------------------
    // 2. GLOBAL vs LOCAL CARD SETTINGS
    // --------------------------------------------------
    $opts            = get_option( 'modfarm_theme_settings', [] );
    $card_use_global = array_key_exists( 'cardUseGlobal', $attributes )
        ? (bool) $attributes['cardUseGlobal']
        : true;

    $pick_token = function( $local, $allowed, $global, $default ) {
        $local  = (string) $local;
        $global = (string) $global;

        if ( $local && $local !== 'inherit' && in_array( $local, $allowed, true ) ) return $local;
        if ( $global && in_array( $global, $allowed, true ) ) return $global;
        return $default;
    };

    // ---- Shapes ----
    $global_cover_shape  = $opts['book_card_cover_shape']   ?? '';
    $global_button_shape = $opts['book_card_button_shape']  ?? '';
    $global_sample_shape = $opts['book_card_sample_shape']  ?? '';

    $local_cover_shape   = $card_use_global ? 'inherit' : ( $attributes['cardCoverShape']  ?? 'inherit' );
    $local_button_shape  = $card_use_global ? 'inherit' : ( $attributes['cardButtonShape'] ?? 'inherit' );
    $local_sample_shape  = $card_use_global ? 'inherit' : ( $attributes['cardSampleShape'] ?? 'inherit' );

    $cover_shape  = $pick_token( $local_cover_shape,  ['square','rounded'], $global_cover_shape,  'square' );
    $button_shape = $pick_token( $local_button_shape, ['square','rounded','pill'], $global_button_shape, 'square' );
    $sample_shape = $pick_token( $local_sample_shape, ['square','rounded','pill'], $global_sample_shape, 'square' );

    // ---- CTA spacing ----
    $global_cta_mode = $opts['book_card_cta_mode'] ?? '';
    $local_cta_mode  = $card_use_global ? 'inherit' : ( $attributes['cardCtaMode'] ?? 'inherit' );
    $cta_mode        = $pick_token( $local_cta_mode, ['joined','gap'], $global_cta_mode, 'joined' );

    // ---- Shadow ----
    $global_shadow = $opts['book_card_shadow_style'] ?? '';
    $local_shadow  = $card_use_global ? 'inherit' : ( $attributes['cardShadowStyle'] ?? 'inherit' );
    $effect        = $pick_token(
        $local_shadow,
        ['flat','shadow-sm','shadow-md','shadow-lg','emboss'],
        $global_shadow,
        'flat'
    );

    // --------------------------------------------------
    // 3. VISIBILITY (title / series / buttons / sample)
    // --------------------------------------------------
    if ( $card_use_global ) {
        $show_title         = true;
        $show_series        = true;
        $show_primary_btn   = true;
        $show_sample_btn    = true;
    } else {
        $show_title         = ! empty( $attributes['cardShowTitle'] );
        $show_series        = ! empty( $attributes['cardShowSeries'] );
        $show_primary_btn   = ! empty( $attributes['cardShowPrimaryButton'] );
        $show_sample_btn    = ! empty( $attributes['cardShowSampleButton'] );
    }

    // Audio mode (local or forced off)
    $audio_mode = $show_sample_btn ? ( $attributes['audio-mode'] ?? 'auto' ) : 'off';

    // --------------------------------------------------
    // 4. PER-CARD COLOR OVERRIDES
    // --------------------------------------------------
    $card_btn_bg    = $attributes['cardButtonBg']   ?? '';
    $card_btn_fg    = $attributes['cardButtonFg']   ?? '';
    $card_sample_bg = $attributes['cardSampleBg']   ?? '';
    $card_sample_fg = $attributes['cardSampleFg']   ?? '';

    $custom_vars = [];

    if ( $card_btn_bg ) {
        $custom_vars[] = '--mfb-btn-bg:' . $card_btn_bg;
        $custom_vars[] = '--mfb-btn-border:' . $card_btn_bg;
    }
    if ( $card_btn_fg ) {
        $custom_vars[] = '--mfb-btn-fg:' . $card_btn_fg;
    }
    if ( $card_sample_bg ) {
        $custom_vars[] = '--mfb-sample-bg:' . $card_sample_bg;
        $custom_vars[] = '--mfb-sample-border:' . $card_sample_bg;
    }
    if ( $card_sample_fg ) {
        $custom_vars[] = '--mfb-sample-fg:' . $card_sample_fg;
    }

    // CTA spacing
    $custom_vars[] = ($cta_mode === 'gap') ? '--mfb-cta-gap:10px' : '--mfb-cta-gap:0px';

    // Button shape radius
    if ( $button_shape === 'square' )        $custom_vars[] = '--mfb-button-radius:0px';
    elseif ( $button_shape === 'rounded' )   $custom_vars[] = '--mfb-button-radius:16px';
    elseif ( $button_shape === 'pill' )      $custom_vars[] = '--mfb-button-radius:9999px';

    // Sample shape radius
    if ( $sample_shape === 'square' )        $custom_vars[] = '--mfb-sample-radius:0px';
    elseif ( $sample_shape === 'rounded' )   $custom_vars[] = '--mfb-sample-radius:16px';
    elseif ( $sample_shape === 'pill' )      $custom_vars[] = '--mfb-sample-radius:9999px';

    // Local visibility override for sample/audio button
    $custom_vars[] = $show_sample_btn ? '--mfb-audio-cta-display:inline-flex' : '--mfb-audio-cta-display:none';

    // --------------------------------------------------
    // 5. TAX FILTERS
    // --------------------------------------------------
    $term_map = [
        'series'   => [ 'attr' => 'series-select',     'taxonomy' => 'book-series' ],
        'genre'    => [ 'attr' => 'genre-select',      'taxonomy' => 'book-genre' ],
        'author'   => [ 'attr' => 'bookauthor-select', 'taxonomy' => 'book-author' ],
        'language' => [ 'attr' => 'language-select',   'taxonomy' => 'book-language' ],
        'booktag'  => [ 'attr' => 'booktag-select',    'taxonomy' => 'book-tags' ],
    ];

    $tax_query = [];
    if ( ! empty( $term_map[ $tax_type ] ) ) {
        $attr_key = $term_map[ $tax_type ]['attr'];
        $tax_slug = $term_map[ $tax_type ]['taxonomy'];
        $term_id  = ! empty( $attributes[ $attr_key ]['id'] ) ? (int) $attributes[ $attr_key ]['id'] : 0;

        if ( $term_id ) {
            $tax_query[] = [
                'taxonomy' => $tax_slug,
                'field'    => 'term_id',
                'terms'    => $term_id,
            ];
        }
    }

    // book-format filter
    $format_term = ( isset( $attributes['book-format']['id'] ) && $attributes['book-format']['id'] )
        ? (int) $attributes['book-format']['id']
        : null;

    if ( $format_term ) {
        $tax_query[] = [
            'taxonomy' => 'book-format',
            'field'    => 'term_id',
            'terms'    => $format_term,
        ];
    }

    // --------------------------------------------------
    // 6. QUERY
    // --------------------------------------------------
    $paged   = max( 1, get_query_var( 'paged' ) ?: get_query_var( 'page' ) ?: 1 );
    $orderby = ( $order_setting === 'rand' ) ? 'rand' : 'meta_value';
    $order   = ( $order_setting === 'ASC' )  ? 'ASC'  : 'DESC';

    $args = [
        'post_type'      => 'book',
        'posts_per_page' => $books_per_page,
        'paged'          => $paged,
        'tax_query'      => $tax_query,
    ];

    if ( $orderby === 'rand' ) {
        $args['orderby'] = 'rand';
    } else {
        $args['orderby']   = 'meta_value';
        $args['meta_key']  = 'publication_date';
        $args['meta_type'] = 'DATE';
        $args['order']     = $order;
    }

    $query = new WP_Query( $args );

    // --------------------------------------------------
    // 7. WRAPPER CLASSES
    // --------------------------------------------------
    $wrapper_classes = [
        'mfb-wrapper',
        'is-multitax',
        'mfb-effect--' . sanitize_html_class( $effect ),
        'mfb-cover--'  . sanitize_html_class( $cover_shape ),
        'mfb-button--' . sanitize_html_class( $button_shape ),
        'mfb-sample--' . sanitize_html_class( $sample_shape ),
        'mfb-cta--'    . sanitize_html_class( $cta_mode ),
    ];

    // Columns
    $pct  = floatval( str_replace( '%', '', $books_per_row ) );
    $cols = ( $pct > 0 ) ? max( 1, (int) round( 100 / $pct ) ) : 4;
    $grid_style = '--mfb-cols:' . (int) $cols . ';';

    $wrapper_style = $grid_style;
    if ( ! empty( $custom_vars ) ) {
        $wrapper_style .= implode( ';', $custom_vars ) . ';';
    }

    // --------------------------------------------------
    // 8. OUTPUT GRID
    // --------------------------------------------------
    ob_start();

    echo '<div class="' . esc_attr( implode( ' ', array_filter( $wrapper_classes ) ) ) . '">';
    echo '<div class="mfb-grid" style="' . esc_attr( $wrapper_style ) . '">';

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();

            $book_id   = get_the_ID();
            $permalink = get_permalink( $book_id );

            // Book identity (for events + SmartLinks helpers downstream)
            $asin = (string) get_post_meta( $book_id, 'asin_kindle', true );

            // ============================================================
            // Primary button URL + meta_key (for SmartLinks in ui.php)
            $btn_meta_key = 'permalink';
            $button_url   = $permalink;

            if ( $show_primary_btn && $btn_link && $btn_link !== 'bookpage' ) {
                $btn_meta_key = (string) $btn_link;
                $custom = (string) get_post_meta( $book_id, $btn_meta_key, true );
                if ( $custom !== '' ) {
                    $button_url = $custom;
                } else {
                    $btn_meta_key = 'permalink';
                    $button_url   = $permalink;
                }
            }

            // Prefer _blank for external unless explicitly overridden
            $resolved_target = $btn_target;
            if ( $btn_meta_key !== 'permalink' && ( $resolved_target === '' || $resolved_target === '_self' ) ) {
                $resolved_target = '_blank';
            }
            // ============================================================

            // Image
            $img_url = '';
            if ( $image_type === 'featured' ) {
                $img_url = get_the_post_thumbnail_url( $book_id, 'full' ) ?: '';
            } else {
                $img_id = get_post_meta( $book_id, $image_type, true );
                if ( $img_id ) {
                    $img_url = is_numeric( $img_id )
                        ? wp_get_attachment_image_url( $img_id, 'full' )
                        : (string) $img_id;
                }
            }

            if ( ! $img_url ) {
                $img_url = get_the_post_thumbnail_url( $book_id, 'full' ) ?: '';
            }

            if ( ! $img_url ) {
                $fallback_id = get_post_meta( $book_id, 'cover_ebook', true );
                if ( $fallback_id ) {
                    $img_url = is_numeric( $fallback_id )
                        ? wp_get_attachment_image_url( $fallback_id, 'full' )
                        : (string) $fallback_id;
                }
            }

            // Aspect ratio
            $aspect = '2 / 3';
            switch ( $image_type ) {
                case 'cover_image_audio':      $aspect = '1 / 1'; break;
                case 'cover_image_3d':         $aspect = '4 / 3'; break;
                case 'cover_image_composite':
                case 'hero_image':             $aspect = '16 / 9'; break;
            }

            // Series
            $series_terms = get_the_terms( $book_id, 'book-series' );
            $series_name  = ( ! empty( $series_terms ) && ! is_wp_error( $series_terms ) ) ? $series_terms[0]->name : '';
            $series_pos   = (string) get_post_meta( $book_id, 'series_position', true );

            // --------------------------------------------------
            // AUDIO — aligned with ui.php keys
            // --------------------------------------------------
            $audio_embed  = (string) get_post_meta( $book_id, 'audio_player_embed', true );
            $audio_sample = (string) get_post_meta( $book_id, 'audio_sample_url',   true );
            $audio_date   =        get_post_meta( $book_id, 'audiobook_publication_date', true ) ?: null;
            $audible_asin = (string) get_post_meta( $book_id, 'audible_asin',       true );
            $amazon_asin  = $asin;

            // If audio is enabled, but no sources exist → force off
            if ( $audio_mode !== 'off' ) {
                $has_sample    = ( $audio_sample !== '' );
                $has_construct = ( $audible_asin !== '' || $amazon_asin !== '' );
                $card_audio_mode = ( ! $has_sample && ! $has_construct ) ? 'off' : $audio_mode;
            } else {
                $card_audio_mode = 'off';
            }

            // --------------------------------------------------
            // BUILD CARD
            // --------------------------------------------------
            $card = [
                'id'        => $book_id,
                'title'     => get_the_title(),
                'permalink' => $permalink,
                'image_url' => $img_url,
                'aspect'    => $aspect,
                'format'    => null,

                // NEW: helps ui.php include book identity in events
                'asin'      => $asin,

                'show_title'      => $show_title,
                'series_name'     => $show_series ? $series_name : '',
                'series_position' => $show_series ? $series_pos  : '',
                'volume_text'     => $volume_txt,

                // ---- Audio (ui.php expects these names) ----
                'audio_mode'                 => $card_audio_mode,
                'audio_player_embed'         => $audio_embed,
                'audio_sample_url'           => $audio_sample,
                'audible_asin'               => $audible_asin,
                'amazon_asin'                => $amazon_asin,
                'audiobook_publication_date' => $audio_date,

                // Legacy / compatibility keys (harmless if present)
                'audio_embed'   => $audio_embed,
                'audio_sample'  => $audio_sample,
                'audio_date'    => $audio_date,

                'sample_button_text' => $sample_btn_text,

                // ---- Buttons ----
                'button' => $show_primary_btn ? [
                    'text'     => $btn_text,
                    'url'      => $button_url,
                    'target'   => $resolved_target,
                    'bg'       => $card_btn_bg,
                    'fg'       => $card_btn_fg,
                    'tracker'  => $tracker,
                    'origin'   => 'multi-tax',

                    // CRITICAL: enables Genius wrapping in ui.php
                    'meta_key' => $btn_meta_key,
                ] : [
                    'text'   => '',
                    'url'    => '',
                    'origin' => 'multi-tax',
                ],
            ];

            echo '<div class="mfb-item">';

            if ( function_exists( 'modfarm_render_book_card' ) ) {
                modfarm_render_book_card( $card );
            } else {
                echo '<article class="mfb-card"><div class="mfb-media">';
                echo '<a class="mfb-image" href="' . esc_url( $card['permalink'] ) . '">';
                if ( ! empty( $card['image_url'] ) ) {
                    echo '<img src="' . esc_url( $card['image_url'] ) . '" alt="' . esc_attr( $card['title'] ) . '"/>';
                }
                echo '</a></div>';
                if ( ! empty( $card['title'] ) ) {
                    echo '<span class="mfb-title">' . esc_html( $card['title'] ) . '</span>';
                }
                echo '</article>';
            }

            echo '</div>'; // .mfb-item
        }
    }

    echo '</div></div>'; // grid + wrapper

    // Pagination
    if ( $show_pagination && ! empty( $query->max_num_pages ) ) {
        $pagination = paginate_links( [
            'total'     => $query->max_num_pages,
            'current'   => $paged,
            'type'      => 'list',
            'prev_text' => '« Prev',
            'next_text' => 'Next »',
        ] );
        if ( $pagination ) {
            echo '<nav class="mfb-pagination" aria-label="Books pagination">' . $pagination . '</nav>';
        }
    }

    wp_reset_postdata();
    return ob_get_clean();
}
endif;