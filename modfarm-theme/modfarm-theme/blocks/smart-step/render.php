<?php
if (!defined('ABSPATH')) exit;

function modfarm_smart_step_checkout_complete() {
    $status = isset($_GET['mf_store_checkout'])
        ? sanitize_key(wp_unslash($_GET['mf_store_checkout']))
        : '';
    $session_id = isset($_GET['session_id'])
        ? sanitize_text_field(wp_unslash($_GET['session_id']))
        : '';

    $complete = is_user_logged_in()
        && $status === 'success'
        && (bool) preg_match('/^cs_[A-Za-z0-9_]+$/', $session_id);

    return (bool) apply_filters('modfarm_smart_sequence_checkout_complete', $complete, $status, $session_id);
}

function modfarm_smart_step_condition_met($condition) {
    switch (sanitize_key((string) $condition)) {
        case 'always':
            return true;
        case 'logged_in':
            return is_user_logged_in();
        case 'checkout_complete':
            return modfarm_smart_step_checkout_complete();
        case 'never':
        default:
            return false;
    }
}

function modfarm_render_smart_step_block($attributes, $content, $block) {
    $attributes = wp_parse_args($attributes, [
        'stepNumber' => 1,
        'title' => __('Step', 'modfarm'),
        'unlockCondition' => 'always',
        'completionCondition' => 'never',
        'lockedMessage' => __('Complete the previous step to continue.', 'modfarm'),
    ]);

    $step_number = max(1, absint($attributes['stepNumber']));
    $title = sanitize_text_field((string) $attributes['title']);
    $title = $title !== '' ? $title : sprintf(__('Step %d', 'modfarm'), $step_number);
    $unlocked = modfarm_smart_step_condition_met($attributes['unlockCondition']);
    $complete = $unlocked && modfarm_smart_step_condition_met($attributes['completionCondition']);
    $state = !$unlocked ? 'locked' : ($complete ? 'complete' : 'active');
    $status_label = $state === 'complete' ? __('Complete', 'modfarm') : '';

    ob_start();
    ?>
    <section class="mf-smart-step is-<?php echo esc_attr($state); ?>" data-sequence-state="<?php echo esc_attr($state); ?>">
      <?php if ($unlocked) : ?>
        <details<?php echo $state === 'active' ? ' open' : ''; ?>>
          <summary class="mf-smart-step__summary">
            <span class="mf-smart-step__number"><?php echo esc_html($step_number); ?></span>
            <span class="mf-smart-step__heading"><small><?php echo esc_html(sprintf(__('Step %d', 'modfarm'), $step_number)); ?></small><strong><?php echo esc_html($title); ?></strong></span>
            <?php if ($status_label !== '') : ?><span class="mf-smart-step__status"><?php echo esc_html($status_label); ?></span><?php endif; ?>
          </summary>
          <div class="mf-smart-step__content"><?php echo $content; ?></div>
        </details>
      <?php else : ?>
        <div class="mf-smart-step__summary" aria-disabled="true">
          <span class="mf-smart-step__number"><?php echo esc_html($step_number); ?></span>
          <span class="mf-smart-step__heading"><small><?php echo esc_html(sprintf(__('Step %d', 'modfarm'), $step_number)); ?></small><strong><?php echo esc_html($title); ?></strong></span>
        </div>
        <?php if (!empty($attributes['lockedMessage'])) : ?>
          <p class="mf-smart-step__locked-message"><?php echo esc_html($attributes['lockedMessage']); ?></p>
        <?php endif; ?>
      <?php endif; ?>
    </section>
    <?php
    return trim(ob_get_clean());
}
