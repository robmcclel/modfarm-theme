<?php
// blocks/hero-cover/render.php

function modfarm_render_hero_cover_block( $attributes, $content = '', $block = null ) {

    // ===== Attributes / defaults =====
    $mode            = $attributes['mode']            ?? 'auto';  // auto|manual
    $manual_url      = $attributes['manualUrl']       ?? '';

    $book_meta_key   = $attributes['bookMetaKey']     ?? 'hero_image';
    $term_meta_key   = $attributes['termMetaKey']     ?? 'archive_image';

    $fallback_feat   = ! empty( $attributes['fallbackFeatured'] );

    $min_height      = (int) ( $attributes['minHeight'] ?? 420 );
    $dim_ratio       = (int) ( $attributes['dimRatio']  ?? 30 );
    $overlay_color   = $attributes['overlayColor']     ?? '#000000';
    $overlay_gradient = $attributes['overlayGradient'] ?? '';

    $content_max_w   = $attributes['contentMaxWidth']  ?? '1200px';
    $content_align   = $attributes['contentAlign']     ?? 'center'; // left|center|right

    $content_align   = in_array( $content_align, ['left','center','right'], true ) ? $content_align : 'center';
      $dim_ratio       = max( 0, min( 100, $dim_ratio ) );
      $overlay_opacity = $dim_ratio / 100;
    
      // ===== Overlay background (gradient > color) =====
      $overlay_bg = '';
      if ( ! empty( $overlay_gradient ) ) {
          // Gradient string like: linear-gradient(... ) or var(--wp--preset--gradient--slug)
          $overlay_bg = $overlay_gradient;
      } else {
          $overlay_bg = $overlay_color ?: '#000000';
      }


    // ===== Resolve image URL =====
    $image_url = '';

    // Manual always wins
    if ( $mode === 'manual' ) {
        $image_url = esc_url( $manual_url );
    } else {

        // Taxonomy archives
        if ( is_category() || is_tag() || is_tax() ) {
            $term = get_queried_object();
            if ( $term && ! empty( $term->term_id ) ) {
                $raw = get_term_meta( (int) $term->term_id, $term_meta_key, true );

                if ( is_numeric( $raw ) ) {
                    $image_url = wp_get_attachment_url( (int) $raw );
                } else {
                    $image_url = esc_url( $raw );
                }
            }

            if ( ! $image_url ) {
                return '<div class="modfarm-hero-cover missing-hero">No archive image found.</div>';
            }

        } else {

            // Singular: needs post context
            $post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();
            if ( ! $post_id || $post_id === 0 ) {
                return '<div class="modfarm-hero-cover missing-hero">No post context.</div>';
            }

            $raw = get_post_meta( $post_id, $book_meta_key, true );

            if ( is_numeric( $raw ) ) {
                $image_url = wp_get_attachment_url( (int) $raw );
            } else {
                $image_url = esc_url( $raw );
            }

            // Optional featured fallback
            if ( ! $image_url && $fallback_feat && has_post_thumbnail( $post_id ) ) {
                $image_url = get_the_post_thumbnail_url( $post_id, 'full' );
            }

            if ( ! $image_url ) {
                return '<div class="modfarm-hero-cover missing-hero">No image found.</div>';
            }
        }
    }

    // ===== Build wrapper attrs (Cover-like) =====
    $classes = [
        'modfarm-hero-cover',
        'align-' . $content_align,
    ];

    $style  = 'min-height:' . $min_height . 'px;';
    $style .= 'background-image:url(' . esc_url( $image_url ) . ');';
    $style .= 'background-size:cover;';
    $style .= 'background-position:50% 50%;';

    // If you want alignfull support via the editor toolbar, keep wrapper attrs and let WP add alignfull class.
    $wrapper = get_block_wrapper_attributes([
      'class' => implode( ' ', $classes ),
      'style' => $style,
  ]);

  // --- Preview-only mode (editor background layer) ----------------------------
  $is_preview = ! empty( $attributes['__preview'] );

  ob_start();
  ?>
  <section <?php echo $wrapper; ?>>
      <div class="modfarm-hero-cover__overlay"
           style="background:<?php echo esc_attr( $overlay_bg ); ?>;opacity:<?php echo esc_attr( $overlay_opacity ); ?>;"></div>

      <?php if ( ! $is_preview ) : ?>
        <div class="modfarm-hero-cover__content"
             style="max-width:<?php echo esc_attr( $content_max_w ); ?>;text-align:<?php echo esc_attr( $content_align ); ?>;">
            <?php echo $content ? $content : ''; ?>
        </div>
      <?php endif; ?>
  </section>
  <?php
  return ob_get_clean();

}