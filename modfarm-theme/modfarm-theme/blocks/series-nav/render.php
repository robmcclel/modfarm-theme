<?php
/**
 * Server-side render for Series Prev/Next block
 * - Stays within current book's series
 * - Supports ordering by: series_position | publication_date | reading_order
 * - Editor rendering fix via attributes['context']['postId']
 */

if ( ! function_exists( 'modfarm_render_series_nav_block' ) ) {
	function modfarm_render_series_nav_block( $attributes, $content ) {

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

		// Attributes
		$mode       = isset( $attributes['mode'] ) ? (string) $attributes['mode'] : 'position'; // position|pubdate|reading
		$prev_label = isset( $attributes['prevLabel'] ) ? (string) $attributes['prevLabel'] : '« Previous';
		$next_label = isset( $attributes['nextLabel'] ) ? (string) $attributes['nextLabel'] : 'Next »';

		// If you ever need overrides, expose these as block attributes later.
		$series_tax   = 'book-series';
		$key_position = 'series_position';
		$key_reading  = 'reading_order';
		$key_pubdate  = 'publication_date'; // expected Y-m-d

		if ( ! in_array( $mode, [ 'position', 'pubdate', 'reading' ], true ) ) {
			$mode = 'position';
		}

		// Determine series term (basic: first assigned). If you have a "primary series" meta, wire it here.
		$terms = wp_get_post_terms( $post_id, $series_tax, [ 'orderby' => 'name', 'order' => 'ASC' ] );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return '';
		}
		$term = $terms[0];

		// Fetch all books in this series (ids only)
		$query = [
			'post_type'      => 'book',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'tax_query'      => [
				[
					'taxonomy' => $series_tax,
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				],
			],
		];

		// For position/reading we can order by meta_value_num in SQL.
		// For pubdate we do a safe PHP sort with fallback to post_date.
		if ( $mode === 'position' ) {
			$query['meta_key'] = $key_position;
			$query['orderby']  = 'meta_value_num';
			$query['order']    = 'ASC';
		} elseif ( $mode === 'reading' ) {
			$query['meta_key'] = $key_reading;
			$query['orderby']  = 'meta_value_num';
			$query['order']    = 'ASC';
		} else {
			// pubdate
			$query['orderby'] = 'none';
		}

		$ids = get_posts( $query );

		if ( empty( $ids ) || ! is_array( $ids ) ) {
			return '';
		}

		// Pubdate sort (fallback to post_date)
		if ( $mode === 'pubdate' ) {
			$rows = [];
			foreach ( $ids as $id ) {
				$d = get_post_meta( $id, $key_pubdate, true );
				$t = $d ? strtotime( $d ) : get_post_time( 'U', true, $id );
				$rows[] = [ 'id' => (int) $id, 't' => $t ?: 0 ];
			}
			usort( $rows, static function( $a, $b ) {
				return $a['t'] <=> $b['t'];
			} );
			$ids = array_column( $rows, 'id' );
		}

		// If position/reading meta is missing for some, push those to the end by title ASC (keeps nav stable).
		if ( $mode === 'position' || $mode === 'reading' ) {
			$key     = ( $mode === 'reading' ) ? $key_reading : $key_position;
			$with    = [];
			$without = [];

			foreach ( $ids as $id ) {
				$val = get_post_meta( $id, $key, true );
				if ( $val !== '' && $val !== null ) {
					$with[] = [ 'id' => (int) $id, 'n' => (int) $val ];
				} else {
					$without[] = (int) $id;
				}
			}

			usort( $with, static function( $a, $b ) {
				return $a['n'] <=> $b['n'];
			} );

			if ( ! empty( $without ) ) {
				usort( $without, static function( $i, $j ) {
					return strcasecmp( get_the_title( $i ), get_the_title( $j ) );
				} );
			}

			$ids = array_merge( array_column( $with, 'id' ), $without );
		}

		$index = array_search( (int) $post_id, $ids, true );
		if ( $index === false ) {
			return '';
		}

		$prev_id = ( $index > 0 ) ? ( $ids[ $index - 1 ] ?? 0 ) : 0;
		$next_id = ( $index < ( count( $ids ) - 1 ) ) ? ( $ids[ $index + 1 ] ?? 0 ) : 0;

		// If this is a 1-book series, you can choose to hide entirely.
		// (Keeping it visible with disabled labels is also fine.)
		if ( count( $ids ) < 2 ) {
			return '';
		}

		$container_class = 'mf-series-nav';

		ob_start();
		?>
		<nav class="<?php echo esc_attr( $container_class ); ?>" aria-label="Series navigation">
			<div class="mf-series-nav__inner">

				<?php if ( $prev_id ) : ?>
					<a class="mf-series-nav__prev" href="<?php echo esc_url( get_permalink( $prev_id ) ); ?>">
						<?php echo esc_html( $prev_label ); ?>
					</a>
				<?php else : ?>
					<span class="mf-series-nav__prev is-disabled"><?php echo esc_html( $prev_label ); ?></span>
				<?php endif; ?>

				<span class="mf-series-nav__series"><?php echo esc_html( $term->name ); ?></span>

				<?php if ( $next_id ) : ?>
					<a class="mf-series-nav__next" href="<?php echo esc_url( get_permalink( $next_id ) ); ?>">
						<?php echo esc_html( $next_label ); ?>
					</a>
				<?php else : ?>
					<span class="mf-series-nav__next is-disabled"><?php echo esc_html( $next_label ); ?></span>
				<?php endif; ?>

			</div>
		</nav>
		<?php
		return ob_get_clean();
	}
}