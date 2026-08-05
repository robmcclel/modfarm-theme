<?php
/**
 * Google Font catalog and helpers for the ModFarm typography settings.
 *
 * Only fonts selected in the theme settings are requested from Google. Keeping
 * the variants here gives the settings UI and the loader one source of truth.
 */

if (!defined('ABSPATH')) {
    exit;
}

function modfarm_font_library(): array {
    $fonts = [
        'Source Sans Pro'       => ['400', '400i', '600', '700'],
        'Abril Fatface'         => ['400'],
        'Arima Madurai'         => ['400', '700'],
        'Arimo'                 => ['400', '400i', '700', '700i'],
        'Arvo'                  => ['400', '400i', '700', '700i'],
        'Audiowide'             => ['400'],
        'Averia Libre'          => ['400', '400i', '700', '700i'],
        'Averia Sans Libre'     => ['400', '400i', '700', '700i'],
        'Averia Serif Libre'    => ['400', '400i', '700', '700i'],
        'Bebas Neue'            => ['400'],
        'Bangers'               => ['400'],
        'Besley'                => ['400', '400i', '700', '700i'],
        'Black Ops One'         => ['400'],
        'Cabin'                 => ['400', '400i', '700'],
        'Calistoga'             => ['400'],
        'Carter One'            => ['400'],
        'Cinzel Decorative'     => ['400', '700'],
        'Corben'                => ['400'],
        'Creepster'             => ['400'],
        'Crimson Text'          => ['400', '400i', '700', '700i'],
        'Droid Sans'            => ['400', '700'],
        'Droid Serif'           => ['400', '400i', '700', '700i'],
        'Eater'                 => ['400'],
        'EB Garamond'           => ['400', '400i', '700', '700i'],
        'Elsie'                 => ['400'],
        'Emilys Candy'          => ['400'],
        'Faster One'            => ['400'],
        'Forum'                 => ['400'],
        'Fugaz One'             => ['400'],
        'Hind Madurai'          => ['400', '700'],
        'Lato'                  => ['400', '400i', '700', '700i'],
        'Libre Baskerville'     => ['400', '400i', '700'],
        'Libre Franklin'        => ['400', '400i', '700', '700i'],
        'Limelight'             => ['400'],
        'Lora'                  => ['400', '400i', '700', '700i'],
        'Luckiest Guy'          => ['400'],
        'Martel'                => ['400', '700'],
        'Merriweather'          => ['300', '300i', '400', '400i', '700', '700i'],
        'Montserrat'            => ['400', '700'],
        'Mystery Quest'         => ['400'],
        'Noto Sans'             => ['400', '400i', '700', '700i'],
        'Noto Serif'            => ['400', '400i', '700', '700i'],
        'Nunito Sans'           => ['400', '400i', '700', '700i'],
        'Oleo Script'           => ['400', '700'],
        'Open Sans'             => ['400', '400i', '700', '700i'],
        'Open Sans Condensed'   => ['300', '300i', '700'],
        'Oswald'                => ['400', '700'],
        'Passion One'           => ['400', '700'],
        'Patua One'             => ['400'],
        'Pirata One'            => ['400'],
        'Playfair Display'      => ['400', '400i', '700'],
        'Poller One'            => ['400'],
        'Poppins'               => ['400', '400i', '600', '700', '700i'],
        'PT Serif'              => ['400', '700'],
        'PT Sans'               => ['400', '400i', '700', '700i'],
        'PT Sans Narrow'        => ['400', '700'],
        'Racing Sans One'       => ['400'],
        'Raleway'               => ['400', '700'],
        'Roboto'                => ['400', '400i', '700', '700i'],
        'Roboto Condensed'      => ['400', '400i', '700', '700i'],
        'Roboto Slab'           => ['400', '700'],
        'Share'                 => ['400', '400i', '700', '700i'],
        'Special Elite'         => ['400'],
        'Spicy Rice'            => ['400'],
        'Squada One'            => ['400'],
        'Stardos Stencil'       => ['400', '700'],
        'Uncial Antiqua'        => ['400'],
        'Urbanist'              => ['400', '400i', '700', '700i'],
        'Yeseva One'            => ['400'],
        'Zilla Slab'            => ['400', '400i', '700', '700i'],
    ];

    $serif_fonts = [
        'Arvo', 'Averia Serif Libre', 'Besley', 'Cinzel Decorative', 'Corben',
        'Crimson Text', 'Droid Serif', 'EB Garamond', 'Libre Baskerville',
        'Lora', 'Martel', 'Merriweather', 'Noto Serif', 'Playfair Display',
        'PT Serif', 'Roboto Slab', 'Uncial Antiqua', 'Zilla Slab',
    ];

    $catalog = [];
    foreach ($fonts as $family => $variants) {
        $catalog[$family] = [
            'family'   => $family,
            'label'    => $family,
            'variants' => $variants,
            'fallback' => in_array($family, $serif_fonts, true) ? 'serif' : 'sans-serif',
        ];
    }

    return apply_filters('modfarm_font_library', $catalog);
}

function modfarm_font_family_from_setting(string $value): string {
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $family = trim((string) strtok($value, ','), " \t\n\r\0\x0B\"'");
    $aliases = [
        'Babas Neue' => 'Bebas Neue',
        'Spicy Rise' => 'Spicy Rice',
    ];

    return $aliases[$family] ?? $family;
}

function modfarm_font_css_value(string $family): string {
    $catalog = modfarm_font_library();
    if (!isset($catalog[$family])) {
        return $family;
    }

    return "'" . $family . "', " . $catalog[$family]['fallback'];
}

function modfarm_effective_font_family(string $value, string $default = 'Source Sans Pro'): string {
    $family = modfarm_font_family_from_setting($value);
    return $family !== '' ? $family : $default;
}

function modfarm_google_font_family_query(array $font): string {
    $normal = [];
    $italic = [];

    foreach ($font['variants'] as $variant) {
        if (substr($variant, -1) === 'i') {
            $italic[] = (int) substr($variant, 0, -1);
        } else {
            $normal[] = (int) $variant;
        }
    }

    sort($normal);
    sort($italic);
    $family = str_replace(' ', '+', $font['family']);

    if (!$italic) {
        return $family . ':wght@' . implode(';', array_unique($normal));
    }

    $tuples = [];
    foreach (array_unique($normal) as $weight) {
        $tuples[] = '0,' . $weight;
    }
    foreach (array_unique($italic) as $weight) {
        $tuples[] = '1,' . $weight;
    }

    return $family . ':ital,wght@' . implode(';', $tuples);
}
