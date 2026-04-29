<?php

return [
    'title'       => 'Simple Centered Footer',
    'slug'        => 'modfarm/footer-simple-centered',
    'categories'  => ['modfarm-footer'],
    'description' => 'A simple, centered footer with site logo/title, navigation, social icons, and login/logout link.',
    'content'     => '
<!-- wp:group {
    "align": "full",
    "backgroundColor": "modfarm-footer-bg",
    "textColor": "modfarm-footer-text",
    "layout": {
        "type": "constrained"
    },
    "style": {
        "spacing": {
            "padding": {
                "top": "2rem",
                "bottom": "2rem"
            }
        }
    }
} -->
<div class="wp-block-group alignfull has-modfarm-footer-bg-background-color has-modfarm-footer-text-color has-text-color has-background" style="padding-top:2rem;padding-bottom:2rem">

    <!-- wp:site-logo {"align":"center"} /-->

    <!-- wp:site-title {"textAlign":"center","level":0,"style":{"typography":{"fontSize":"1.25rem"}}} /-->

    <!-- wp:navigation {"layout":{"type":"flex","justifyContent":"center"}} /-->

    <!-- wp:social-links {"iconColor":"modfarm-footer-text","iconColorValue":"#ffffff","openInNewTab":true,"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"1rem"}}}} /-->

    <!-- wp:paragraph {"align":"center","style":{"spacing":{"margin":{"top":"1rem"}}},"fontSize":"small"} -->
    <p class="has-text-align-center has-small-font-size" style="margin-top:1rem">
        [modfarm_footer_login]
    </p>
    <!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
',
];