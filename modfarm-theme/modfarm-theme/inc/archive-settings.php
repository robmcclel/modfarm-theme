<?php
/**
 * ModFarm Theme: Book Archive Term Settings
 *
 * Adds per-archive controls to each taxonomy used by the Book CPT.
 * Stores settings as term meta and exposes helper functions for rendering.
 *
 * Fields per term:
 * - archive_image_variant: string enum (featured|flat|3d|audio|composite)
 * - archive_show_button:   int(1|0)
 * - archive_show_sample:   int(1|0)
 * - archive_show_title:    int(1|0)
 * - archive_show_series:   int(1|0)
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
 * Register term meta with schema + REST.
 */
add_action('init', function () {
    $taxes = mfs_book_archive_taxonomies();

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
    $string_schema = [
        'type'         => 'string',
        'single'       => true,
        'show_in_rest' => true,
        'default'      => 'featured',
        'sanitize_callback' => 'sanitize_text_field',
    ];

    foreach ($taxes as $tx) {
        register_term_meta($tx, 'archive_image_variant', $string_schema);
        register_term_meta($tx, 'archive_show_button',   $bool_schema);
        register_term_meta($tx, 'archive_show_sample',   $bool_schema);
        register_term_meta($tx, 'archive_show_title',    $bool_schema);
        register_term_meta($tx, 'archive_show_series',   $bool_schema);

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
    foreach (mfs_book_archive_taxonomies() as $tx) {
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
    if (!in_array($screen->taxonomy, mfs_book_archive_taxonomies(), true)) return;

    wp_enqueue_media();

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

    $hero_id     = absint($get('archive_hero_image', 0));
    $default_id  = absint($get('archive_default_image', 0));

    $display_hero    = (int) $get('archive_display_hero', 0);
    $display_default = (int) $get('archive_display_default', 0);

    $hero_url    = $hero_id ? wp_get_attachment_image_url($hero_id, 'large') : '';
    $default_url = $default_id ? wp_get_attachment_image_url($default_id, 'medium') : '';

    $variants = [
        'featured'  => 'Featured (Featured Image)',
        'flat'      => 'Flat',
        '3d'        => '3D',
        'audio'     => 'Audiobook',
        'composite' => 'Composite',
    ];
    ?>
    <table class="form-table mfs-term-table" role="presentation">
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
            <!-- Show Elements -->
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
                <p class="description mfs-term-note">Toggle visibility of these UI elements on this archive’s listing.</p>
              </td>
            </tr>
        </tr>
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
            <th scope="row">Default Image</th>
            <td>
                <div class="mfs-img-wrap">
                    <img id="archive_default_image_preview" src="<?php echo esc_url($default_url); ?>" style="<?php echo $default_url ? '' : 'display:none;'; ?>">
                    <div>
                        <input type="hidden" id="archive_default_image" name="archive_default_image" value="<?php echo esc_attr($default_id); ?>">
                        <a href="#" class="button mfs-image-select" data-target="archive_default_image" data-preview="archive_default_image_preview">Select Image</a>
                        <a href="#" class="button button-secondary mfs-image-clear" data-target="archive_default_image" data-preview="archive_default_image_preview">Clear</a>
                        <p class="description mfs-term-note">
                            Fallback/brand image for this term. Can be shown on the archive page (toggle below) and
                            used globally in taxonomy listing blocks across the site.
                        </p>
                    </div>
                </div>
                <label style="display:block;margin-top:8px;">
                    <input type="checkbox" name="archive_display_default" value="1" <?php checked($display_default, 1); ?>> Display default image on archive page
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

    $variants = [
        'featured'  => 'Featured (Featured Image)',
        'flat'      => 'Flat',
        '3d'        => '3D',
        'audio'     => 'Audiobook',
        'composite' => 'Composite',
    ];
    ?>
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
        <label>Show Elements</label>
        <label><input type="checkbox" name="archive_show_button" value="1" checked> Button</label><br>
        <label><input type="checkbox" name="archive_show_sample" value="1"> Sample</label><br>
        <label><input type="checkbox" name="archive_show_title"  value="1" checked> Title</label><br>
        <label><input type="checkbox" name="archive_show_series" value="1"> Series</label>
    </div>
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
        <label>Default Image</label>
        <input type="hidden" id="archive_default_image" name="archive_default_image" value="">
        <div>
            <a href="#" class="button mfs-image-select" data-target="archive_default_image" data-preview="archive_default_image_preview">Select Default Image</a>
            <a href="#" class="button button-secondary mfs-image-clear" data-target="archive_default_image" data-preview="archive_default_image_preview">Clear</a>
        </div>
        <img id="archive_default_image_preview" src="" style="display:none; margin-top:8px; max-width:160px;">
        <label style="display:block;margin-top:8px;">
            <input type="checkbox" name="archive_display_default" value="1"> Display default image on archive page
        </label>
        <p class="description">Also used by taxonomy listing blocks around the site.</p>
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
    $tx = function($k, $def=''){ return isset($_POST[$k]) ? sanitize_text_field(wp_unslash($_POST[$k])) : $def; };
    $id = function($k){ return isset($_POST[$k]) ? absint($_POST[$k]) : 0; };

    update_term_meta($term_id, 'archive_image_variant', $tx('archive_image_variant','featured'));

    update_term_meta($term_id, 'archive_show_button',   $cb('archive_show_button'));
    update_term_meta($term_id, 'archive_show_sample',   $cb('archive_show_sample'));
    update_term_meta($term_id, 'archive_show_title',    $cb('archive_show_title'));
    update_term_meta($term_id, 'archive_show_series',   $cb('archive_show_series'));

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