<?php

return [
    'title'       => 'Footer: Two Row (Dynamic)',
    'slug'        => 'modfarm/footer-two-row-layout',
    'categories'  => ['modfarm-footer'],
    'description' => 'Inserts the dynamic Footer Two Row block.',
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
    <!-- wp:modfarm/footer-two-row {"menu":"primary","showSocial":true} /-->
</div>
<!-- /wp:group -->
',
];