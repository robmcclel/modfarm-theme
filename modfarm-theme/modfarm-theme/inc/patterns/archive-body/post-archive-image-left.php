<?php
return [
    'slug'        => 'modfarm/post-archive-image-left',
    'title'       => 'Post Archive Image Left',
    'description' => 'A traditional news archive with thumbnails on the left and story text on the right.',
    'categories'  => [ 'modfarm-archive-body' ],
    'keywords'    => [ 'archive', 'posts', 'news', 'image left', 'traditional', 'list' ],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--20)">
<!-- wp:query {"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"layout":{"type":"constrained"}} -->
<div class="wp-block-query"><!-- wp:post-template -->
<!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|50"},"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}},"border":{"bottom":{"color":"#e5e5e5","width":"1px"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center" style="border-bottom-color:#e5e5e5;border-bottom-width:1px;padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:column {"verticalAlignment":"center","width":"42%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:42%"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"58%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:58%"><!-- wp:post-title {"isLink":true,"level":2,"style":{"typography":{"fontSize":"34px","lineHeight":"1.1","fontStyle":"normal","fontWeight":"700"}}} /-->
<!-- wp:post-date {"style":{"typography":{"fontSize":"14px"}}} /-->
<!-- wp:post-excerpt {"moreText":"Read more","excerptLength":32} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>No posts found.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results -->

<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"space-between"}} -->
<!-- wp:query-pagination-previous /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->
',
];
