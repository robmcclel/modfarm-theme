<?php
/**
 * Shared book option resolvers for listing and presentation blocks.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('modfarm_book_option_normalize_link_source')) {
    function modfarm_book_option_normalize_link_source(string $source): string {
        $source = sanitize_key($source);
        $aliases = [
            'bookpage'  => 'permalink',
            'kindle'    => 'kindle_url',
            'amazon'    => 'kindle_url',
            'paperback' => 'amazon_paper',
            'hardcover' => 'amazon_hard',
            'audible'   => 'audible_url',
            'apple'     => 'ibooks',
            'bn'        => 'nook',
        ];
        return $aliases[$source] ?? $source;
    }
}

if (!function_exists('modfarm_book_option_normalize_cover_source')) {
    function modfarm_book_option_normalize_cover_source(string $source): string {
        $source = sanitize_key($source);
        $aliases = [
            'featured'         => 'featured_image',
            'hero_image'       => 'featured_image',
            'cover_image_flat' => 'cover_ebook',
        ];
        return $aliases[$source] ?? $source;
    }
}

if (!function_exists('modfarm_book_option_value_to_url')) {
    function modfarm_book_option_value_to_url($value): string {
        if (is_numeric($value)) {
            return wp_get_attachment_image_url((int) $value, 'full') ?: '';
        }

        if (is_array($value)) {
            foreach (['ID', 'id', 'attachment_id'] as $key) {
                if (!empty($value[$key]) && is_numeric($value[$key])) {
                    return wp_get_attachment_image_url((int) $value[$key], 'full') ?: '';
                }
            }

            if (!empty($value['url'])) {
                return (string) $value['url'];
            }

            if (!empty($value['sizes']) && is_array($value['sizes'])) {
                foreach (['full', 'large', 'medium_large', 'medium'] as $size) {
                    if (!empty($value['sizes'][$size])) {
                        return (string) $value['sizes'][$size];
                    }
                }
            }

            return '';
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return '';
            }
            if (is_numeric($value)) {
                return wp_get_attachment_image_url((int) $value, 'full') ?: '';
            }
            return $value;
        }

        return '';
    }
}

if (!function_exists('modfarm_book_cover_source_keys')) {
    function modfarm_book_cover_source_keys(): array {
        return [
            'cover_ebook',
            'cover_image_audio',
            'cover_paperback',
            'cover_hardcover',
            'cover_ebook_3d',
            'cover_paperback_3d',
            'cover_hardcover_3d',
            'cover_image_audio_3d',
            'cover_image_composite',
            'cover_image_3d',
            'featured_image',
            'cover_image_flat',
            'hero_image',
        ];
    }
}

if (!function_exists('modfarm_book_cover_url')) {
    function modfarm_book_cover_url(int $book_id, string $source, bool $fallback = true): string {
        $source = modfarm_book_option_normalize_cover_source($source);

        if ($source === 'featured_image') {
            $url = get_the_post_thumbnail_url($book_id, 'full') ?: '';
        } else {
            $valid = modfarm_book_cover_source_keys();
            $key = in_array($source, $valid, true) ? $source : 'cover_ebook';
            $url = modfarm_book_option_value_to_url(get_post_meta($book_id, $key, true));
        }

        if (!$url && $fallback) {
            foreach (modfarm_book_cover_source_keys() as $try) {
                if ($try === 'featured_image') {
                    $url = get_the_post_thumbnail_url($book_id, 'full') ?: '';
                } else {
                    $url = modfarm_book_option_value_to_url(get_post_meta($book_id, $try, true));
                }
                if ($url) {
                    break;
                }
            }
        }

        if ($url && strpos($url, 'm.media-amazon.com') !== false) {
            $url = preg_replace('~\._[A-Z0-9_,-]+(?:_)?\.~', '.', $url);
            $url = preg_replace('~\._[A-Z0-9_,-]+$~', '', $url);
        }

        return $url ?: '';
    }
}

if (!function_exists('modfarm_book_cover_aspect')) {
    function modfarm_book_cover_aspect(string $source): string {
        $source = modfarm_book_option_normalize_cover_source($source);

        if ($source === 'cover_image_audio') {
            return '1 / 1';
        }

        if (in_array($source, ['cover_image_3d', 'cover_ebook_3d', 'cover_paperback_3d', 'cover_hardcover_3d', 'cover_image_audio_3d'], true)) {
            return '4 / 3';
        }

        if ($source === 'cover_image_composite') {
            return '16 / 9';
        }

        return '2 / 3';
    }
}

if (!function_exists('modfarm_book_series_permalink')) {
    function modfarm_book_series_permalink(int $book_id): string {
        $terms = get_the_terms($book_id, 'book-series');
        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        $url = get_term_link($terms[0]);
        return !is_wp_error($url) ? (string) $url : '';
    }
}

if (!function_exists('modfarm_book_link_url')) {
    function modfarm_book_link_url(int $book_id, string $source, string $permalink = ''): string {
        $source = modfarm_book_option_normalize_link_source($source);
        $permalink = $permalink !== '' ? $permalink : (get_permalink($book_id) ?: '');

        if ($source === '' || $source === '__none__') {
            return '';
        }

        if ($source === 'permalink') {
            return $permalink;
        }

        if ($source === 'series_permalink') {
            return modfarm_book_series_permalink($book_id);
        }

        $url = (string) get_post_meta($book_id, $source, true);

        if ($url === '' && $source === 'kindle_url') {
            $url = (string) get_post_meta($book_id, 'amazon_paper', true);
            if ($url === '') {
                $url = (string) get_post_meta($book_id, 'amazon_hard', true);
            }
            if ($url === '') {
                $url = $permalink;
            }
        }

        return $url;
    }
}

if (!function_exists('modfarm_book_link_is_internal')) {
    function modfarm_book_link_is_internal(string $source): bool {
        $source = modfarm_book_option_normalize_link_source($source);
        return in_array($source, ['permalink', 'series_permalink'], true);
    }
}

if (!function_exists('modfarm_book_link_default_label')) {
    function modfarm_book_link_default_label(string $source): string {
        $source = modfarm_book_option_normalize_link_source($source);
        if ($source === 'permalink') {
            return __('See The Book', 'modfarm');
        }
        if ($source === 'series_permalink') {
            return __('See The Full Series', 'modfarm');
        }

        return ucwords(str_replace(['_', 'url'], [' ', ''], $source));
    }
}
