<?php
/**
 * Server render for modfarm/archive-book-list
 */

defined( 'ABSPATH' ) || exit;

// Simple helpers for archive meta/images
if ( ! function_exists( 'mfs_get_archive_term_meta' ) ) {
    function mfs_get_archive_term_meta( $term_id, $key, $default = '' ) {
        $val = get_term_meta( $term_id, $key, true );
        return ( $val === '' || $val === null ) ? $default : $val;
    }

    function mfs_get_archive_term_media( $term_id, $which = 'hero', $size = 'full' ) {
        $meta_key = ( $which === 'default' ) ? 'archive_default_image' : 'archive_hero_image';
        $att_id   = absint( get_term_meta( $term_id, $meta_key, true ) );
        if ( ! $att_id ) return '';
        $url = wp_get_attachment_image_url( $att_id, $size );
        return $url ?: '';
    }
}

if ( ! function_exists( 'modfarm_render_archive_book_list_block' ) ) :
function modfarm_render_archive_book_list_block( $attributes ) {

    // --------------------------------------------------
    // 1. BASIC ATTRIBUTES + ARCHIVE CONTEXT
    // --------------------------------------------------
    $defaults = array(
        'image-type'      => 'featured',

        'books-in-row'    => '25%',
        'display-layout'  => 'grid',
        'display-order'   => 'DESC',
        'order-date-key'   => 'publication_date',
        'books-per-page'  => 12,
        'show-pagination' => false,

        'button-text'     => 'See The Book',
        'button-link'     => 'bookpage',
        'button-target'   => '_self',
        'tracker-loc'     => '',

        'samplebtn-text'  => 'Play Sample',

        'audio-mode'      => 'auto',

        'volume-text'     => 'Book',

        'book-format'     => array( 'id' => 0 ),

        // Card grammar defaults
        'cardUseGlobal'   => true,
        'cardCoverShape'  => 'inherit',
        'cardButtonShape' => 'inherit',
        'cardSampleShape' => 'inherit',
        'cardCtaMode'     => 'inherit',
        'cardShadowStyle' => 'inherit',

        'cardShowTitle'         => true,
        'cardShowSeries'        => true,
        'cardShowPrimaryButton' => true,
        'cardShowSampleButton'  => true,

        'cardButtonBg'          => '',
        'cardButtonFg'          => '',
        'cardSampleBg'          => '',
        'cardSampleFg'          => '',
    );

    $a   = wp_parse_args( $attributes, $defaults );
    $display_layout  = in_array( (string) ( $a['display-layout'] ?? 'grid' ), array( 'grid', 'horizontal' ), true )
        ? (string) $a['display-layout']
        : 'grid';
    $horizontal_cols = max( 3, min( 5, (int) ( $a['horizontal-columns'] ?? 4 ) ) );
    $horizontal_width = 'calc(' . round( 100 / $horizontal_cols, 6 ) . '% - ' . round( 10 * ( $horizontal_cols - 1 ) / $horizontal_cols, 4 ) . 'px)';
    $qo  = get_queried_object();

    // --------------------------------------------------
    // ✅ SmartLinks toggle (default ON; matches other upgraded blocks)
    // --------------------------------------------------
    $use_smartlinks = array_key_exists( 'use-smartlinks', $a )
        ? (bool) $a['use-smartlinks']
        : true;

    // --------------------------------------------------
    // 1a. ARCHIVE TERM → IMAGE VARIANT + FLAGS
    // --------------------------------------------------
    $archive_show_title  = null;
    $archive_show_series = null;
    $archive_show_button = null;
    $archive_show_sample = null;

    if ( $qo instanceof WP_Term ) {
        // Image variant mapping
        $variant = mfs_get_archive_term_meta( $qo->term_id, 'archive_image_variant', 'featured' );
        $image_map = array(
            'featured'  => 'featured',
            'flat'      => 'cover_image_flat',
            '3d'        => 'cover_image_3d',
            'audio'     => 'cover_image_audio',
            'composite' => 'cover_image_composite',
        );
        if ( ( empty( $a['image-type'] ) || $a['image-type'] === 'featured' ) && ! empty( $image_map[ $variant ] ) ) {
            $a['image-type'] = $image_map[ $variant ];
        }

        // Archive-level visibility flags (null = "no preference")
        $t = mfs_get_archive_term_meta( $qo->term_id, 'archive_show_title',  '' );
        if ( $t !== '' ) $archive_show_title  = (bool) $t;

        $t = mfs_get_archive_term_meta( $qo->term_id, 'archive_show_series', '' );
        if ( $t !== '' ) $archive_show_series = (bool) $t;

        $t = mfs_get_archive_term_meta( $qo->term_id, 'archive_show_button', '' );
        if ( $t !== '' ) $archive_show_button = (bool) $t;

        $t = mfs_get_archive_term_meta( $qo->term_id, 'archive_show_sample', '' );
        if ( $t !== '' ) $archive_show_sample = (bool) $t;
    }

    // --------------------------------------------------
    // 2. GLOBAL vs LOCAL CARD SETTINGS (MATCHES MULTI-TAX)
    // --------------------------------------------------
    $opts            = get_option( 'modfarm_theme_settings', array() );
    $card_use_global = array_key_exists( 'cardUseGlobal', $a )
        ? (bool) $a['cardUseGlobal']
        : true;

    $pick_token = function( $local, $allowed, $global, $default ) {
        $local  = (string) $local;
        $global = (string) $global;

        if ( $local && $local !== 'inherit' && in_array( $local, $allowed, true ) ) {
            return $local;
        }
        if ( $global && in_array( $global, $allowed, true ) ) {
            return $global;
        }
        return $default;
    };

    // Shapes
    $global_cover_shape  = $opts['book_card_cover_shape']  ?? '';
    $global_button_shape = $opts['book_card_button_shape'] ?? '';
    $global_sample_shape = $opts['book_card_sample_shape'] ?? '';

    $local_cover_shape   = $card_use_global ? 'inherit' : ( $a['cardCoverShape']  ?? 'inherit' );
    $local_button_shape  = $card_use_global ? 'inherit' : ( $a['cardButtonShape'] ?? 'inherit' );
    $local_sample_shape  = $card_use_global ? 'inherit' : ( $a['cardSampleShape'] ?? 'inherit' );

    $cover_shape  = $pick_token( $local_cover_shape,  array( 'square','rounded' ),               $global_cover_shape,  'square' );
    $button_shape = $pick_token( $local_button_shape, array( 'square','rounded','pill' ),        $global_button_shape, 'square' );
    $sample_shape = $pick_token( $local_sample_shape, array( 'square','rounded','pill' ),        $global_sample_shape, 'pill' );

    // CTA spacing
    $global_cta_mode = $opts['book_card_cta_mode'] ?? '';
    $local_cta_mode  = $card_use_global ? 'inherit' : ( $a['cardCtaMode'] ?? 'inherit' );
    $cta_mode        = $pick_token( $local_cta_mode, array( 'joined','gap' ), $global_cta_mode, 'joined' );

    // Shadow/effect
    $global_shadow = $opts['book_card_shadow_style'] ?? '';
    $local_shadow  = $card_use_global ? 'inherit' : ( $a['cardShadowStyle'] ?? 'inherit' );
    $effect        = $pick_token(
        $local_shadow,
        array( 'flat','shadow-sm','shadow-md','shadow-lg','emboss' ),
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
        $show_title         = ! empty( $a['cardShowTitle'] );
        $show_series        = ! empty( $a['cardShowSeries'] );
        $show_primary_btn   = ! empty( $a['cardShowPrimaryButton'] );
        $show_sample_btn    = ! empty( $a['cardShowSampleButton'] );
    }

    // Archive-level flags override those (if present)
    if ( $archive_show_title  !== null ) $show_title       = $archive_show_title;
    if ( $archive_show_series !== null ) $show_series      = $archive_show_series;
    if ( $archive_show_button !== null ) $show_primary_btn = $archive_show_button;
    if ( $archive_show_sample !== null ) $show_sample_btn  = $archive_show_sample;

    // Audio mode (local or forced off if sample button is hidden)
    $audio_mode_attr = $a['audio-mode'] ?? 'auto';
    $audio_mode      = $show_sample_btn ? $audio_mode_attr : 'off';

    // --------------------------------------------------
    // 4. PER-BLOCK COLOR OVERRIDES & CSS TOKENS
    // --------------------------------------------------
    $card_btn_bg    = $a['cardButtonBg']   ?? '';
    $card_btn_fg    = $a['cardButtonFg']   ?? '';
    $card_sample_bg = $a['cardSampleBg']   ?? '';
    $card_sample_fg = $a['cardSampleFg']   ?? '';

    $custom_vars = array();

    if ( $card_btn_bg ) {
        $custom_vars[] = '--mfb-btn-bg:'     . $card_btn_bg;
        $custom_vars[] = '--mfb-btn-border:' . $card_btn_bg;
    }
    if ( $card_btn_fg ) {
        $custom_vars[] = '--mfb-btn-fg:' . $card_btn_fg;
    }
    if ( $card_sample_bg ) {
        $custom_vars[] = '--mfb-sample-bg:'     . $card_sample_bg;
        $custom_vars[] = '--mfb-sample-border:' . $card_sample_bg;
    }
    if ( $card_sample_fg ) {
        $custom_vars[] = '--mfb-sample-fg:' . $card_sample_fg;
    }

    // CTA gap
    if ( $cta_mode === 'gap' ) {
        $custom_vars[] = '--mfb-cta-gap:10px';
    } else {
        $custom_vars[] = '--mfb-cta-gap:0px';
    }

    // Button shape radius
    if ( $button_shape === 'square' ) {
        $custom_vars[] = '--mfb-button-radius:0px';
    } elseif ( $button_shape === 'rounded' ) {
        $custom_vars[] = '--mfb-button-radius:16px';
    } elseif ( $button_shape === 'pill' ) {
        $custom_vars[] = '--mfb-button-radius:9999px';
    }

    // Sample shape radius
    if ( $sample_shape === 'square' ) {
        $custom_vars[] = '--mfb-sample-radius:0px';
    } elseif ( $sample_shape === 'rounded' ) {
        $custom_vars[] = '--mfb-sample-radius:16px';
    } elseif ( $sample_shape === 'pill' ) {
        $custom_vars[] = '--mfb-sample-radius:9999px';
    }

    // Local visibility override for sample/audio button
    if ( $show_sample_btn ) {
        $custom_vars[] = '--mfb-audio-cta-display:inline-flex';
    } else {
        $custom_vars[] = '--mfb-audio-cta-display:none';
    }

    // --------------------------------------------------
    // 5. ARCHIVE CONTEXT → TAX QUERY
    // --------------------------------------------------
    $tax_query       = array();
    $is_books_archive = is_post_type_archive( 'book' ) || ( is_post_type_archive() && get_query_var( 'post_type' ) === 'book' );

    if ( $qo instanceof WP_Term ) {
        $tax_query[] = array(
            'taxonomy' => $qo->taxonomy,
            'field'    => 'term_id',
            'terms'    => (int) $qo->term_id,
        );
    } elseif ( ! $is_books_archive ) {
        if ( ! is_admin() ) {
            return '<div class="mfb-wrapper"><p>No archive context detected.</p></div>';
        }
    }

    // Optional book-format filter (align with multi-tax)
    $format_term = ( isset( $a['book-format']['id'] ) && $a['book-format']['id'] )
        ? (int) $a['book-format']['id']
        : null;

    if ( $format_term ) {
        $tax_query[] = array(
            'taxonomy' => 'book-format',
            'field'    => 'term_id',
            'terms'    => $format_term,
        );
    }

    // --------------------------------------------------
    // 6. QUERY
    // --------------------------------------------------
    $paged         = max( 1, get_query_var( 'paged' ) ?: get_query_var( 'page' ) ?: 1 );
    $order_setting = $a['display-order'];
    $date_keys     = array( 'publication_date', 'hardcover_publication_date', 'audiobook_publication_date' );
    $order_date_key = in_array( $a['order-date-key'], $date_keys, true ) ? $a['order-date-key'] : 'publication_date';
    $orderby       = ( $order_setting === 'rand' ) ? 'rand' : 'meta_value';
    $order         = ( $order_setting === 'ASC' )  ? 'ASC'  : 'DESC';

    $ppp = (int) $a['books-per-page'];
    $ppp = $display_layout === 'horizontal' ? max( 1, $ppp ) : ( ! empty( $a['show-pagination'] ) ? max( 1, $ppp ) : -1 );
    $query_has_pagination = $display_layout !== 'horizontal' && ! empty( $a['show-pagination'] );

    $args = array(
        'post_type'      => 'book',
        'post_status'    => 'publish',
        'posts_per_page' => $ppp,
        'paged'          => $query_has_pagination ? $paged : 1,
        'tax_query'      => $tax_query,
        'no_found_rows'  => ! $query_has_pagination,
    );

    if ( $orderby === 'rand' ) {
        $args['orderby'] = 'rand';
    } else {
        $args['orderby']   = 'meta_value';
        $args['meta_key']  = $order_date_key;
        $args['meta_type'] = 'DATE';
        $args['order']     = $order;
    }

    $query = new WP_Query( $args );

    // --------------------------------------------------
    // 7. WRAPPER CLASSES + GRID STYLE
    // --------------------------------------------------
    $books_per_row   = (string) $a['books-in-row'];
    $image_type      = (string) $a['image-type'];
    $show_pagination = $query_has_pagination;
    $btn_link        = (string) $a['button-link'];
    $btn_text        = (string) $a['button-text'];
    $btn_target      = (string) $a['button-target'];
    $tracker         = (string) $a['tracker-loc'];
    $volume_txt      = (string) $a['volume-text'];
    $sample_btn_text = (string) ( $a['samplebtn-text'] ?? __( 'Play Sample', 'modfarm' ) );

    $wrapper_classes = array(
        'mfb-wrapper',
        'is-archive',
        'mfb-effect--' . sanitize_html_class( $effect ),
        'mfb-cover--'  . sanitize_html_class( $cover_shape ),
        'mfb-button--' . sanitize_html_class( $button_shape ),
        'mfb-sample--' . sanitize_html_class( $sample_shape ),
        'mfb-cta--'    . sanitize_html_class( $cta_mode ),
        'mfb-wrapper--' . sanitize_html_class( $display_layout ),
    );

    $pct  = floatval( str_replace( '%', '', $books_per_row ) );
    $cols = ( $pct > 0 ) ? max( 1, (int) round( 100 / $pct ) ) : 4;
    $grid_style = '--mfb-cols:' . (int) $cols . ';--mfb-scroll-cols:' . (int) $horizontal_cols . ';--mfb-scroll-card-width:' . $horizontal_width . ';';

    $wrapper_style = $grid_style;
    if ( ! empty( $custom_vars ) ) {
        $wrapper_style .= implode( ';', $custom_vars ) . ';';
    }

    // --------------------------------------------------
    // 8. OUTPUT
    // --------------------------------------------------
    ob_start();

    // Archive hero/default images (above grid)
    if ( $qo instanceof WP_Term ) {
        $display_hero    = (bool) mfs_get_archive_term_meta( $qo->term_id, 'archive_display_hero',    0 );
        $display_default = (bool) mfs_get_archive_term_meta( $qo->term_id, 'archive_display_default', 0 );

        if ( $display_hero ) {
            $hero_url = mfs_get_archive_term_media( $qo->term_id, 'hero', 'full' );
            if ( $hero_url ) {
                echo '<div class="mfs-archive-hero"><img src="' . esc_url( $hero_url ) . '" alt="" loading="lazy" decoding="async"></div>';
            }
        }

        if ( $display_default ) {
            $def_url = mfs_get_archive_term_media( $qo->term_id, 'default', 'large' );
            if ( $def_url ) {
                echo '<div class="mfs-archive-default"><img src="' . esc_url( $def_url ) . '" alt="" loading="lazy" decoding="async"></div>';
            }
        }
    }

    static $scroll_count = 0;
    $scroll_count++;
    $scroll_id = 'mfb-archive-book-list-scroll-' . $scroll_count;

    echo '<div class="' . esc_attr( implode( ' ', array_filter( $wrapper_classes ) ) ) . '"' . ( $display_layout === 'horizontal' ? ' data-mf-card-scroll-wrap' : '' ) . '>';
    if ( $display_layout === 'horizontal' ) {
        echo '<div class="mfb-scroll-head"><div class="mfb-scroll-controls" aria-label="' . esc_attr__( 'Book carousel controls', 'modfarm' ) . '">';
        echo '<button type="button" class="mfb-scroll-control mfb-scroll-control--prev" data-mf-card-scroll-target="' . esc_attr( $scroll_id ) . '" data-mf-card-scroll-direction="-1" aria-label="' . esc_attr__( 'Previous books', 'modfarm' ) . '"><span aria-hidden="true">&larr;</span></button>';
        echo '<button type="button" class="mfb-scroll-control mfb-scroll-control--next" data-mf-card-scroll-target="' . esc_attr( $scroll_id ) . '" data-mf-card-scroll-direction="1" aria-label="' . esc_attr__( 'Next books', 'modfarm' ) . '"><span aria-hidden="true">&rarr;</span></button>';
        echo '</div></div>';
    }
    echo '<div id="' . esc_attr( $scroll_id ) . '" class="mfb-grid' . ( $display_layout === 'horizontal' ? ' mfb-grid--horizontal' : '' ) . '" style="' . esc_attr( $wrapper_style ) . '"' . ( $display_layout === 'horizontal' ? ' data-mf-card-scroll-rail' : '' ) . '>';

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $book_id   = get_the_ID();
            $permalink = get_permalink( $book_id );

            // Primary button link
            $button_url = $permalink;
            $button_is_internal_bookpage = true;

            if ( $show_primary_btn && $btn_link !== 'bookpage' ) {
                $candidate = get_post_meta( $book_id, $btn_link, true );
                if ( ! empty( $candidate ) ) {
                    $button_url = (string) $candidate;
                    $button_is_internal_bookpage = false;
                }
            }

            // ✅ SmartLinks: only apply to outbound (non-bookpage) URLs
            if ( ! $button_is_internal_bookpage && $use_smartlinks && function_exists( 'modfarm_smartlink_url' ) ) {
                $button_url = modfarm_smartlink_url( $button_url, array(
                    'context'  => 'book_list',
                    'book_id'  => $book_id,
                    'origin'   => 'archive-list',
                    'meta_key' => $btn_link,
                ) );
            }

            // ✅ Destination host tracking (best-effort from final href)
            $dest_host = '';
            $parsed = wp_parse_url( $button_url );
            if ( ! empty( $parsed['host'] ) ) {
                $dest_host = strtolower( $parsed['host'] );
            }

            // Image + fallbacks
            $img_url = '';
            if ( $image_type === 'featured' || $image_type === '' ) {
                $img_url = get_the_post_thumbnail_url( $book_id, 'full' ) ?: '';
            } else {
                $meta_val = get_post_meta( $book_id, $image_type, true );
                if ( $meta_val ) {
                    $img_url = is_numeric( $meta_val )
                        ? wp_get_attachment_image_url( (int) $meta_val, 'full' )
                        : esc_url( $meta_val );
                }
            }
            if ( ! $img_url ) {
                $fallback = get_the_post_thumbnail_url( $book_id, 'full' );
                if ( $fallback ) {
                    $img_url = $fallback;
                }
            }
            if ( ! $img_url ) {
                $fallback_id = get_post_meta( $book_id, 'cover_ebook', true );
                if ( $fallback_id ) {
                    $img_url = is_numeric( $fallback_id )
                        ? wp_get_attachment_image_url( (int) $fallback_id, 'full' )
                        : esc_url( $fallback_id );
                }
            }

            // Aspect based on image-type
            $aspect = '2 / 3';
            switch ( $image_type ) {
                case 'cover_image_audio':    $aspect = '1 / 1';  break;
                case 'cover_image_3d':       $aspect = '4 / 3';  break;
                case 'cover_image_composite':
                case 'hero_image':           $aspect = '16 / 9'; break;
            }

            // Series
            $series_terms = get_the_terms( $book_id, 'book-series' );
            $series_name  = ( ! empty( $series_terms ) && ! is_wp_error( $series_terms ) ) ? $series_terms[0]->name : '';
            $series_pos   = get_post_meta( $book_id, 'series_position', true );

            // AUDIO — aligned with multi-tax / book-page-audio
            $audio_embed  = (string) get_post_meta( $book_id, 'audio_player_embed',       true );
            $audio_sample = (string) get_post_meta( $book_id, 'audio_sample_url',         true );
            $audio_date   =         get_post_meta( $book_id, 'audiobook_publication_date', true ) ?: null;
            $audible_asin = (string) get_post_meta( $book_id, 'audible_asin',             true );
            $amazon_asin  = (string) get_post_meta( $book_id, 'asin_kindle',              true );

            if ( $audio_mode !== 'off' ) {
                $has_sample    = ( $audio_sample !== '' );
                $has_construct = ( $audible_asin !== '' || $amazon_asin !== '' );

                if ( ! $has_sample && ! $has_construct ) {
                    $card_audio_mode = 'off';
                } else {
                    $card_audio_mode = $audio_mode;
                }
            } else {
                $card_audio_mode = 'off';
            }

            // BUILD CARD
            $card = array(
                'id'        => $book_id,
                'title'     => get_the_title(),
                'permalink' => $permalink,
                'image_url' => $img_url,
                'aspect'    => $aspect,
                'format'    => null,

                'show_title'      => $show_title,
                'series_name'     => $show_series ? $series_name : '',
                'series_position' => $show_series ? $series_pos  : '',
                'volume_text'     => $volume_txt,

                'audio_mode'   => $card_audio_mode,
                'audio_embed'  => $audio_embed,
                'audio_sample' => $audio_sample,
                'audible_asin' => $audible_asin,
                'amazon_asin'  => $amazon_asin,
                'audio_date'   => $audio_date,

                // Legacy mirrors (safe to keep)
                'audio_player_embed'         => $audio_embed,
                'audio_sample_url'           => $audio_sample,
                'audiobook_publication_date' => $audio_date,

                'sample_button_text' => $sample_btn_text,

                'button' => $show_primary_btn ? array(
                    'text'       => $btn_text,
                    'url'        => $button_url,
                    'target'     => $btn_target,
                    'bg'         => $card_btn_bg,
                    'fg'         => $card_btn_fg,
                    'tracker'    => $tracker,
                    'origin'     => 'archive-list',

                    // ✅ Same upgrades as other blocks
                    'smartlinks' => ( ! $button_is_internal_bookpage && $use_smartlinks ),
                    'dest_host'  => $dest_host,
                ) : array(
                    'text'   => '',
                    'url'    => '',
                    'origin' => 'archive-list',
                ),
            );

            echo '<div class="mfb-item">';

            if ( function_exists( 'modfarm_render_book_card' ) ) {
                modfarm_render_book_card( $card );
            } else {
                // minimal fallback
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
        $pagination = paginate_links( array(
            'total'     => $query->max_num_pages,
            'current'   => $paged,
            'type'      => 'list',
            'prev_text' => '« Prev',
            'next_text' => 'Next »',
        ) );
        if ( $pagination ) {
            echo '<nav class="mfb-pagination" aria-label="Books pagination">' . $pagination . '</nav>';
        }
    }

    wp_reset_postdata();
    return ob_get_clean();
}
endif;
