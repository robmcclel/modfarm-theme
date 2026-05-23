<?php
return [
    'slug'        => 'modfarm/archive-header-hero-description',
    'title'       => 'Archive Header Hero Description',
    'description' => 'Archive header with navigation, hero image, title, and archive description.',
    'categories'  => [ 'modfarm-archive-header' ],
    'keywords'    => [ 'archive', 'hero', 'description', 'collection' ],
    'content'     => '
<!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull">
<!-- wp:modfarm/navigation-menu {"leftMenu":2,"align":"full"} /-->

<!-- wp:modfarm/hero-cover {"termMetaKey":"archive_hero_image","minHeight":420,"dimRatio":35,"contentAlign":"center","contentMaxWidth":"900px"} -->
<!-- wp:query-title {"type":"archive","textAlign":"center","fontSize":"xlarge","showPrefix":false} /-->
<!-- wp:modfarm/archive-description {"alignText":"center"} /-->
<!-- /wp:modfarm/hero-cover -->
</div>
<!-- /wp:group -->
',
];
