<?php

return [
    'title'       => 'Mega Footer',
    'slug'        => 'modfarm/footer-mega',
    'categories'  => ['modfarm-footer'],
    'description' => 'Two-row mega footer with logo, navigation, social links, and copyright/login.',
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

    <!-- Top Row: Site Logo/Title -->
    <!-- wp:site-logo {"width":80,"align":"center"} /-->
    <!-- wp:site-title {"textAlign":"center","level":0,"style":{"typography":{"fontSize":"1.25rem"}}} /-->

    <!-- Bottom Row: Columns -->
    <!-- wp:columns {"isStackedOnMobile":true} -->
    <div class="wp-block-columns is-stacked-on-mobile">

        <!-- wp:column {"width":"30%"} -->
        <div class="wp-block-column" style="flex-basis:30%">
            <!-- wp:social-links {
                "iconColor":"modfarm-footer-text",
                "iconColorValue":"#ffffff",
                "openInNewTab":true,
                "layout":{"type":"flex","orientation":"horizontal","justifyContent":"left"}
            } /-->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"width":"70%"} -->
        <div class="wp-block-column" style="flex-basis:70%">
            <!-- wp:navigation {"layout":{"type":"flex","justifyContent":"right"}} /-->
            <!-- wp:search {"label":"Search","showLabel":false,"buttonText":"Search","buttonPosition":"button-inside","align":"right"} /-->
            <!-- wp:paragraph {"align":"right","style":{"spacing":{"margin":{"top":"1rem"}}},"fontSize":"small"} -->
            <p class="has-text-align-right has-small-font-size" style="margin-top:1rem">
                [modfarm_footer_login]
            </p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->

    </div>
    <!-- /wp:columns -->

</div>
<!-- /wp:group -->
',
];
