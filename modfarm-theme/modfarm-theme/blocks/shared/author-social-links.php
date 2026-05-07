<?php
/**
 * Shared helpers for rendering author social/profile links.
 */

if (!defined('ABSPATH')) {
  exit;
}

if (!function_exists('modfarm_author_social_platform_map')) {
  function modfarm_author_social_platform_map(): array {
    return [
      'amazon'        => ['label' => 'Amazon',        'color' => '#ff9900', 'glyph' => 'a'],
      'amazon-author' => ['label' => 'Amazon Author', 'color' => '#ff9900', 'glyph' => 'a'],
      'bluesky'       => ['label' => 'Bluesky',       'color' => '#1185fe', 'glyph' => 'b'],
      'bookbub'       => ['label' => 'BookBub',       'color' => '#f15a24', 'glyph' => 'b'],
      'discord'       => ['label' => 'Discord',       'color' => '#5865f2', 'glyph' => 'd'],
      'facebook'      => ['label' => 'Facebook',      'color' => '#1877f2', 'glyph' => 'f'],
      'goodreads'     => ['label' => 'Goodreads',     'color' => '#553b08', 'glyph' => 'g'],
      'instagram'     => ['label' => 'Instagram',     'color' => '#e4405f', 'glyph' => 'i'],
      'kickstarter'   => ['label' => 'Kickstarter',   'color' => '#05ce78', 'glyph' => 'k'],
      'patreon'       => ['label' => 'Patreon',       'color' => '#ff424d', 'glyph' => 'p'],
      'ream'          => ['label' => 'Ream',          'color' => '#7c3aed', 'glyph' => 'r'],
      'royalroad'     => ['label' => 'RoyalRoad',     'color' => '#1d4ed8', 'glyph' => 'r'],
      'substack'      => ['label' => 'Substack',      'color' => '#ff6719', 'glyph' => 's'],
      'tiktok'        => ['label' => 'TikTok',        'color' => '#111111', 'glyph' => 't'],
      'twitter'       => ['label' => 'X',             'color' => '#111111', 'glyph' => 'x'],
      'twitter-x'     => ['label' => 'X',             'color' => '#111111', 'glyph' => 'x'],
      'website'       => ['label' => 'Website',       'color' => '#2563eb', 'glyph' => 'w'],
      'x'             => ['label' => 'X',             'color' => '#111111', 'glyph' => 'x'],
      'youtube'       => ['label' => 'YouTube',       'color' => '#ff0000', 'glyph' => 'play'],
    ];
  }
}

if (!function_exists('modfarm_author_social_normalize_key')) {
  function modfarm_author_social_normalize_key(string $key, string $label = ''): string {
    $slug = sanitize_title($key !== '' ? $key : $label);
    $aliases = [
      'amazon-author-page' => 'amazon-author',
      'amazon-authors'     => 'amazon-author',
      'blue-sky'           => 'bluesky',
      'book-bub'           => 'bookbub',
      'twitter-x'          => 'twitter-x',
      'twitterx'           => 'twitter-x',
      'url'                => 'website',
      'web'                => 'website',
    ];
    return $aliases[$slug] ?? $slug;
  }
}

