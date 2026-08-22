<?php
if (!defined('ABSPATH')) exit;

/**
 * Smart Sequence is an editorial container. Its Smart Step children own the
 * state-aware rendering, so the parent preserves ordinary InnerBlocks content.
 */
function modfarm_render_smart_sequence_block($attributes, $content, $block) {
    if (isset($attributes['showProgress']) && !$attributes['showProgress']) {
        return $content;
    }

    $steps = [];
    $inner_blocks = $block instanceof WP_Block && is_array($block->parsed_block['innerBlocks'] ?? null)
        ? $block->parsed_block['innerBlocks']
        : [];

    foreach ($inner_blocks as $index => $inner_block) {
        if (($inner_block['blockName'] ?? '') !== 'modfarm/smart-step') continue;
        $step_attributes = is_array($inner_block['attrs'] ?? null) ? $inner_block['attrs'] : [];
        $number = max(1, absint($step_attributes['stepNumber'] ?? ($index + 1)));
        $title = sanitize_text_field((string) ($step_attributes['title'] ?? ''));
        $title = $title !== '' ? $title : sprintf(__('Step %d', 'modfarm'), $number);
        $unlocked = function_exists('modfarm_smart_step_condition_met')
            ? modfarm_smart_step_condition_met($step_attributes['unlockCondition'] ?? 'always')
            : $index === 0;
        $complete = $unlocked
            && function_exists('modfarm_smart_step_condition_met')
            && modfarm_smart_step_condition_met($step_attributes['completionCondition'] ?? 'never');
        $steps[] = [
            'number' => $number,
            'title' => $title,
            'state' => !$unlocked ? 'locked' : ($complete ? 'complete' : 'active'),
        ];
    }

    if (!$steps) return $content;

    ob_start();
    ?>
    <ol class="mf-smart-sequence__progress" aria-label="<?php esc_attr_e('Sequence steps', 'modfarm'); ?>">
      <?php foreach ($steps as $step) : ?>
        <li class="is-<?php echo esc_attr($step['state']); ?>">
          <span><?php echo esc_html($step['number']); ?></span>
          <strong><?php echo esc_html($step['title']); ?></strong>
        </li>
      <?php endforeach; ?>
    </ol>
    <?php
    $progress = trim(ob_get_clean());

    if (preg_match('/^<div\b[^>]*>/', $content)) {
        return preg_replace_callback(
            '/^(<div\b[^>]*>)/',
            static function ($matches) use ($progress) {
                return $matches[1] . $progress;
            },
            $content,
            1
        );
    }

    return '<div class="wp-block-modfarm-smart-sequence mf-smart-sequence">' . $progress . $content . '</div>';
}
