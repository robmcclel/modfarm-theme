<?php
return [
    'slug'        => 'modfarm/archive-header-basic-left',
    'title'       => 'Archive Header Basic Left',
    'description' => '',
    'categories'  => [ 'modfarm-archive-header' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull">
    <!-- wp:modfarm/navigation-menu {"leftMenu":7,"align":"full"} /-->

    <!-- wp:group {"style":{"spacing":{"padding":{"top":"30px","bottom":"30px"}}},"layout":{"type":"constrained"}} -->
    <div class="wp-block-group" style="padding-top:30px;padding-bottom:30px">
        <!-- wp:query-title {"type":"archive","textAlign":"left","fontSize":"xlarge", "showPrefix":false} /-->
    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->
',
];