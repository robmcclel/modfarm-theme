<?php
return [
    'slug'        => 'modfarm/book-page-available-audio',
    'title'       => 'Book Page Available Audio',
    'description' => '',
    'categories'  => [ 'modfarm-book-elements', 'audiobook'],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center","style":{"typography":{"textTransform":"capitalize"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="text-transform:capitalize">Available In Audio</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:modfarm/book-cover-art {"coverType":"cover_image_audio"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:modfarm/book-page-audio /-->

<!-- wp:modfarm/advanced-book-details {"rows":["meta:audiobook_narrator","meta:audiobook_duration","meta:audiobook_publication_date"]} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
',
];