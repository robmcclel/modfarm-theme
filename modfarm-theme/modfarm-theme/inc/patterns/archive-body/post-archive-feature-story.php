<?php
return [
    'slug'        => 'modfarm/post-archive-feature-story',
    'title'       => 'Post Archive Feature Story',
    'description' => 'A centered editorial archive with large images, generous spacing, and no card frame.',
    'categories'  => [ 'modfarm-archive-body' ],
    'keywords'    => [ 'archive', 'posts', 'feature', 'editorial', 'large image', 'magazine' ],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained","contentSize":"980px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--20)">
<!-- wp:query {"query":{"perPage":6,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"layout":{"type":"constrained"}} -->
<div class="wp-block-query"><!-- wp:post-template -->
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"},"blockGap":"var:preset|spacing|40"},"border":{"bottom":{"color":"#e5e5e5","width":"1px"}}},"layout":{"type":"constrained","contentSize":"920px"}} -->
<div class="wp-block-group" style="border-bottom-color:#e5e5e5;border-bottom-width:1px;padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">
<!-- wp:post-title {"textAlign":"center","isLink":true,"level":2,"style":{"typography":{"fontSize":"52px","lineHeight":"1.05","fontStyle":"normal","fontWeight":"800"}}} /-->

<!-- wp:group {"style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:post-date {"style":{"typography":{"fontSize":"16px"}}} /-->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"16px"}}} -->
<p style="font-size:16px">by</p>
<!-- /wp:paragraph -->
<!-- wp:post-author-name {"isLink":true,"style":{"typography":{"fontSize":"16px","fontStyle":"normal","fontWeight":"700"}}} /--></div>
<!-- /wp:group -->

<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->
<!-- wp:post-excerpt {"moreText":"Read more","excerptLength":34,"style":{"typography":{"fontSize":"21px","lineHeight":"1.5"}}} /-->
</div>
<!-- /wp:group -->
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
