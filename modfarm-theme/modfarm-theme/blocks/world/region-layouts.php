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
    $style = '--mfw-region-columns:' . (int) $region['columns'] . ';--mfw-image-ratio:' . ( 'portraits' === $region['layout'] && 'original' === $region['image_ratio'] ? '1' : $ratios[$region['image_ratio']] ) . ';--mfw-image-fit:' . $region['image_fit'] . ';';
    $widths = array( 'main' => 'var(--mf-layout-content-width,var(--mf-content-width,var(--wp--style--global--content-size,1100px)))', 'wide' => 'var(--mf-layout-wide-width,var(--mf-wide-width,var(--wp--style--global--wide-size,1300px)))', 'full' => 'none' );
    $style .= '--mfw-region-width:' . ( $widths[$region['width'] ?? 'main'] ?? $widths['main'] ) . ';';
    if ( 'full' === ( $region['width'] ?? 'main' ) ) $style .= '--mfw-region-gutter:0px;';
    $shape = $region['image_shape'] ?? 'layout';
    if ( 'circle' === $shape || ( 'layout' === $shape && 'portraits' === $region['layout'] ) ) $style .= '--mfw-image-ratio:1;--mfw-image-fit:cover;--mfw-image-radius:50%;--mfw-image-width:220px;';
    elseif ( 'rounded' === $shape ) $style .= '--mfw-image-radius:18px;';
    elseif ( 'square' === $shape ) $style .= '--mfw-image-radius:0;';
    if ( 'inherit' !== ( $region['text_align'] ?? 'inherit' ) ) $style .= '--mfw-text-align:' . $region['text_align'] . ';';
    $align = $region['image_align'] ?? 'inherit';
    if ( 'inherit' !== $align ) {
        $style .= '--mfw-image-start:' . ( 'left' === $align ? '0' : 'auto' ) . ';--mfw-image-end:' . ( 'right' === $align ? '0' : 'auto' ) . ';--mfw-image-width:220px;';
    }
    foreach ( $region['colors'] as $key => $color ) if ( $color ) $style .= '--mfw-' . $key . ':' . $color . ';';
    return $style;
}

function modfarm_world_region_content( $region, $public, $presentation, $term ) {
    if ( 'overview' === $region['source'] ) {
        if ( ! trim( wp_strip_all_tags( $public['overview'] ) ) ) return '';
        $presentation['description'] = 'full';
        $entry = array( 'name' => $region['show_heading'] ? $region['title'] : '', 'description' => $public['overview'], 'image_id' => $region['image_id'] ?? 0 );
        return '<div class="mfw-entries">' . modfarm_world_entry_html( $entry, $presentation, 'right' === $region['image_side'] ? 'mfw-image-right' : '', 'full' ) . '</div>';
    }
    if ( 'books' === $region['source'] ) {
        $presentation['books'] = 'included';
        return modfarm_world_section_content( 'books', $public, $presentation, $term );
    }
    $entries = modfarm_world_region_entries( $region, $public );
    if ( ! $entries ) return '';
    if ( 'inherit' !== $region['description'] ) $presentation['description'] = $region['description'];
    $presentation['show_titles'] = $region['show_titles'] ?? true;
    $html = '<div class="mfw-entries">';
    foreach ( $entries as $index => $entry ) {
        $right = 'right' === $region['image_side'];
        if ( 'alternating' === $region['layout'] && $index % 2 ) $right = ! $right;
        $html .= modfarm_world_entry_html( $entry, $presentation, $right ? 'mfw-image-right' : '', 'full' );
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
        $sections[] = array( 'key' => $region['id'], 'label' => $region['title'], 'html' => $html, 'show_heading' => $region['show_heading'] && 'overview' !== $region['source'], 'show_titles' => in_array( $region['source'], array( 'overview', 'books' ), true ) || ( $region['show_titles'] ?? true ), 'class' => 'mfw-region mfw-region--' . $region['layout'] . ( 'overview' === $region['source'] ? ' mfw-region--overview' : '' ), 'style' => modfarm_world_region_style( $region ) );
    }
    return $sections;
}
