<?php
/**
 * ModFarm Theme: Book Archive Term Settings
 *
 * Adds per-archive controls to each taxonomy used by the Book CPT.
 * Stores settings as term meta and exposes helper functions for rendering.
 *
 * Fields per term:
 * - archive_image_variant: string enum/meta key for archive card image
 * - archive_show_button:   int(1|0)
 * - archive_show_sample:   int(1|0)
 * - archive_show_title:    int(1|0)
 * - archive_show_series:   int(1|0)
 * - archive_format_filter: int (book-format term ID; 0 = no additional filter)
 * - archive_books_in_row:  string percent width used by archive-list
 * - archive_display_order: string enum (ASC|DESC|rand)
 * - archive_order_date_key:string enum date meta key used by archive-list
 * - archive_hero_image:    int (attachment ID)
 * - archive_default_image: int (attachment ID)
 * - archive_display_hero:  int(1|0)  (show hero image on archive page)
 * - archive_display_default:int(1|0) (show default image on archive page header/intro if desired)
 *
 * Helpers:
 * - mfs_book_archive_taxonomies(): array of taxonomy slugs bound to book CPT
 * - mfs_get_archive_term_meta( $term_id, $key, $default='' )
 * - mfs_get_archive_term_media( $term_id, $which='hero'|'default', $size='full' )
 */

defined('ABSPATH') || exit;

/**
 * Return all public taxonomies attached to the Book CPT (slug 'book' by default).
 * Filterable via 'mfs_book_cpt' and 'mfs_book_archive_taxonomies'.
 */
function mfs_book_archive_taxonomies() {
    $book_cpt = apply_filters('mfs_book_cpt', 'book');

    $tax_objects = get_object_taxonomies($book_cpt, 'objects');
    $tax = array();
    foreach ($tax_objects as $tx => $obj) {
        if (!empty($obj->public)) {
            $tax[] = $tx;
        }
    }

    /**
     * Allow explicit control. Example to limit:
     * add_filter('mfs_book_archive_taxonomies', fn() => ['book-series','book-author','book-genre','book-tag']);
     */
    return apply_filters('mfs_book_archive_taxonomies', $tax);
}

/**
 * Return public taxonomies that can use ModFarm archive description/image meta.
 */
function mfs_archive_taxonomies() {
    $tax_objects = get_taxonomies(['public' => true], 'objects');
    $tax = array();

    foreach ($tax_objects as $tx => $obj) {
        if (!empty($obj->show_ui)) {
            $tax[] = $tx;
        }
    }

    return apply_filters('mfs_archive_taxonomies', array_values(array_unique($tax)));
}

function mfs_taxonomy_supports_book_archive_controls($taxonomy) {
    return in_array((string) $taxonomy, mfs_book_archive_taxonomies(), true);
}

/**
 * Register term meta with schema + REST.
 */
add_action('init', function () {
    $taxes = mfs_archive_taxonomies();

    $bool_schema = [
      'type'              => 'boolean',
      'single'            => true,
      'show_in_rest'      => true,
      'default'           => false,
      'sanitize_callback' => 'rest_sanitize_boolean',
    ];
    $int_schema = [
        'type'         => 'integer',
        'single'       => true,
        'show_in_rest' => true,
        'default'      => 0,
        'sanitize_callback' => 'absint',
    ];
    $image_variant_schema = [
        'type'         => 'string',
        'single'       => true,
        'show_in_rest' => true,
        'default'      => 'featured',
        'sanitize_callback' => 'sanitize_text_field',
    ];
    $string_schema = [
        'type'         => 'string',
        'single'       => true,
        'show_in_rest' => true,
        'default'      => '',
        'sanitize_callback' => 'sanitize_text_field',
    ];

    foreach ($taxes as $tx) {
        register_term_meta($tx, 'archive_image_variant', $image_variant_schema);
        register_term_meta($tx, 'archive_show_button',   $bool_schema);
        register_term_meta($tx, 'archive_show_sample',   $bool_schema);
        register_term_meta($tx, 'archive_show_title',    $bool_schema);
        register_term_meta($tx, 'archive_show_series',   $bool_schema);
        register_term_meta($tx, 'archive_format_filter', $int_schema);
        register_term_meta($tx, 'archive_books_in_row',  $string_schema);
        register_term_meta($tx, 'archive_display_order', $string_schema);
        register_term_meta($tx, 'archive_order_date_key', $string_schema);

        register_term_meta($tx, 'archive_hero_image',    $int_schema);
        register_term_meta($tx, 'archive_default_image', $int_schema);

        register_term_meta($tx, 'archive_display_hero',     $bool_schema);
        register_term_meta($tx, 'archive_display_default',  $bool_schema);
    }
});

