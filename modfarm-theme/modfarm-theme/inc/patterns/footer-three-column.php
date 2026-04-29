<?php

return [
    'title'       => 'Three-Column Footer (Dynamic)',
    'slug'        => 'modfarm/footer-three-column-layout',
    'categories'  => ['modfarm-footer'],
    'description' => 'Full-width footer layout using the dynamic ModFarm three-column footer block.',
    'content'     => '
<!-- wp:group {
    "align": "full",
    "backgroundColor": "modfarm-footer-bg",
    "textColor": "modfarm-footer-text",
    "layout": { "type": "constrained" },
    "style": {
        "spacing": {
            "padding": { "top": "2rem", "bottom": "2rem" }
        }
    }
} -->
<div class="wp-block-group alignfull has-modfarm-footer-bg-background-color has-modfarm-footer-text-color has-text-color has-background" style="padding-top:2rem;padding-bottom:2rem">
    <!-- wp:modfarm/footer-three-column /-->
</div>
<!-- /wp:group -->
',
];