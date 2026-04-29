<?php
if (!defined('ABSPATH')) exit;

/**
 * Render a unified book card by locating the canonical template.
 * @param array $card Normalized card array (see template-parts/book/card.php)
 */
function modfarm_render_book_card(array $card): void {
    $template = locate_template(['template-parts/book/card.php']);
    if ($template) {
        // expose $card to the template scope
        include $template;
    }
}