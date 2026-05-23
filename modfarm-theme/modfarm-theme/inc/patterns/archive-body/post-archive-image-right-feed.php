<?php
return [
    'slug'        => 'modfarm/post-archive-image-right-feed',
    'title'       => 'Post Archive Image Right Feed',
    'description' => 'A writer feed with text-first entries and thumbnails aligned to the right.',
    'categories'  => [ 'modfarm-archive-body' ],
    'keywords'    => [ 'archive', 'posts', 'medium', 'thumbnail right', 'feed' ],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained","contentSize":"1020px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--20)">
<!-- wp:query {"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"layout":{"type":"constrained"}} -->
<div class="wp-block-query"><!-- wp:post-template -->
<!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|50"},"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}},"border":{"bottom":{"color":"#e5e5e5","width":"1px"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center" style="border-bottom-color:#e5e5e5;border-bottom-width:1px;padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:column {"verticalAlignment":"center","width":"68%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:68%"><!-- wp:post-date {"style":{"typography":{"fontSize":"14px"}}} /-->
<!-- wp:post-title {"isLink":true,"level":2,"style":{"typography":{"fontSize":"30px","lineHeight":"1.15","fontStyle":"normal","fontWeight":"700"}}} /-->
<!-- wp:post-excerpt {"moreText":"","excerptLength":26} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"32%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:32%"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3"} /--></div>
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