/**
 * Admin UI: add fields to EDIT form (per term).
 */
add_action('admin_init', function () {
    foreach (mfs_archive_taxonomies() as $tx) {
        add_action("{$tx}_edit_form_fields", 'mfs_render_book_archive_term_fields', 10, 2);
        add_action("edited_{$tx}",           'mfs_save_book_archive_term_fields',   10, 2);

        // Optional: also show on ADD form (compact)
        add_action("{$tx}_add_form_fields",  'mfs_render_book_archive_add_fields', 10, 1);
        add_action("created_{$tx}",          'mfs_save_book_archive_term_fields',  10, 2);
    }
});

/**
 * Enqueue media for image selectors on the correct admin screens.
 */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'edit-tags.php' && $hook !== 'term.php') return;

    $screen = get_current_screen();
    if (empty($screen->taxonomy)) return;
    if (!in_array($screen->taxonomy, mfs_archive_taxonomies(), true)) return;

    wp_enqueue_media();
    wp_enqueue_editor();

    // Tiny inline JS for media selector buttons
    wp_add_inline_script(
        'jquery-core',
        "
        jQuery(document).on('click', '.mfs-image-select', function(e){
            e.preventDefault();
            const target  = jQuery(this).data('target'); // input id
            const preview = jQuery(this).data('preview'); // img id
            const frame = wp.media({ multiple:false });
            frame.on('select', function(){
                const att = frame.state().get('selection').first().toJSON();
                jQuery('#'+target).val(att.id);
                jQuery('#'+preview).attr('src', att.url).show();
            });
            frame.open();
        });
        jQuery(document).on('click', '.mfs-image-clear', function(e){
            e.preventDefault();
            const target  = jQuery(this).data('target');
            const preview = jQuery(this).data('preview');
            jQuery('#'+target).val('');
            jQuery('#'+preview).attr('src','').hide();
        });
        ",
        'after'
    );

    // Basic styles
    wp_add_inline_style(
        'common',
        ".mfs-term-table th{width:220px;}
         .mfs-img-wrap{display:flex; align-items:center; gap:12px;}
         .mfs-img-wrap img{max-width:160px; height:auto; display:block; border:1px solid #cdcdcd; padding:4px; background:#fff;}
         .mfs-term-note{color:#555; font-size:12px;}"
    );
});

/**
 * Upgrade the native taxonomy description textarea to a compact rich text editor.
 */
add_action('admin_footer-edit-tags.php', 'mfs_rich_taxonomy_description_editor');
add_action('admin_footer-term.php', 'mfs_rich_taxonomy_description_editor');

