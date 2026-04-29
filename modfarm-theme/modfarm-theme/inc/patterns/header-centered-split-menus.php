<?php

return [
    'title'       => 'Centered Logo or Title with Split Menus',
    'slug'        => 'modfarm/header-centered-split-menus',
    'categories'  => ['modfarm-headers'],
    'description' => 'A responsive header with primary and secondary menus on left and right, and logo or site title centered.',
    'content'     => '
<!-- wp:group {
    "align": "full",
    "layout": { "type": "constrained" },
    "backgroundColor": "modfarm-header-bg",
    "textColor": "modfarm-header-text",
    "style": {
        "spacing": {
            "padding": {
                "top": "1rem",
                "bottom": "1rem"
            }
        }
    },
    "className": "modfarm-header-centered"
} -->
<div class="wp-block-group alignfull has-modfarm-header-bg-background-color has-modfarm-header-text-color has-text-color has-background modfarm-header-centered" style="padding-top:1rem;padding-bottom:1rem">

    <!-- wp:columns {"isStackedOnMobile":false,"verticalAlignment":"center"} -->
    <div class="wp-block-columns are-vertically-aligned-center is-stacked-on-mobile">

        <!-- wp:column {"verticalAlignment":"center"} -->
        <div class="wp-block-column is-vertically-aligned-center">
            <!-- wp:navigation {"layout":{"type":"flex","justifyContent":"left"}} /-->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"verticalAlignment":"center","className":"modfarm-header-logo-column"} -->
        <div class="wp-block-column is-vertically-aligned-center modfarm-header-logo-column" style="flex-grow:0; flex-shrink:0;">
            <!-- wp:site-logo {"width":120,"shouldSyncIcon":true,"className":"is-style-rounded"} /-->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"verticalAlignment":"center"} -->
        <div class="wp-block-column is-vertically-aligned-center">
            <!-- wp:navigation {"layout":{"type":"flex","justifyContent":"right"}} /-->
        </div>
        <!-- /wp:column -->

    </div>
    <!-- /wp:columns -->

</div>
<!-- /wp:group -->
',
];
