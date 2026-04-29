<?php
return [
    'slug'        => 'modfarm/book-header-basic-center',
    'title'       => 'Book Header Basic Center',
    'description' => '',
    'categories'  => [ 'modfarm-book-header' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"align":"full","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull"><!-- wp:modfarm/navigation-menu {"leftMenu":7,"align":"full"} /-->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|70","left":"0","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--60);padding-left:0"><!-- wp:post-title {"textAlign":"center","level":1} /-->

<!-- wp:modfarm/book-page-series {"alignment":"center","fontSize":24} /-->

<!-- wp:modfarm/book-author-credit {"alignment":"center","fontSize":20} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
',
];
