<?php
return [
    'slug'        => 'modfarm/book-framework',
    'title'       => 'Book Framework Layout',
    'description' => 'Assembles header, selected book body pattern, and footer. The body pattern is chosen in ModFarm Settings.',
    'categories'  => [ 'modfarm-full-pages' ],
    'keywords'    => [ 'book', 'framework', 'modfarm', 'layout', 'builder' ],
    'content'     => '
<!-- wp:pattern {"slug":"modfarm/header"} /-->

<!-- wp:pattern {"slug":"modfarm/book-body-placeholder"} /-->

<!-- wp:pattern {"slug":"modfarm/footer"} /-->
',
];
