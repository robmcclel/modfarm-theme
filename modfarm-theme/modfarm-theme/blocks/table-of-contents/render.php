<?php
defined('ABSPATH') || exit;

function modfarm_render_table_of_contents_block( $attributes, $content, $block ) {
    $args = wp_parse_args( (array) $attributes, [
        'includeH2' => true,
        'includeH3' => true,
        'includeH4' => false,
        'slugCase'  => 'lower',
        'columns'   => 1,
        'align'     => 'left',
        'listStyle' => 'plain',
        'title'     => '',
    ]);

    $post_id = get_the_ID();
    if (!$post_id) return '';

    $raw = get_post_field('post_content', $post_id);
    if (empty($raw)) return '';

    $allowed = [];
    if (!empty($args['includeH2'])) $allowed[] = 2;
    if (!empty($args['includeH3'])) $allowed[] = 3;
    if (!empty($args['includeH4'])) $allowed[] = 4;
    if (empty($allowed)) return '';

    // Parse headings (for labels & rough slugs)
    $blocks = parse_blocks($raw);
    $items  = [];
    $stack  = $blocks;

    while (!empty($stack)) {
        $b = array_shift($stack);
        if (!is_array($b)) continue;

        if (!empty($b['blockName']) && $b['blockName'] === 'core/heading') {
            $level = isset($b['attrs']['level']) ? (int)$b['attrs']['level'] : 2;
            if (in_array($level, $allowed, true)) {
                $html = isset($b['innerHTML']) && $b['innerHTML'] !== ''
                    ? $b['innerHTML']
                    : (!empty($b['innerContent']) && is_array($b['innerContent'])
                        ? implode('', array_filter($b['innerContent'])) : '');
                $text = trim(wp_strip_all_tags($html));
                if ($text !== '') {
                    $slug = !empty($b['attrs']['anchor'])
                        ? sanitize_title($b['attrs']['anchor'])
                        : sanitize_title($text);
                    if ($args['slugCase'] === 'lower') $slug = strtolower($slug);
                    $items[] = ['text'=>$text, 'slug'=>$slug, 'level'=>$level];
                }
            }
        }
        if (!empty($b['innerBlocks']) && is_array($b['innerBlocks'])) {
            foreach ($b['innerBlocks'] as $child) $stack[] = $child;
        }
    }

    if (empty($items)) return '';

    $columns = max(1, min(3, (int)$args['columns']));
    $align   = in_array($args['align'], ['left','center','right'], true) ? $args['align'] : 'left';
    $list    = in_array($args['listStyle'], ['plain','bulleted','numbered'], true) ? $args['listStyle'] : 'plain';
    
    $wrapper_classes = [
        'mftoc',
        'mftoc--cols-' . $columns,
        'mftoc--align-' . $align,
        'mftoc--' . $list,
    ];
    
    $levels_attr = implode(',', $allowed); // "2,3,4"
    
    /** ✅ NEW: accessibility + wrapper attrs **/
    $has_title  = trim((string)$args['title']) !== '';
    $title_id   = 'mftoc-title-' . uniqid();
    $label_fallback = __('Table of Contents','modfarm');
    
    // Let WP print typography/link-color/etc. styles/classes for this block instance
    $wrapper_attrs = get_block_wrapper_attributes([
        'class' => implode(' ', $wrapper_classes),
    ]);


    ob_start(); ?>
    <nav class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>"
         aria-label="<?php esc_attr_e('Table of Contents','modfarm'); ?>"
         data-mftoc-levels="<?php echo esc_attr($levels_attr); ?>"
         data-mftoc-case="<?php echo esc_attr($args['slugCase']); ?>">
      <?php if (trim((string)$args['title']) !== ''): ?>
        <div class="mftoc-title"><?php echo esc_html($args['title']); ?></div>
      <?php endif; ?>
      <ul class="mftoc-list" data-anchor-count="<?php echo count($items); ?>">
        <?php foreach ($items as $it): ?>
          <li class="mftoc-item mftoc-l<?php echo (int)$it['level']; ?>">
            <a data-mftoc-text="<?php echo esc_attr($it['text']); ?>"
               data-mftoc-slug="<?php echo esc_attr($it['slug']); ?>"
               href="#<?php echo esc_attr($it['slug']); ?>"><?php echo esc_html($it['text']); ?></a>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <?php
    return ob_get_clean();
}