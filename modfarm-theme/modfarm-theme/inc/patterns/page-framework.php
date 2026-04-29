<?php
return [
    'slug'        => 'modfarm/page-framework',
    'title'       => 'Page Framework Layout',
    'description' => 'Assembles header, selected page body pattern, and footer. The body pattern is chosen in ModFarm Settings.',
    'categories'  => [ 'modfarm-full-pages' ],
    'keywords'    => [ 'page', 'framework', 'modfarm', 'layout', 'builder' ],
    'content'     => '
<!-- wp:pattern {"slug":"modfarm/header"} /-->

<!-- wp:pattern {"slug":"modfarm/page-body-placeholder"} /-->

<!-- wp:pattern {"slug":"modfarm/footer"} /-->
',
];
