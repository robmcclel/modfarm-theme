<?php
if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/blocks/shared/author-social-links.php';
$creator_credit_render = get_template_directory() . '/blocks/creator-credit/render.php';
if (file_exists($creator_credit_render)) {
  require_once $creator_credit_render;
}

function modfarm_interview_qa_allowed_question_tag($tag): string {
  $tag = strtolower((string) $tag);
  return in_array($tag, ['h2', 'h3', 'h4', 'p'], true) ? $tag : 'h3';
}

function modfarm_interview_qa_items($items): array {
  if (!is_array($items)) {
    return [];
  }

  $clean = [];
  foreach ($items as $item) {
    if (!is_array($item)) {
      continue;
    }

    $question = trim((string) ($item['question'] ?? ''));
    $answer = trim((string) ($item['answer'] ?? ''));

    if (trim(wp_strip_all_tags($question)) === '' && trim(wp_strip_all_tags($answer)) === '') {
      continue;
    }

    $clean[] = [
      'question' => wp_kses_post($question),
      'answer' => wp_kses_post($answer),
    ];
  }

  return $clean;
}

function modfarm_interview_qa_author_term(int $author_id, string $preferred_taxonomy = ''): ?WP_Term {
  if ($author_id <= 0) {
    return null;
  }

  $taxonomies = function_exists('modfarm_author_taxonomies') ? modfarm_author_taxonomies() : ['book-author', 'book-authors'];
  if ($preferred_taxonomy !== '' && in_array($preferred_taxonomy, $taxonomies, true)) {
    $taxonomies = array_values(array_unique(array_merge([$preferred_taxonomy], $taxonomies)));
  }

  foreach ($taxonomies as $taxonomy) {
    $term = get_term($author_id, $taxonomy);
    if ($term instanceof WP_Term) {
      return $term;
    }
  }

  return null;
}