function mfs_rich_taxonomy_description_editor() {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || empty($screen->taxonomy) || !in_array($screen->taxonomy, mfs_archive_taxonomies(), true)) {
        return;
    }

    $description_id = $screen->base === 'edit-tags' ? 'tag-description' : 'description';
    ?>
    <script>
    (function () {
        var initialized = false;
        var descriptionId = <?php echo wp_json_encode($description_id); ?>;

        function initRichDescription() {
            var textarea = document.getElementById(descriptionId);
            if (initialized || !window.wp || !wp.editor || !textarea) {
                return;
            }

            if (textarea.offsetParent === null && textarea.type !== 'hidden') {
                return;
            }

            initialized = true;
            wp.editor.initialize(descriptionId, {
                mediaButtons: false,
                tinymce: {
                    wpautop: true,
                    menubar: false,
                    toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,link,unlink,undo,redo',
                    toolbar2: '',
                    block_formats: 'Paragraph=p;Heading 3=h3;Heading 4=h4'
                },
                quicktags: true
            });

            var form = document.getElementById('edittag') || document.getElementById('addtag');
            if (form) {
                form.addEventListener('submit', function () {
                    if (window.tinyMCE) {
                        tinyMCE.triggerSave();
                    }
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initRichDescription);
        } else {
            initRichDescription();
        }
    })();
    </script>
    <?php
}

/**
 * Render fields on EDIT form (table layout).
 */
function mfs_render_book_archive_term_fields($term, $taxonomy) {
    // Security
    wp_nonce_field('mfs_save_book_archive_term_fields', 'mfs_book_archive_term_nonce');

    $get = fn($k,$d='') => get_term_meta($term->term_id, $k, true) ?: $d;

    $variant     = $get('archive_image_variant', 'featured');

    $show_button = (int) $get('archive_show_button', 1);
    $show_sample = (int) $get('archive_show_sample', 0);
    $show_title  = (int) $get('archive_show_title', 1);
    $show_series = (int) $get('archive_show_series', 0);
    $format_filter = absint($get('archive_format_filter', 0));
    $books_in_row = $get('archive_books_in_row', '25%');
    $display_order = $get('archive_display_order', 'DESC');
    $order_date_key = $get('archive_order_date_key', 'publication_date');

    $hero_id     = absint($get('archive_hero_image', 0));
    $default_id  = absint($get('archive_default_image', 0));

    $display_hero    = (int) $get('archive_display_hero', 0);
    $display_default = (int) $get('archive_display_default', 0);

    $hero_url    = $hero_id ? wp_get_attachment_image_url($hero_id, 'large') : '';
    $default_url = $default_id ? wp_get_attachment_image_url($default_id, 'medium') : '';
    $default_image_label = ($taxonomy === 'book-author' || $taxonomy === 'book-authors') ? 'Profile Picture' : 'Default Image';
    $default_image_button = ($taxonomy === 'book-author' || $taxonomy === 'book-authors') ? 'Select Profile Picture' : 'Select Image';
    $default_image_description = ($taxonomy === 'book-author' || $taxonomy === 'book-authors')
        ? 'Profile picture for this author. Used in author blocks, archive cards, and taxonomy listing blocks.'
        : 'Fallback/brand image for this term. Can be shown on the archive page (toggle below) and used globally in taxonomy listing blocks across the site.';
    $default_display_label = ($taxonomy === 'book-author' || $taxonomy === 'book-authors') ? 'Display profile picture on archive page' : 'Display default image on archive page';

    $variants = [
        'featured'  => 'Featured (Featured Image)',
        'flat'      => 'Flat Cover Image (legacy)',
        '3d'        => '3D Mockup Cover (legacy)',
        'audio'     => 'Audiobook Cover (legacy)',
        'composite' => 'Composite Marketing Image (legacy)',
        'cover_ebook' => 'eBook Cover',
        'cover_paperback' => 'Paperback Cover',
        'cover_hardcover' => 'Hardcover Cover',
        'cover_image_audio' => 'Audiobook Cover',
        'cover_image_flat' => 'Flat Cover Image',
        'cover_image_3d' => '3D Mockup Cover',
        'cover_ebook_3d' => '3D eBook Cover',
        'cover_paperback_3d' => '3D Paperback Cover',
        'cover_hardcover_3d' => '3D Hardcover Cover',
        'cover_image_audio_3d' => '3D Audiobook Cover',
        'cover_image_composite' => 'Composite Marketing Image',
    ];
    $row_options = [
        '50%' => '2 per row',
        '33.333%' => '3 per row',
        '25%' => '4 per row',
        '20%' => '5 per row',
        '16.666%' => '6 per row',
    ];
    $order_options = [
        'ASC' => 'Oldest first',
        'DESC' => 'Most recent first',
        'rand' => 'Random',
    ];
    $date_key_options = [
        'publication_date' => 'Primary Publication Date',
        'paperback_publication_date' => 'Paperback Publication Date',
        'hardcover_publication_date' => 'Hardcover Publication Date',
        'audiobook_publication_date' => 'Audiobook Publication Date',
    ];
    $format_terms = get_terms([
        'taxonomy'   => 'book-format',
        'hide_empty' => false,
    ]);
    ?>
    <table class="form-table mfs-term-table" role="presentation">
        <?php if (mfs_taxonomy_supports_book_archive_controls($taxonomy)) : ?>
        <tr class="form-field">
            <th scope="row"><label for="archive_image_variant">Image Variant</label></th>
            <td>
                <select name="archive_image_variant" id="archive_image_variant">
                    <?php foreach ($variants as $val => $label): ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected($variant, $val); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description mfs-term-note">Controls which image style the archive’s book cards prefer.</p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="archive_books_in_row">Books Per Row</label></th>
            <td>
                <select name="archive_books_in_row" id="archive_books_in_row">
                    <?php foreach ($row_options as $val => $label): ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected($books_in_row, $val); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description mfs-term-note">Controls the archive grid density for this term.</p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="archive_display_order">Book Order</label></th>
            <td>
                <select name="archive_display_order" id="archive_display_order">
                    <?php foreach ($order_options as $val => $label): ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected($display_order, $val); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description mfs-term-note">Choose whether archive books start with earliest, newest, or random entries.</p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="archive_order_date_key">Order Date Basis</label></th>
            <td>
                <select name="archive_order_date_key" id="archive_order_date_key">
                    <?php foreach ($date_key_options as $val => $label): ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected($order_date_key, $val); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description mfs-term-note">For example, paperback archives can sort by paperback publication date.</p>
            </td>
        </tr>

        <?php if ($taxonomy !== 'book-format' && $taxonomy !== 'book-formats') : ?>
        <tr class="form-field">
            <th scope="row"><label for="archive_format_filter">Archive Format Filter</label></th>
            <td>
                <select name="archive_format_filter" id="archive_format_filter">
                    <option value="0"><?php esc_html_e('No additional format filter', 'modfarm'); ?></option>
                    <?php if (!is_wp_error($format_terms)) : ?>
                        <?php foreach ($format_terms as $format_term) : ?>
                            <option value="<?php echo esc_attr((int) $format_term->term_id); ?>" <?php selected($format_filter, (int) $format_term->term_id); ?>>
                                <?php echo esc_html($format_term->name); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <p class="description mfs-term-note">Optionally limit this archive listing to one format. Leave unset to show all formats assigned to this term.</p>
            </td>
        </tr>
        <?php endif; ?>

        <tr class="form-field">
            <th scope="row">Show Elements</th>
            <td>
                <input type="hidden" name="archive_show_button" value="0">
                <label><input type="checkbox" name="archive_show_button" value="1" <?php checked($show_button, 1); ?>> Button</label>&nbsp;&nbsp;

                <input type="hidden" name="archive_show_sample" value="0">
                <label><input type="checkbox" name="archive_show_sample" value="1" <?php checked($show_sample, 1); ?>> Sample</label>&nbsp;&nbsp;

                <input type="hidden" name="archive_show_title" value="0">
                <label><input type="checkbox" name="archive_show_title"  value="1" <?php checked($show_title, 1); ?>> Title</label>&nbsp;&nbsp;

                <input type="hidden" name="archive_show_series" value="0">
                <label><input type="checkbox" name="archive_show_series" value="1" <?php checked($show_series, 1); ?>> Series</label>
                <p class="description mfs-term-note">Toggle visibility of these UI elements on this archive's listing.</p>
            </td>
        </tr>
        <?php endif; ?>
        <tr class="form-field">
            <th scope="row">Hero Image</th>
            <td>
                <div class="mfs-img-wrap">
                    <img id="archive_hero_image_preview" src="<?php echo esc_url($hero_url); ?>" style="<?php echo $hero_url ? '' : 'display:none;'; ?>">
                    <div>
                        <input type="hidden" id="archive_hero_image" name="archive_hero_image" value="<?php echo esc_attr($hero_id); ?>">
                        <a href="#" class="button mfs-image-select" data-target="archive_hero_image" data-preview="archive_hero_image_preview">Select Image</a>
                        <a href="#" class="button button-secondary mfs-image-clear" data-target="archive_hero_image" data-preview="archive_hero_image_preview">Clear</a>
                        <p class="description mfs-term-note">Displayed at the top of this archive (if enabled below).</p>
                    </div>
                </div>
                <label style="display:block;margin-top:8px;">
                    <input type="checkbox" name="archive_display_hero" value="1" <?php checked($display_hero, 1); ?>> Display hero on archive page
                </label>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><?php echo esc_html($default_image_label); ?></th>
            <td>
                <div class="mfs-img-wrap">
                    <img id="archive_default_image_preview" src="<?php echo esc_url($default_url); ?>" style="<?php echo $default_url ? '' : 'display:none;'; ?>">
                    <div>
                        <input type="hidden" id="archive_default_image" name="archive_default_image" value="<?php echo esc_attr($default_id); ?>">
                        <a href="#" class="button mfs-image-select" data-target="archive_default_image" data-preview="archive_default_image_preview"><?php echo esc_html($default_image_button); ?></a>
                        <a href="#" class="button button-secondary mfs-image-clear" data-target="archive_default_image" data-preview="archive_default_image_preview">Clear</a>
                        <p class="description mfs-term-note"><?php echo esc_html($default_image_description); ?></p>
                    </div>
                </div>
                <label style="display:block;margin-top:8px;">
                    <input type="checkbox" name="archive_display_default" value="1" <?php checked($display_default, 1); ?>> <?php echo esc_html($default_display_label); ?>
                </label>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Compact ADD form fields (optional). Keeps it minimal to avoid clutter on creation.
 */
function mfs_render_book_archive_add_fields($taxonomy) {
    wp_nonce_field('mfs_save_book_archive_term_fields', 'mfs_book_archive_term_nonce');
    $default_image_label = ($taxonomy === 'book-author' || $taxonomy === 'book-authors') ? 'Profile Picture' : 'Default Image';
    $default_image_button = ($taxonomy === 'book-author' || $taxonomy === 'book-authors') ? 'Select Profile Picture' : 'Select Default Image';
    $default_image_description = ($taxonomy === 'book-author' || $taxonomy === 'book-authors')
        ? 'Used in author blocks, archive cards, and taxonomy listing blocks.'
        : 'Also used by taxonomy listing blocks around the site.';
    $default_display_label = ($taxonomy === 'book-author' || $taxonomy === 'book-authors') ? 'Display profile picture on archive page' : 'Display default image on archive page';

    $variants = [
        'featured'  => 'Featured (Featured Image)',
        'flat'      => 'Flat Cover Image (legacy)',
        '3d'        => '3D Mockup Cover (legacy)',
        'audio'     => 'Audiobook Cover (legacy)',
        'composite' => 'Composite Marketing Image (legacy)',
        'cover_ebook' => 'eBook Cover',
        'cover_paperback' => 'Paperback Cover',
        'cover_hardcover' => 'Hardcover Cover',
        'cover_image_audio' => 'Audiobook Cover',
        'cover_image_flat' => 'Flat Cover Image',
        'cover_image_3d' => '3D Mockup Cover',
        'cover_ebook_3d' => '3D eBook Cover',
        'cover_paperback_3d' => '3D Paperback Cover',
        'cover_hardcover_3d' => '3D Hardcover Cover',
        'cover_image_audio_3d' => '3D Audiobook Cover',
        'cover_image_composite' => 'Composite Marketing Image',
    ];
    $row_options = [
        '50%' => '2 per row',
        '33.333%' => '3 per row',
        '25%' => '4 per row',
        '20%' => '5 per row',
        '16.666%' => '6 per row',
    ];
    $order_options = [
        'ASC' => 'Oldest first',
        'DESC' => 'Most recent first',
        'rand' => 'Random',
    ];
    $date_key_options = [
        'publication_date' => 'Primary Publication Date',
        'paperback_publication_date' => 'Paperback Publication Date',
        'hardcover_publication_date' => 'Hardcover Publication Date',
        'audiobook_publication_date' => 'Audiobook Publication Date',
    ];
    $format_terms = get_terms([
        'taxonomy'   => 'book-format',
        'hide_empty' => false,
    ]);
    ?>
    <?php if (mfs_taxonomy_supports_book_archive_controls($taxonomy)) : ?>
    <div class="form-field term-group">
        <label for="archive_image_variant">Image Variant</label>
        <select name="archive_image_variant" id="archive_image_variant">
            <?php foreach ($variants as $val => $label): ?>
                <option value="<?php echo esc_attr($val); ?>"><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
        <p class="description">Controls which image style this archive prefers.</p>
    </div>
    <div class="form-field term-group">
        <label for="archive_books_in_row">Books Per Row</label>
        <select name="archive_books_in_row" id="archive_books_in_row">
            <?php foreach ($row_options as $val => $label): ?>
                <option value="<?php echo esc_attr($val); ?>" <?php selected($val, '25%'); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-field term-group">
        <label for="archive_display_order">Book Order</label>
        <select name="archive_display_order" id="archive_display_order">
            <?php foreach ($order_options as $val => $label): ?>
                <option value="<?php echo esc_attr($val); ?>" <?php selected($val, 'DESC'); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-field term-group">
        <label for="archive_order_date_key">Order Date Basis</label>
        <select name="archive_order_date_key" id="archive_order_date_key">
            <?php foreach ($date_key_options as $val => $label): ?>
                <option value="<?php echo esc_attr($val); ?>" <?php selected($val, 'publication_date'); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if ($taxonomy !== 'book-format' && $taxonomy !== 'book-formats') : ?>
    <div class="form-field term-group">
        <label for="archive_format_filter">Archive Format Filter</label>
        <select name="archive_format_filter" id="archive_format_filter">
            <option value="0"><?php esc_html_e('No additional format filter', 'modfarm'); ?></option>
            <?php if (!is_wp_error($format_terms)) : ?>
                <?php foreach ($format_terms as $format_term) : ?>
                    <option value="<?php echo esc_attr((int) $format_term->term_id); ?>">
                        <?php echo esc_html($format_term->name); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <p class="description">Optionally limit this archive listing to one format.</p>
    </div>
    <?php endif; ?>
    <div class="form-field term-group">
        <label>Show Elements</label>
        <label><input type="checkbox" name="archive_show_button" value="1" checked> Button</label><br>
        <label><input type="checkbox" name="archive_show_sample" value="1"> Sample</label><br>
        <label><input type="checkbox" name="archive_show_title"  value="1" checked> Title</label><br>
        <label><input type="checkbox" name="archive_show_series" value="1"> Series</label>
    </div>
    <?php endif; ?>
    <div class="form-field term-group">
        <label>Hero Image</label>
        <input type="hidden" id="archive_hero_image" name="archive_hero_image" value="">
        <div>
            <a href="#" class="button mfs-image-select" data-target="archive_hero_image" data-preview="archive_hero_image_preview">Select Hero Image</a>
            <a href="#" class="button button-secondary mfs-image-clear" data-target="archive_hero_image" data-preview="archive_hero_image_preview">Clear</a>
        </div>
        <img id="archive_hero_image_preview" src="" style="display:none; margin-top:8px; max-width:160px;">
        <label style="display:block;margin-top:8px;">
            <input type="checkbox" name="archive_display_hero" value="1"> Display hero on archive page
        </label>
    </div>
    <div class="form-field term-group">
        <label><?php echo esc_html($default_image_label); ?></label>
        <input type="hidden" id="archive_default_image" name="archive_default_image" value="">
        <div>
            <a href="#" class="button mfs-image-select" data-target="archive_default_image" data-preview="archive_default_image_preview"><?php echo esc_html($default_image_button); ?></a>
            <a href="#" class="button button-secondary mfs-image-clear" data-target="archive_default_image" data-preview="archive_default_image_preview">Clear</a>
        </div>
        <img id="archive_default_image_preview" src="" style="display:none; margin-top:8px; max-width:160px;">
        <label style="display:block;margin-top:8px;">
            <input type="checkbox" name="archive_display_default" value="1"> <?php echo esc_html($default_display_label); ?>
        </label>
        <p class="description"><?php echo esc_html($default_image_description); ?></p>
    </div>
    <?php
}

/**
 * Save handler (both created_{$tax} and edited_{$tax}).
 */
function mfs_save_book_archive_term_fields($term_id, $tt_id = 0) {
    if (!isset($_POST['mfs_book_archive_term_nonce']) || !wp_verify_nonce($_POST['mfs_book_archive_term_nonce'], 'mfs_save_book_archive_term_fields')) {
        return;
    }
    if (!current_user_can('manage_categories')) return;

    // Sanitize checkbox to 1/0
    $cb = function($k){ return isset($_POST[$k]) && $_POST[$k] ? 1 : 0; };
    $id = function($k){ return isset($_POST[$k]) ? absint($_POST[$k]) : 0; };
    $one_of = function($k, array $allowed, $def) {
        $val = isset($_POST[$k]) ? sanitize_text_field(wp_unslash($_POST[$k])) : $def;
        return in_array($val, $allowed, true) ? $val : $def;
    };

    update_term_meta($term_id, 'archive_image_variant', $one_of('archive_image_variant', [
        'featured',
        'flat',
        '3d',
        'audio',
        'composite',
        'cover_ebook',
        'cover_paperback',
        'cover_hardcover',
        'cover_image_audio',
        'cover_image_flat',
        'cover_image_3d',
        'cover_ebook_3d',
        'cover_paperback_3d',
        'cover_hardcover_3d',
        'cover_image_audio_3d',
        'cover_image_composite',
    ], 'featured'));

    update_term_meta($term_id, 'archive_show_button',   $cb('archive_show_button'));
    update_term_meta($term_id, 'archive_show_sample',   $cb('archive_show_sample'));
    update_term_meta($term_id, 'archive_show_title',    $cb('archive_show_title'));
    update_term_meta($term_id, 'archive_show_series',   $cb('archive_show_series'));
    update_term_meta($term_id, 'archive_format_filter', $id('archive_format_filter'));
    update_term_meta($term_id, 'archive_books_in_row', $one_of('archive_books_in_row', ['50%', '33.333%', '25%', '20%', '16.666%'], '25%'));
    update_term_meta($term_id, 'archive_display_order', $one_of('archive_display_order', ['ASC', 'DESC', 'rand'], 'DESC'));
    update_term_meta($term_id, 'archive_order_date_key', $one_of('archive_order_date_key', ['publication_date', 'paperback_publication_date', 'hardcover_publication_date', 'audiobook_publication_date'], 'publication_date'));

    update_term_meta($term_id, 'archive_hero_image',    $id('archive_hero_image'));
    update_term_meta($term_id, 'archive_default_image', $id('archive_default_image'));

    update_term_meta($term_id, 'archive_display_hero',     $cb('archive_display_hero'));
    update_term_meta($term_id, 'archive_display_default',  $cb('archive_display_default'));
}

/* -------------------------- Front-end Helpers --------------------------- */

/**
 * Get archive term meta with default.
 */
function mfs_get_archive_term_meta($term_id, $key, $default = '') {
    $val = get_term_meta($term_id, $key, true);
    return ($val === '' || $val === null) ? $default : $val;
}

/**
 * Get hero/default image URL for a term. Returns '' if none.
 *
 * @param int    $term_id
 * @param string $which  'hero'|'default'
 * @param string $size   image size
 * @return string
 */
function mfs_get_archive_term_media($term_id, $which = 'hero', $size = 'full') {
    $meta_key = $which === 'default' ? 'archive_default_image' : 'archive_hero_image';
    $att_id = absint(get_term_meta($term_id, $meta_key, true));
    if (!$att_id) return '';
    $url = wp_get_attachment_image_url($att_id, $size);
    return $url ? $url : '';
}