if (!function_exists('modfarm_author_social_icon_svg')) {
  function modfarm_author_social_icon_svg(string $key, string $label, string $glyph): string {
    $title = esc_html($label);
    $key = modfarm_author_social_normalize_key($key, $label);

    $paths = [
      'facebook'  => '<path d="M15.1 8.2h2.2V4.5c-.4-.1-1.7-.2-3.2-.2-3.2 0-5.3 1.9-5.3 5.5v3.1H5.3v4.1h3.5v10.6h4.3V17h3.5l.6-4.1h-4.1v-2.7c0-1.2.3-2 2-2z" />',
      'instagram' => '<rect x="5.4" y="5.4" width="21.2" height="21.2" rx="6.1" ry="6.1" fill="none" stroke="currentColor" stroke-width="2.6" /><circle cx="16" cy="16" r="5.1" fill="none" stroke="currentColor" stroke-width="2.6" /><circle cx="23.1" cy="8.9" r="1.7" />',
      'youtube'   => '<path d="M28.7 10.3a3.4 3.4 0 0 0-2.4-2.4C24.2 7.3 16 7.3 16 7.3s-8.2 0-10.3.6a3.4 3.4 0 0 0-2.4 2.4A35.8 35.8 0 0 0 2.7 16a35.8 35.8 0 0 0 .6 5.7 3.4 3.4 0 0 0 2.4 2.4c2.1.6 10.3.6 10.3.6s8.2 0 10.3-.6a3.4 3.4 0 0 0 2.4-2.4 35.8 35.8 0 0 0 .6-5.7 35.8 35.8 0 0 0-.6-5.7zM13.3 20.1v-8.2l7.1 4.1z" />',
      'x'         => '<path d="M18.8 14.1 27 4.6h-2l-7.1 8.2-5.7-8.2H5.6l8.6 12.5-8.6 10h2l7.5-8.7 6 8.7h6.6zM16.2 17.2l-.9-1.3L8.4 6.1h2.9l5.6 8 .9 1.3 7.3 10.4h-2.9z" />',
      'twitter-x' => '<path d="M18.8 14.1 27 4.6h-2l-7.1 8.2-5.7-8.2H5.6l8.6 12.5-8.6 10h2l7.5-8.7 6 8.7h6.6zM16.2 17.2l-.9-1.3L8.4 6.1h2.9l5.6 8 .9 1.3 7.3 10.4h-2.9z" />',
      'discord'   => '<path d="M11.2 10.4c1.7-.5 3.2-.7 4.8-.7s3.1.2 4.8.7c1.8 2.6 2.7 5.5 2.4 8.9-1.9 1.4-3.7 2.2-5.5 2.6l-.8-1.7c.9-.3 1.7-.7 2.5-1.2-2.1 1-4.7 1-6.8 0 .8.5 1.6.9 2.5 1.2l-.8 1.7c-1.8-.4-3.7-1.2-5.5-2.6-.3-3.4.6-6.3 2.4-8.9zm1.7 5.5c0 1 .7 1.8 1.6 1.8s1.6-.8 1.6-1.8-.7-1.8-1.6-1.8-1.6.8-1.6 1.8zm5 0c0 1 .7 1.8 1.6 1.8s1.6-.8 1.6-1.8-.7-1.8-1.6-1.8-1.6.8-1.6 1.8z" />',
      'bluesky'   => '<path d="M10.2 7.1c2.3 1.7 4.8 5.1 5.8 6.9 1-1.8 3.5-5.2 5.8-6.9 1.7-1.2 4.4-2.2 4.4.8 0 .6-.3 4.9-.6 5.6-.8 2.7-3.5 3.4-5.9 3 4.2.7 5.3 3.2 3 5.6-4.4 4.6-6.4-1.1-6.8-2.6-.1-.3-.1-.4-.2-.4s-.1.1-.2.4c-.4 1.5-2.4 7.2-6.8 2.6-2.3-2.4-1.2-4.9 3-5.6-2.4.4-5.1-.3-5.9-3-.2-.7-.6-5-.6-5.6 0-3 2.7-2 4.4-.8z" />',
      'patreon'   => '<path d="M6.3 5h4.6v22H6.3zM20.7 5c3.8 0 6.9 3.1 6.9 6.9s-3.1 6.9-6.9 6.9-6.9-3.1-6.9-6.9S16.9 5 20.7 5z" />',
      'tiktok'    => '<path d="M20.2 4.5c.5 3.5 2.4 5.6 5.6 5.8v4.1c-1.9.2-3.6-.4-5.5-1.5v7.8c0 9.9-10.8 13-15.2 5.9-2.8-4.6-1.1-12.7 7.9-13v4.3c-.6.1-1.3.3-1.9.5-1.8.6-2.8 1.7-2.5 3.8.5 4 7.8 5.2 7.2-2.6V4.5z" />',
      'website'   => '<path d="M16 3.8a12.2 12.2 0 1 0 0 24.4 12.2 12.2 0 0 0 0-24.4zm8.5 11h-4.4a18 18 0 0 0-1.4-6 9.1 9.1 0 0 1 5.8 6zm-8.5-8c.8 1.2 1.7 3.8 2 8h-4c.3-4.2 1.2-6.8 2-8zm-8.5 11H12a20.8 20.8 0 0 0 0 4.4H7.5a9.5 9.5 0 0 1 0-4.4zm0-3a9.1 9.1 0 0 1 5.8-6 18 18 0 0 0-1.4 6zm8.5 10.4c-.8-1.2-1.7-3.8-2-8h4c-.3 4.2-1.2 6.8-2 8zm2.7-1.9a18 18 0 0 0 1.4-6h4.4a9.1 9.1 0 0 1-5.8 6z" />',
    ];

    $path = $paths[$key] ?? '';
    if ($path === '') {
      $text = $glyph === 'play' ? '' : mb_strtoupper(mb_substr($glyph ?: $label, 0, 1));
      $path = '<text x="16" y="21" text-anchor="middle" font-size="17" font-family="Arial, Helvetica, sans-serif" font-weight="700" fill="currentColor">' . esc_html($text) . '</text>';
    }

    return '<svg class="mfas-icon__svg mfas-icon__svg--' . esc_attr($key) . '" viewBox="0 0 32 32" aria-hidden="true" focusable="false" role="img"><title>' . $title . '</title>' . $path . '</svg>';
  }
}

