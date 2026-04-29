<?php
/**
 * Adds a ModFarm Theme admin panel under Appearance
 * for quick reference and setup status.
 */

add_action('admin_menu', function () {
    add_theme_page(
        __('ModFarm Theme Panel', 'modfarm-author'),
        __('ModFarm Theme', 'modfarm-author'),
        'edit_theme_options',
        'modfarm-theme-panel',
        'modfarm_render_theme_panel'
    );
});

function modfarm_render_theme_panel() {
    $options = get_option('modfarm_theme_settings');
    $theme   = wp_get_theme();
    $body_font = $options['body_font'] ?? 'Default';
    $heading_font = $options['heading_font'] ?? 'Default';
    $author_name = $options['primary_author_name'] ?? '';
    ?>
    <div class="wrap">
        <h1>ModFarm Author Theme</h1>
        <p><strong>Version:</strong> <?php echo esc_html($theme->get('Version')); ?></p>

        <hr>
        <h2>Theme Overview</h2>
        <ul>
            <li><strong>Active Theme:</strong> <?php echo esc_html($theme->get('Name')); ?></li>
            <li><strong>Theme Directory:</strong> <?php echo esc_html(get_template_directory()); ?></li>
            <li><strong>Primary Author:</strong> <?php echo $author_name ? esc_html($author_name) : '<em>Not yet set</em>'; ?></li>
        </ul>

        <hr>
        <h2>Current Font Settings</h2>
        <ul>
            <li><strong>Body Font:</strong> <?php echo esc_html($body_font); ?></li>
            <li><strong>Heading Font:</strong> <?php echo esc_html($heading_font); ?></li>
        </ul>

        <hr>
        <h2>Quick Links</h2>
        <ul>
            <li><a href="<?php echo esc_url(admin_url('customize.php')); ?>">Customize Fonts & Colors</a></li>
            <li><a href="<?php echo esc_url(admin_url('site-editor.php')); ?>">Open Site Editor</a></li>
            <li><a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=book-authors&post_type=books')); ?>">Manage Book Authors</a></li>
            <li><a href="<?php echo esc_url(admin_url('edit.php?post_type=books')); ?>">View Books</a></li>
        </ul>

        <hr>
        <h2>Installed ModFarm Blocks</h2>
        <ul>
            <li><strong>Book Details</strong>: Displays ISBN, publisher, date, etc.</li>
            <li><strong>Audio Sample</strong>: Adds a player from the audio sample meta</li>
            <li><strong>Cover Art</strong>: Select format-specific image with aspect control</li>
            <li><strong>Page Buttons</strong>: Up to 5 custom buttons for CTA</li>
            <li><strong>Sales Links</strong>: Up to 6 icon-based store buttons</li>
            <li><strong>Author Credit</strong>: Displays linked author names and avatars</li>
            <li><strong>Multi-Tax Book Query</strong>: Filter and show books by format/series/etc.</li>
        </ul>

        <hr>
        <p><em>ModFarm Author Theme is built with modular patterns, smart block layouts, and performance-focused design.</em></p>
    </div>
    <?php
}