function modfarm_interview_qa_author_image_url(WP_Term $term): string {
  $decode = function ($raw) {
    if (is_array($raw)) {
      return $raw;
    }
    if (is_string($raw) && ($raw[0] === '{' || $raw[0] === '[')) {
      $decoded = json_decode($raw, true);
      return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
    return null;
  };

  $meta_keys = [
    'archive_default_image',
    'archive_default_image_id',
    'archive_display_default',
    'archive_image_id',
    'term_image_id',
    'author_image_id',
    'author_photo_id',
    'profile_image_id',
    'thumbnail_id',
    'image_id',
    '_thumbnail_id',
  ];

  foreach ($meta_keys as $key) {
    $raw = get_term_meta($term->term_id, $key, true);
    if (!$raw) {
      continue;
    }

    if (is_string($raw) && stripos($raw, 'http') === 0) {
      return esc_url_raw($raw);
    }

    $id = absint(get_term_meta($term->term_id, $key, true));
    if ($id > 0) {
      $url = wp_get_attachment_image_url($id, 'medium');
      if ($url) {
        return $url;
      }
    }

    $data = $decode($raw);
    if (is_array($data)) {
      foreach (['id', 'ID', 'attachment_id', 'image_id'] as $id_key) {
        if (!empty($data[$id_key]) && is_numeric($data[$id_key])) {
          $url = wp_get_attachment_image_url((int) $data[$id_key], 'medium');
          if ($url) {
            return $url;
          }
        }
      }

      foreach (['url', 'src'] as $url_key) {
        if (!empty($data[$url_key])) {
          return esc_url_raw((string) $data[$url_key]);
        }
      }

      if (!empty($data['sizes']['medium'])) {
        return esc_url_raw((string) $data['sizes']['medium']);
      }
    }
  }

  foreach (['author_image', 'author_photo', 'profile_image', 'image'] as $key) {
    $url = esc_url_raw((string) get_term_meta($term->term_id, $key, true));
    if ($url !== '') {
      return $url;
    }
  }

  return '';
}

function modfarm_interview_qa_render_author_profile(WP_Term $author, array $attributes): string {
  if (function_exists('modfarm_render_creator_credit_block')) {
    return modfarm_render_creator_credit_block([
      'taxonomy' => $author->taxonomy,
      'termId' => (int) $author->term_id,
      'layout' => 'auto',
      'imageShape' => 'circle',
      'imgSize' => 240,
      'linkToArchive' => true,
      'showDescription' => !empty($attributes['showAuthorBio']),
      'showSocialLinks' => !empty($attributes['showAuthorSocials']),
      'socialIconSize' => 28,
      'socialGap' => 10,
      'socialColorMode' => 'native',
      'socialOpenInNewTab' => true,
      'hideIfEmpty' => true,
    ]);
  }

  return '';
}

function modfarm_interview_qa_author_schema(WP_Term $term): array {
  $same_as = [];
  $socials = function_exists('modfarm_get_author_socials') ? modfarm_get_author_socials((int) $term->term_id) : [];
  foreach ($socials as $social) {
    $url = trim((string) ($social['url'] ?? ''));
    if ($url !== '') {
      $same_as[] = esc_url_raw($url);
    }
  }

  $author = [
    '@type' => 'Person',
    'name' => wp_strip_all_tags($term->name),
  ];

  $description = trim((string) get_term_meta($term->term_id, 'author_short', true));
  if ($description === '') {
    $description = trim(wp_strip_all_tags((string) $term->description));
  }
  if ($description !== '') {
    $author['description'] = $description;
  }

  $image = modfarm_interview_qa_author_image_url($term);
  if ($image !== '') {
    $author['image'] = $image;
  }

  $url = get_term_link($term);
  if (!is_wp_error($url)) {
    $author['url'] = $url;
  }

  if (!empty($same_as)) {
    $author['sameAs'] = array_values(array_unique($same_as));
  }

  return $author;
}

function modfarm_interview_qa_structured_data(array $items, ?WP_Term $author): string {
  if (empty($items)) {
    return '';
  }

  $schema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [],
  ];

  if ($author instanceof WP_Term) {
    $schema['about'] = modfarm_interview_qa_author_schema($author);
  }

  foreach ($items as $item) {
    $question = trim(wp_strip_all_tags((string) $item['question']));
    $answer = trim(wp_strip_all_tags((string) $item['answer']));
    if ($question === '' || $answer === '') {
      continue;
    }

    $schema['mainEntity'][] = [
      '@type' => 'Question',
      'name' => $question,
      'acceptedAnswer' => [
        '@type' => 'Answer',
        'text' => $answer,
      ],
    ];
  }

  if (empty($schema['mainEntity'])) {
    return '';
  }

  return '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

function modfarm_render_interview_qa_block($attributes = []) {
  $a = wp_parse_args($attributes, [
    'items' => [],
    'authorId' => 0,
    'authorTaxonomy' => 'book-author',
    'showAuthorProfile' => false,
    'showAuthorImage' => true,
    'showAuthorBio' => true,
    'showAuthorSocials' => true,
    'emitStructuredData' => true,
    'heading' => '',
    'questionTag' => 'h3',
    'showQuestionMarker' => true,
    'colorMode' => 'inherit',
    'accentColor' => '',
    'questionBgColor' => '',
    'markerTextColor' => '',
  ]);

  $items = modfarm_interview_qa_items($a['items']);
  $author = modfarm_interview_qa_author_term(absint($a['authorId']), sanitize_key((string) $a['authorTaxonomy']));
  $question_tag = modfarm_interview_qa_allowed_question_tag($a['questionTag']);
  $style_vars = [];

  if (($a['colorMode'] ?? 'inherit') === 'custom') {
    $custom_colors = [
      '--mf-interview-accent' => sanitize_hex_color((string) $a['accentColor']),
      '--mf-interview-question-bg' => sanitize_hex_color((string) $a['questionBgColor']),
      '--mf-interview-marker-text' => sanitize_hex_color((string) $a['markerTextColor']),
    ];

    foreach ($custom_colors as $property => $color) {
      if ($color) {
        $style_vars[] = $property . ':' . $color;
      }
    }
  }

  if (empty($items)) {
    return current_user_can('edit_posts')
      ? '<div class="mf-interview-qa mf-interview-qa--empty">Interview Q&A: add at least one question and answer pair.</div>'
      : '';
  }

  $profile = '';
  if (!empty($a['showAuthorProfile']) && $author instanceof WP_Term) {
    $profile = modfarm_interview_qa_render_author_profile($author, $a);
  }

  ob_start();
  ?>
  <section class="mf-interview-qa" data-mf-block="interview-qa"<?php echo $author instanceof WP_Term ? ' data-mf-author-id="' . esc_attr((string) $author->term_id) . '"' : ''; ?> data-mf-schema="FAQPage"<?php echo !empty($style_vars) ? ' style="' . esc_attr(implode(';', $style_vars)) . '"' : ''; ?>>
    <?php if (trim((string) $a['heading']) !== '') : ?>
      <h2 class="mf-interview-qa__heading"><?php echo esc_html((string) $a['heading']); ?></h2>
    <?php endif; ?>

    <?php echo $profile; ?>

    <div class="mf-interview-qa__items">
      <?php foreach ($items as $item) : ?>
        <article class="mf-interview-qa__item">
          <div class="mf-interview-qa__question-row<?php echo empty($a['showQuestionMarker']) ? ' mf-interview-qa__question-row--no-marker' : ''; ?>">
            <?php if (!empty($a['showQuestionMarker'])) : ?>
              <span class="mf-interview-qa__question-marker" aria-hidden="true">?</span>
            <?php endif; ?>
            <<?php echo tag_escape($question_tag); ?> class="mf-interview-qa__question"><?php echo wp_kses_post($item['question']); ?></<?php echo tag_escape($question_tag); ?>>
          </div>
          <div class="mf-interview-qa__answer"><?php echo wp_kses_post($item['answer']); ?></div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php

  $html = trim(ob_get_clean());
  if (!empty($a['emitStructuredData'])) {
    $html .= "\n" . modfarm_interview_qa_structured_data($items, $author);
  }

  return $html;
}