if (!function_exists('modfarm_render_author_social_links')) {
  function modfarm_render_author_social_links(WP_Term $term, array $attributes = []): string {
    $a = wp_parse_args($attributes, [
      'align'         => 'left',
      'iconSize'      => 32,
      'gap'           => 14,
      'colorMode'     => 'native',
      'monotoneColor' => '',
      'openInNewTab'  => true,
      'hideIfEmpty'   => false,
    ]);

    $socials = function_exists('modfarm_get_author_socials')
      ? modfarm_get_author_socials((int) $term->term_id)
      : [];

    if (empty($socials)) {
      if (current_user_can('edit_posts') && empty($a['hideIfEmpty'])) {
        return '<div class="mfas-socials mfas-socials--empty"><em>Author Social Links:</em> No links found for ' . esc_html($term->name) . '.</div>';
      }
      return '';
    }

    $align = in_array($a['align'], ['left', 'center', 'right'], true) ? $a['align'] : 'left';
    $color_mode = in_array($a['colorMode'], ['native', 'monotone'], true) ? $a['colorMode'] : 'native';
    $icon_size = max(12, min(128, (int) $a['iconSize']));
    $gap = max(0, min(64, (int) $a['gap']));
    $map = modfarm_author_social_platform_map();

    $style_vars = [
      '--mfas-icon-size:' . $icon_size . 'px',
      '--mfas-gap:' . $gap . 'px',
    ];
    if ($color_mode === 'monotone' && !empty($a['monotoneColor'])) {
      $style_vars[] = '--mfas-color:' . esc_attr((string) $a['monotoneColor']);
    }

    $target = !empty($a['openInNewTab']) ? ' target="_blank" rel="noopener noreferrer"' : '';

    ob_start(); ?>
    <div class="mfas-socials mfas-socials--align-<?php echo esc_attr($align); ?> mfas-socials--<?php echo esc_attr($color_mode); ?>" style="<?php echo esc_attr(implode(';', $style_vars)); ?>" aria-label="<?php echo esc_attr(sprintf(__('%s links', 'modfarm'), $term->name)); ?>">
      <?php foreach ($socials as $social):
        $label = trim((string) ($social['label'] ?? ''));
        $url = trim((string) ($social['url'] ?? ''));
        if ($label === '' || $url === '') {
          continue;
        }
        $key = modfarm_author_social_normalize_key((string) ($social['key'] ?? ''), $label);
        $platform = $map[$key] ?? ['label' => $label, 'color' => '#64748b', 'glyph' => mb_substr($label, 0, 1)];
        $icon_label = $platform['label'] ?: $label;
        $native_style = $color_mode === 'native' ? ' style="' . esc_attr('--mfas-platform-color:' . $platform['color']) . '"' : '';
        ?>
        <a class="mfas-icon mfas-icon--<?php echo esc_attr($key); ?>" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr($label); ?>" title="<?php echo esc_attr($label); ?>"<?php echo $target; ?><?php echo $native_style; ?>>
          <?php echo modfarm_author_social_icon_svg($key, $icon_label, (string) $platform['glyph']); ?>
        </a>
      <?php endforeach; ?>
    </div>
    <?php
    return trim(ob_get_clean());
  }
}
