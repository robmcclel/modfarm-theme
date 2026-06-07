<?php
return [
    'slug'        => 'modfarm/search-results-layout',
    'title'       => 'Search Results Layout',
    'description' => 'A grouped search results layout for books, articles, authors, and series.',
    'categories'  => [ 'modfarm-archive-body' ],
    'keywords'    => [ 'search', 'results', 'books', 'authors', 'series' ],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--20)">
<!-- wp:modfarm/search-results /-->
</div>
<!-- /wp:group -->
',
];
