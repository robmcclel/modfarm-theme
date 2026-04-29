<?php
/**
 * Author Meta Fields for book-author taxonomy
 * Adds avatar and short description fields to book-authors
 */

add_action('book-authors_add_form_fields', function () {
    ?>
    <div class="form-field">
        <label for="author_avatar"><?php _e('Author Avatar URL', 'modfarm-author'); ?></label>
        <input type="text" name="author_avatar" id="author_avatar" class="widefat" />
        <p class="description">Paste the URL of the author’s profile image.</p>
    </div>
    <div class="form-field">
        <label for="author_short"><?php _e('Short Author Description', 'modfarm-author'); ?></label>
        <textarea name="author_short" id="author_short" class="widefat"></textarea>
        <p class="description">Displayed in author lists. Keep it brief.</p>
    </div>
    <?php
});

add_action('book-authors_edit_form_fields', function ($term) {
    $avatar = get_term_meta($term->term_id, 'author_avatar', true);
    $short = get_term_meta($term->term_id, 'author_short', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label for="author_avatar"><?php _e('Author Avatar URL', 'modfarm-author'); ?></label></th>
        <td>
            <input type="text" name="author_avatar" id="author_avatar" value="<?php echo esc_attr($avatar); ?>" class="widefat" />
            <p class="description">Paste the URL of the author’s profile image.</p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row"><label for="author_short"><?php _e('Short Author Description', 'modfarm-author'); ?></label></th>
        <td>
            <textarea name="author_short" id="author_short" class="widefat"><?php echo esc_textarea($short); ?></textarea>
            <p class="description">Displayed in author lists. Keep it brief.</p>
        </td>
    </tr>
    <?php
});

add_action('created_book-authors', function ($term_id) {
    if (isset($_POST['author_avatar'])) {
        update_term_meta($term_id, 'author_avatar', esc_url_raw($_POST['author_avatar']));
    }
    if (isset($_POST['author_short'])) {
        update_term_meta($term_id, 'author_short', sanitize_text_field($_POST['author_short']));
    }
}, 10, 1);

add_action('edited_book-authors', function ($term_id) {
    if (isset($_POST['author_avatar'])) {
        update_term_meta($term_id, 'author_avatar', esc_url_raw($_POST['author_avatar']));
    }
    if (isset($_POST['author_short'])) {
        update_term_meta($term_id, 'author_short', sanitize_text_field($_POST['author_short']));
    }
}, 10, 1);
