<?php
return [
    'slug'        => 'modfarm/page-author-index-center-heading',
    'title'       => 'Page Author Index Center Heading',
    'description' => '',
    'categories'  => [ 'modfarm-page-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center","fontSize":"xlarge"} -->
<h2 class="wp-block-heading has-text-align-center has-xlarge-font-size">Author Index</h2>
<!-- /wp:heading -->

<!-- wp:spacer {"height":"30px"} -->
<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:modfarm/taxonomy-grid {"taxonomy":"book-author","showTOC":false,"shape":"circle"} /--></div>
<!-- /wp:group -->
',
];
