<?php
/**
 * Server-side render for Book Page Buttons block
 * - Alignment support
 * - Primary/Secondary styling via global CSS tokens (ModFarm Settings)
 * - Backward compatible with per-button inline colors (optional)
 * - SmartLinks wrapping (Genius Quick Build Proxy) for eligible BMS fields
 * - Click tracking via ModFarm Core Events (data-mf-event)
 */

if ( ! function_exists( 'modfarm_render_book_page_buttons_block' ) ) {
	function modfarm_render_book_page_buttons_block( $attributes, $content ) {
		$post_id = get_the_ID();

		// Fix for editor rendering
		if ( ! $post_id && isset( $attributes['context']['postId'] ) ) {
			$post_id = intval( $attributes['context']['postId'] );
		}

		if ( ! $post_id ) {
			return '';
		}

		if ( get_post_type( $post_id ) !== 'book' ) {
			return '';
		}

		$buttons         = $attributes['buttons'] ?? [];
		$alignment       = $attributes['alignment'] ?? 'center';
		$container_class = 'mfs-book-page-buttons mfs-align-' . esc_attr( $alignment );
		$allow_overrides = ! empty( $attributes['showAdvanced'] );

		if ( empty( $buttons ) ) {
			return '';
		}

		// Radius override support (if you adopted radiusMode in block.json)
		$radius_mode   = $attributes['radiusMode'] ?? 'inherit';
		$border_radius = isset( $attributes['border_radius'] ) ? intval( $attributes['border_radius'] ) : 0;

		// Meta keys that should default to SECONDARY when button type is "inherit"
		$secondary_default_keys = [
			'text_sample_url',
			'audio_sample_url',
			'reviews_url',
			'serieslink',
			'review',
		];

		ob_start();
		?>
		<div class="<?php echo $container_class; ?>">
			<?php foreach ( $buttons as $button ) :

				$meta_key = isset($button['meta_key']) ? (string)$button['meta_key'] : '';
				$url      = $meta_key ? (string)get_post_meta( $post_id, $meta_key, true ) : '';

				if ( $url === '' ) {
					continue;
				}

				$label        = esc_html( $button['label'] ?? ( $meta_key ? ucfirst( $meta_key ) : 'Link' ) );
				$open_new_tab = ! empty( $button['new_tab'] );

				// Per-button type (new): inherit | primary | secondary
				$type = $button['type'] ?? 'inherit';
				if ( ! in_array( $type, [ 'inherit', 'primary', 'secondary' ], true ) ) {
					$type = 'inherit';
				}

				// Inherit logic: retail/buy => primary; samples/reviews/etc => secondary
				if ( $type === 'inherit' ) {
					$is_secondary = in_array( $meta_key, $secondary_default_keys, true );
					$type = $is_secondary ? 'secondary' : 'primary';
				}

				$classes = [
					'book-page-button',
					( $type === 'secondary' ) ? 'is-secondary' : 'is-primary',
				];

				$inline_vars = [];

                // Only apply per-button color overrides when Advanced is enabled.
                // This prevents older patterns with saved overrides from silently overriding inherited colors.
                if ( $allow_overrides ) {
                	$bg_color     = isset( $button['bg_color'] ) ? trim( (string) $button['bg_color'] ) : '';
                	$text_color   = isset( $button['text_color'] ) ? trim( (string) $button['text_color'] ) : '';
                	$border_color = isset( $button['border_color'] ) ? trim( (string) $button['border_color'] ) : '';
                
                	if ( $bg_color !== '' ) {
                		$inline_vars[] = '--mfb-bp-override-bg:' . esc_attr( $bg_color );
                	}
                	if ( $text_color !== '' ) {
                		$inline_vars[] = '--mfb-bp-override-fg:' . esc_attr( $text_color );
                	}
                	if ( $border_color !== '' ) {
                		$inline_vars[] = '--mfb-bp-override-border:' . esc_attr( $border_color );
                	}
                }


				$style_attr = ! empty( $inline_vars ) ? 'style="' . esc_attr( implode( ';', $inline_vars ) ) . '"' : '';

				// ============================================================
				// SmartLinks wrap (Genius Quick Build Proxy) — eligible keys only
				// - Audible excluded for now (SmartLinks core should already enforce)
				$destination   = (string)$url;
				$href          = $destination;
				$smart_wrapped = 0;

				if ( function_exists('mfc_smartlinks_wrap_url') ) {
					$maybe = mfc_smartlinks_wrap_url( $destination, $meta_key );
					if ( is_string($maybe) && $maybe !== '' && $maybe !== $destination ) {
						$href = $maybe;
						$smart_wrapped = 1;
					}
				}
				// ============================================================

				// ============================================================
				// Click tracking payload (ModFarm Core Events)
				$event_payload = [
					'event_type'     => 'click',
					'event_category' => 'book_button',
					'origin'         => 'book_page_buttons',
					'book_id'        => $post_id,
					'meta_key'       => $meta_key,
					'label'          => wp_strip_all_tags( $label ),
					'button_style'   => ($type === 'secondary') ? 'secondary' : 'primary',
					'smartlinks'     => $smart_wrapped ? 'genius_quickbuild' : 'none',
				];

				$data_mf_event = esc_attr( wp_json_encode( $event_payload ) );
				// ============================================================

				?>
				<a class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
				   data-mf-event="<?php echo $data_mf_event; ?>"
				   data-mf-href="<?php echo esc_attr( $href ); ?>"
				   data-mf-destination="<?php echo esc_attr( $destination ); ?>"
				   href="<?php echo esc_url( $href ); ?>"
				   <?php echo $style_attr; ?>
				   <?php echo $open_new_tab ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
					<?php echo $label; ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}