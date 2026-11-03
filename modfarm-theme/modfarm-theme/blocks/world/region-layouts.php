<?php
/** Render curated World Regions with the same media/text grammar as theme features. */
defined( 'ABSPATH' ) || exit;

function modfarm_world_region_entries( $region, $public ) {
    if ( 'type' === $region['source'] ) return array_values( array_filter( $public['elements'], static function ( $entry ) use ( $region ) { return $entry['type'] === $region['type']; } ) );
    $by_id = array_column( $public['elements'], null, 'id' ); $entries = array();
    foreach ( $region['element_ids'] as $id ) if ( isset( $by_id[$id] ) ) $entries[] = $by_id[$id];
    return $entries;
}

function modfarm_world_region_style( $region ) {
    $ratios = array( 'original' => 'auto', 'square' => '1 / 1', 'portrait' => '3 / 4', 'landscape' => '16 / 9' );
    $style = '--mfw-region-columns:' . (int) $region['columns'] . ';--mfw-image-ratio:' . $ratios[$region['image_ratio']] . ';--mfw-image-fit:' . $region['image_fit'] . ';';
    foreach ( $region['colors'] as $key => $color ) if ( $color ) $style .= '--mfw-' . $key . ':' . $color . ';';
    return $style;
}

function modfarm_world_region_content( $region, $public, $presentation, $term ) {
    if ( 'overview' === $region['source'] ) return modfarm_world_section_content( 'overview', $public, $presentation, $term );
    if ( 'books' === $region['source'] ) {
        $presentation['books'] = 'included';
        return modfarm_world_section_content( 'books', $public, $presentation, $term );
    }
    $entries = modfarm_world_region_entries( $region, $public );
    if ( ! $entries ) return '';
    if ( 'inherit' !== $region['description'] ) $presentation['description'] = $region['description'];
    $html = '<div class="mfw-entries">';
    foreach ( $entries as $index => $entry ) {
        $right = 'right' === $region['image_side'];
        if ( 'alternating' === $region['layout'] && $index % 2 ) $right = ! $right;
        $image = '';
        if ( $presentation['show_images'] && ! empty( $entry['image_id'] ) ) $image = wp_get_attachment_image( absint( $entry['image_id'] ), 'full', false, array( 'class' => 'mfw-entry-image', 'alt' => $entry['name'] ) );
        $html .= '<article class="mfw-entry' . ( $right ? ' mfw-image-right' : '' ) . ( $image ? '' : ' mfw-without-image' ) . '">' . $image . '<div class="mfw-entry-copy"><h3>' . esc_html( $entry['name'] ) . '</h3>';
        if ( ! empty( $entry['summary'] ) ) $html .= '<p class="mfw-entry-summary">' . nl2br( esc_html( $entry['summary'] ) ) . '</p>';
        if ( 'full' === $presentation['description'] && ! empty( $entry['description'] ) ) $html .= '<div class="mfw-rich-text">' . wp_kses_post( wpautop( $entry['description'] ) ) . '</div>';
        $html .= '</div></article>';
    }
    return $html . '</div>';
}

/** Older Core packages retain their original category-based rendering. */
function modfarm_world_build_regions( $presentation, $public, $term, $attributes ) {
    $sections = array();
    if ( ! function_exists( 'modfarm_world_regions' ) ) {
        foreach ( $presentation['sections'] as $section ) if ( $section['enabled'] ) {
            $html = modfarm_world_section_content( $section['key'], $public, $presentation, $term );
            if ( trim( $html ) ) $sections[] = array( 'key' => $section['key'], 'label' => $section['label'], 'html' => $html, 'show_heading' => true, 'class' => '', 'style' => '' );
        }
        return $sections;
    }
    $regions = modfarm_world_regions( $presentation );
    if ( 'included' === ( $attributes['books'] ?? '' ) && ! array_filter( $regions, static function ( $region ) { return $region['enabled'] && 'books' === $region['source']; } ) ) {
        $region = modfarm_world_region_default( 'region-included-books' ); $region['title'] = 'Books'; $region['source'] = 'books'; $regions[] = $region;
    }
    foreach ( $regions as $region ) {
        if ( ! $region['enabled'] || ( 'books' === $region['source'] && 'separate' === ( $attributes['books'] ?? '' ) ) ) continue;
        $html = modfarm_world_region_content( $region, $public, $presentation, $term );
        if ( ! trim( $html ) ) continue;
        $sections[] = array( 'key' => $region['id'], 'label' => $region['title'], 'html' => $html, 'show_heading' => $region['show_heading'], 'class' => 'mfw-region mfw-region--' . $region['layout'], 'style' => modfarm_world_region_style( $region ) );
    }
    return $sections;
}
