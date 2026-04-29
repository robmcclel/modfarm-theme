<?php
return [
    'slug'        => 'modfarm/post-framework',
    'title'       => 'Post Framework Layout',
    'description' => 'Assembles header, selected post body pattern, and footer. The body pattern is chosen in ModFarm Settings.',
    'categories'  => [ 'modfarm-full-pages' ],
    'keywords'    => [ 'post', 'framework', 'modfarm', 'layout', 'builder' ],
    'content'     => '
<!-- wp:pattern {"slug":"modfarm/header"} /-->

<!-- wp:pattern {"slug":"modfarm/post-body-placeholder"} /-->

<!-- wp:pattern {"slug":"modfarm/footer"} /-->
',
];
