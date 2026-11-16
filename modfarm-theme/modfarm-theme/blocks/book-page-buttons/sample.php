<?php
defined( 'ABSPATH' ) || exit;

/** Return the matching sample, including its fragment, for one Book Button. */
function modfarm_book_button_sample( $post_id, $fragment ) {
    if ( ! preg_match( '/\A#book-sample(?:-([a-z0-9]+(?:-[a-z0-9]+)*))?\z/', $fragment, $parts ) ) {
        return null;
    }
    if ( 'publish' !== get_post_status( $post_id ) && ! current_user_can( 'read_post', $post_id ) ) {
        return array();
    }
    if ( post_password_required( $post_id ) ) {
        return array();
    }

    $samples = get_post_meta( $post_id, '_modfarm_book_samples', true );
    $sample  = is_array( $samples ) ? ( $samples[ $parts[1] ?? '' ] ?? array() ) : array();
    if ( ! is_array( $sample ) || empty( $sample['body'] ) ) {
        return array();
    }
    $sample['id'] = substr( $fragment, 1 );
    if ( ! is_singular( 'book' ) || (int) get_queried_object_id() !== $post_id ) {
        $sample['id'] = 'mf-book-' . $post_id . '-' . $sample['id'];
    }
    return $sample;
}

/** Render one reader section even if several Book Buttons point to it. */
function modfarm_render_book_button_sample( $post_id, array $sample ) {
    static $rendered = array();
    $id  = $sample['id'];
    $key = get_current_blog_id() . ':' . $id;
    if ( isset( $rendered[ $key ] ) ) {
        return;
    }
    $rendered[ $key ] = true;

    $heading_id = 'mf-sample-heading-' . $id;
    $title = ( $sample['title'] ?? '' ) ?: get_the_title( $post_id );
    $font  = '';
    if ( ! empty( $sample['heading_font'] ) && function_exists( 'modfarm_get_installed_custom_fonts' ) ) {
        $fonts = modfarm_get_installed_custom_fonts();
        if ( isset( $fonts[ $sample['heading_font'] ] ) ) {
            $font = '"' . addcslashes( $sample['heading_font'], "\\\"\n\r\f" ) . '", serif';
        }
    }
    $palette = array();
    foreach ( array( 'background_color', 'text_color', 'heading_color', 'button_background_color', 'button_text_color', 'button_border_color' ) as $key ) {
        $value = $sample[ $key ] ?? '';
        if ( is_string( $value ) && ( $color = sanitize_hex_color( $value ) ) ) {
            $palette[] = '--mfs-sample-' . str_replace( '_', '-', $key ) . ':' . $color;
        }
    }
    ?>
    <section class="mfs-book-sample" id="<?php echo esc_attr( $id ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>"<?php if ( $palette ) : ?> style="<?php echo esc_attr( implode( ';', $palette ) ); ?>"<?php endif; ?>>
        <header class="mfs-book-sample-header">
            <div>
                <h2 id="<?php echo esc_attr( $heading_id ); ?>" tabindex="-1"<?php if ( $font ) : ?> style="<?php echo esc_attr( 'font-family:' . $font ); ?>"<?php endif; ?>><?php echo esc_html( $title ); ?></h2>
                <?php if ( ! empty( $sample['subtitle'] ) ) : ?>
                    <p class="mfs-book-sample-subtitle"><?php echo esc_html( $sample['subtitle'] ); ?></p>
                <?php endif; ?>
            </div>
            <button type="button" class="mfs-book-sample-close" aria-label="<?php esc_attr_e( 'Close sample', 'modfarm' ); ?>">&times;</button>
        </header>
        <div class="mfs-book-sample-content" tabindex="0" role="region" aria-label="<?php esc_attr_e( 'Sample text', 'modfarm' ); ?>">
            <div class="mfs-book-sample-text">
                <?php echo wp_kses_post( wpautop( $sample['body'] ) ); ?>
            </div>
            <?php if ( ! empty( $sample['cta_url'] ) && ! empty( $sample['cta_label'] ) ) :
                $cta_event = array(
                    'event_type'     => 'click',
                    'event_category' => 'book_sample_cta',
                    'origin'         => 'book_page_buttons',
                    'book_id'        => $post_id,
                    'label'          => $sample['cta_label'],
                );
                ?>
                <a class="book-page-button is-primary mfs-book-sample-cta" href="<?php echo esc_url( $sample['cta_url'] ); ?>"
                   data-mf-event="<?php echo esc_attr( wp_json_encode( $cta_event ) ); ?>"
                   data-mf-href="<?php echo esc_attr( $sample['cta_url'] ); ?>"><?php echo esc_html( $sample['cta_label'] ); ?></a>
            <?php endif; ?>
        </div>
    </section>
    <?php
}
