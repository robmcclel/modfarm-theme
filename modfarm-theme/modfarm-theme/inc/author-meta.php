<?php
/**
 * Author Meta Fields for the book-author taxonomy.
 *
 * Adds short description and repeatable social/profile links.
 */

if (!defined('ABSPATH')) {
    exit;
}

function modfarm_author_taxonomies(): array {
    return ['book-author', 'book-authors'];
}

function modfarm_author_social_meta_key(): string {
    return 'author_socials';
}

function modfarm_normalize_amazon_author_id($value): string {
    $author_id = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $value));

    return preg_match('/^[A-Z0-9]{10,14}$/', $author_id) ? $author_id : '';
}

function modfarm_extract_amazon_author_id($value): string {
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    if (preg_match('~/stores/author/([A-Z0-9]{10,14})~i', $value, $matches)) {
        return modfarm_normalize_amazon_author_id($matches[1]);
    }

    return modfarm_normalize_amazon_author_id($value);
}

function modfarm_amazon_author_url_from_id(string $author_id): string {
    $author_id = modfarm_normalize_amazon_author_id($author_id);

    return $author_id !== '' ? 'https://www.amazon.com/stores/author/' . rawurlencode($author_id) . '/about' : '';
}

function modfarm_author_social_platform_suggestions(): array {
    return [
        ['label' => 'Amazon Author', 'key' => 'amazon-author', 'type' => 'profile'],
        ['label' => 'BookBub', 'key' => 'bookbub', 'type' => 'profile'],
        ['label' => 'Goodreads', 'key' => 'goodreads', 'type' => 'profile'],
        ['label' => 'Kickstarter', 'key' => 'kickstarter', 'type' => 'crowdfunding'],
        ['label' => 'Patreon', 'key' => 'patreon', 'type' => 'subscription'],
        ['label' => 'BlueSky', 'key' => 'bluesky', 'type' => 'social'],
        ['label' => 'Discord', 'key' => 'discord', 'type' => 'social'],
        ['label' => 'Twitter/X', 'key' => 'twitter-x', 'type' => 'social'],
        ['label' => 'Facebook', 'key' => 'facebook', 'type' => 'social'],
        ['label' => 'Instagram', 'key' => 'instagram', 'type' => 'social'],
        ['label' => 'TikTok', 'key' => 'tiktok', 'type' => 'social'],
        ['label' => 'YouTube', 'key' => 'youtube', 'type' => 'video'],
        ['label' => 'RoyalRoad', 'key' => 'royalroad', 'type' => 'publishing'],
        ['label' => 'Ream', 'key' => 'ream', 'type' => 'subscription'],
        ['label' => 'Substack', 'key' => 'substack', 'type' => 'newsletter'],
        ['label' => 'Website', 'key' => 'website', 'type' => 'website'],
    ];
}

function modfarm_sanitize_author_socials($value): array {
    if (!is_array($value)) {
        return [];
    }

    $rows = [];
    $is_field_map = isset($value['label']) || isset($value['url']) || isset($value['key']) || isset($value['type']);

    if ($is_field_map) {
        $count = max(
            is_array($value['label'] ?? null) ? count($value['label']) : 0,
            is_array($value['url'] ?? null) ? count($value['url']) : 0,
            is_array($value['key'] ?? null) ? count($value['key']) : 0,
            is_array($value['type'] ?? null) ? count($value['type']) : 0
        );

        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'label' => $value['label'][$i] ?? '',
                'key'   => $value['key'][$i] ?? '',
                'url'   => $value['url'][$i] ?? '',
                'type'  => $value['type'][$i] ?? '',
            ];
        }
    } else {
        $rows = $value;
    }

    $socials = [];

    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }

        $label = sanitize_text_field((string) ($row['label'] ?? ''));
        $url = esc_url_raw((string) ($row['url'] ?? ''));

        if ($label === '' || $url === '') {
            continue;
        }

        $key = sanitize_title((string) ($row['key'] ?? ''));
        if ($key === '') {
            $key = sanitize_title($label);
        }

        $socials[] = [
            'label' => $label,
            'key'   => $key,
            'url'   => $url,
            'type'  => sanitize_key((string) ($row['type'] ?? '')),
        ];
    }

    return array_values($socials);
}

function modfarm_get_author_socials(int $term_id): array {
    if ($term_id <= 0) {
        return [];
    }

    return modfarm_sanitize_author_socials(get_term_meta($term_id, modfarm_author_social_meta_key(), true));
}

add_action('init', function () {
    $schema = [
        'type'       => 'array',
        'items'      => [
            'type'       => 'object',
            'properties' => [
                'label' => ['type' => 'string'],
                'key'   => ['type' => 'string'],
                'url'   => ['type' => 'string', 'format' => 'uri'],
                'type'  => ['type' => 'string'],
            ],
        ],
    ];

    foreach (modfarm_author_taxonomies() as $taxonomy) {
        register_term_meta($taxonomy, 'amazon_author_id', [
            'single'            => true,
            'type'              => 'string',
            'show_in_rest'      => true,
            'sanitize_callback' => 'modfarm_normalize_amazon_author_id',
            'auth_callback'     => static function (): bool {
                return current_user_can('manage_categories');
            },
        ]);

        register_term_meta($taxonomy, 'amazon_author_url', [
            'single'            => true,
            'type'              => 'string',
            'show_in_rest'      => true,
            'sanitize_callback' => 'esc_url_raw',
            'auth_callback'     => static function (): bool {
                return current_user_can('manage_categories');
            },
        ]);

        register_term_meta($taxonomy, modfarm_author_social_meta_key(), [
            'single'            => true,
            'type'              => 'array',
            'show_in_rest'      => ['schema' => $schema],
            'sanitize_callback' => 'modfarm_sanitize_author_socials',
            'auth_callback'     => static function (): bool {
                return current_user_can('manage_categories');
            },
        ]);

        register_rest_field($taxonomy, 'socials', [
            'get_callback' => static function ($term): array {
                $term_id = isset($term['id']) ? absint($term['id']) : 0;
                return modfarm_get_author_socials($term_id);
            },
            'schema'       => [
                'description' => __('Normalized author social/profile links.', 'modfarm-author'),
                'type'        => 'array',
                'context'     => ['view', 'edit'],
                'items'       => $schema['items'],
            ],
        ]);
    }
});

function modfarm_render_author_socials_fields(array $socials = []): void {
    $suggestions = modfarm_author_social_platform_suggestions();
    ?>
    <datalist id="modfarm-author-social-platforms">
        <?php foreach ($suggestions as $suggestion) : ?>
            <option
                value="<?php echo esc_attr($suggestion['label']); ?>"
                data-key="<?php echo esc_attr($suggestion['key']); ?>"
                data-type="<?php echo esc_attr($suggestion['type']); ?>"
            ></option>
        <?php endforeach; ?>
    </datalist>
    <div class="modfarm-author-socials" data-modfarm-author-socials>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Label', 'modfarm-author'); ?></th>
                    <th><?php esc_html_e('Key', 'modfarm-author'); ?></th>
                    <th><?php esc_html_e('URL', 'modfarm-author'); ?></th>
                    <th><?php esc_html_e('Type', 'modfarm-author'); ?></th>
                    <th><span class="screen-reader-text"><?php esc_html_e('Actions', 'modfarm-author'); ?></span></th>
                </tr>
            </thead>
            <tbody data-modfarm-author-social-rows>
                <?php
                $rows = !empty($socials) ? $socials : [['label' => '', 'key' => '', 'url' => '', 'type' => '']];
                foreach ($rows as $social) :
                    ?>
                    <tr>
                        <td><input type="text" class="widefat" name="author_socials[label][]" value="<?php echo esc_attr($social['label'] ?? ''); ?>" list="modfarm-author-social-platforms" data-social-label /></td>
                        <td><input type="text" class="widefat" name="author_socials[key][]" value="<?php echo esc_attr($social['key'] ?? ''); ?>" data-social-key /></td>
                        <td><input type="url" class="widefat" name="author_socials[url][]" value="<?php echo esc_url($social['url'] ?? ''); ?>" placeholder="https://" /></td>
                        <td><input type="text" class="widefat" name="author_socials[type][]" value="<?php echo esc_attr($social['type'] ?? ''); ?>" data-social-type /></td>
                        <td><button type="button" class="button" data-remove-social><?php esc_html_e('Remove', 'modfarm-author'); ?></button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><button type="button" class="button" data-add-social><?php esc_html_e('Add social/profile link', 'modfarm-author'); ?></button></p>
        <p class="description"><?php esc_html_e('Choose a suggested platform or enter a custom label and URL. Keys are stored as slugs for blocks, data, and future schema mapping.', 'modfarm-author'); ?></p>
    </div>
    <?php
}

foreach (['book-author', 'book-authors'] as $taxonomy) {
    add_action("{$taxonomy}_add_form_fields", function () {
        wp_nonce_field('modfarm_save_author_meta', 'modfarm_author_meta_nonce');
        ?>
        <div class="form-field">
            <label for="amazon_author_id"><?php esc_html_e('Amazon Author ID', 'modfarm-author'); ?></label>
            <input type="text" name="amazon_author_id" id="amazon_author_id" class="regular-text" placeholder="B00R7T569C" />
            <p class="description"><?php esc_html_e('Used by Fetch Author ID Import and Amazon Author profile links.', 'modfarm-author'); ?></p>
        </div>
        <div class="form-field">
            <label for="amazon_author_url"><?php esc_html_e('Amazon Author URL', 'modfarm-author'); ?></label>
            <input type="url" name="amazon_author_url" id="amazon_author_url" class="regular-text" placeholder="https://www.amazon.com/stores/author/B00R7T569C/about" />
            <p class="description"><?php esc_html_e('Optional. If left blank, ModFarm builds the About URL from the Author ID.', 'modfarm-author'); ?></p>
        </div>
        <div class="form-field">
            <label for="author_short"><?php esc_html_e('Short Author Description', 'modfarm-author'); ?></label>
            <textarea name="author_short" id="author_short" class="widefat"></textarea>
            <p class="description"><?php esc_html_e('Displayed in author lists. Keep it brief.', 'modfarm-author'); ?></p>
        </div>
        <div class="form-field">
            <label><?php esc_html_e('Social/Profile Links', 'modfarm-author'); ?></label>
            <?php modfarm_render_author_socials_fields(); ?>
        </div>
        <?php
    });

    add_action("{$taxonomy}_edit_form_fields", function ($term) {
        $amazon_author_id = get_term_meta($term->term_id, 'amazon_author_id', true);
        $amazon_author_url = get_term_meta($term->term_id, 'amazon_author_url', true);
        $short = get_term_meta($term->term_id, 'author_short', true);
        $socials = modfarm_get_author_socials((int) $term->term_id);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="amazon_author_id"><?php esc_html_e('Amazon Author ID', 'modfarm-author'); ?></label></th>
            <td>
                <?php wp_nonce_field('modfarm_save_author_meta', 'modfarm_author_meta_nonce'); ?>
                <input type="text" name="amazon_author_id" id="amazon_author_id" class="regular-text" value="<?php echo esc_attr($amazon_author_id); ?>" placeholder="B00R7T569C" />
                <p class="description"><?php esc_html_e('Used by Fetch Author ID Import and Amazon Author profile links.', 'modfarm-author'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="amazon_author_url"><?php esc_html_e('Amazon Author URL', 'modfarm-author'); ?></label></th>
            <td>
                <input type="url" name="amazon_author_url" id="amazon_author_url" class="regular-text" value="<?php echo esc_url($amazon_author_url); ?>" placeholder="https://www.amazon.com/stores/author/B00R7T569C/about" />
                <p class="description"><?php esc_html_e('Optional. If left blank, ModFarm builds the About URL from the Author ID.', 'modfarm-author'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="author_short"><?php esc_html_e('Short Author Description', 'modfarm-author'); ?></label></th>
            <td>
                <textarea name="author_short" id="author_short" class="widefat"><?php echo esc_textarea($short); ?></textarea>
                <p class="description"><?php esc_html_e('Displayed in author lists. Keep it brief.', 'modfarm-author'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><?php esc_html_e('Social/Profile Links', 'modfarm-author'); ?></th>
            <td><?php modfarm_render_author_socials_fields($socials); ?></td>
        </tr>
        <?php
    });

    add_action("created_{$taxonomy}", 'modfarm_save_author_term_meta', 10, 1);
    add_action("edited_{$taxonomy}", 'modfarm_save_author_term_meta', 10, 1);
}

function modfarm_save_author_term_meta($term_id): void {
    if (!isset($_POST['modfarm_author_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['modfarm_author_meta_nonce'])), 'modfarm_save_author_meta')) {
        return;
    }

    if (!current_user_can('edit_term', $term_id)) {
        return;
    }

    if (isset($_POST['author_short'])) {
        update_term_meta($term_id, 'author_short', sanitize_text_field(wp_unslash($_POST['author_short'])));
    }

    $amazon_author_id = isset($_POST['amazon_author_id'])
        ? modfarm_extract_amazon_author_id(wp_unslash($_POST['amazon_author_id']))
        : '';
    $amazon_author_url = isset($_POST['amazon_author_url'])
        ? esc_url_raw(wp_unslash($_POST['amazon_author_url']))
        : '';

    if ($amazon_author_id === '' && $amazon_author_url !== '') {
        $amazon_author_id = modfarm_extract_amazon_author_id($amazon_author_url);
    }

    if ($amazon_author_url === '' && $amazon_author_id !== '') {
        $amazon_author_url = modfarm_amazon_author_url_from_id($amazon_author_id);
    }

    update_term_meta($term_id, 'amazon_author_id', $amazon_author_id);
    update_term_meta($term_id, 'amazon_author_url', $amazon_author_url);

    $socials = isset($_POST['author_socials'])
        ? modfarm_sanitize_author_socials(wp_unslash($_POST['author_socials']))
        : [];

    update_term_meta($term_id, modfarm_author_social_meta_key(), $socials);
}

add_action('admin_footer-edit-tags.php', 'modfarm_author_socials_admin_script');
add_action('admin_footer-term.php', 'modfarm_author_socials_admin_script');

function modfarm_author_socials_admin_script(): void {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || !in_array($screen->taxonomy, ['book-author', 'book-authors'], true)) {
        return;
    }
    ?>
    <style>
        .modfarm-author-socials table input { min-width: 120px; }
        .modfarm-author-socials th:last-child,
        .modfarm-author-socials td:last-child { width: 1%; white-space: nowrap; }
    </style>
    <script>
        (function () {
            function slugify(value) {
                return String(value || '').toLowerCase().trim()
                    .replace(/&/g, 'and')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            function findSuggestion(value) {
                var options = document.querySelectorAll('#modfarm-author-social-platforms option');
                for (var i = 0; i < options.length; i++) {
                    if (options[i].value === value) {
                        return options[i];
                    }
                }
                return null;
            }

            function bindRow(row) {
                var label = row.querySelector('[data-social-label]');
                var key = row.querySelector('[data-social-key]');
                var type = row.querySelector('[data-social-type]');
                var remove = row.querySelector('[data-remove-social]');

                if (label) {
                    label.addEventListener('change', function () {
                        var option = findSuggestion(label.value);
                        if (key && !key.value) {
                            key.value = option ? option.getAttribute('data-key') || slugify(label.value) : slugify(label.value);
                        }
                        if (type && !type.value && option) {
                            type.value = option.getAttribute('data-type') || '';
                        }
                    });
                }

                if (remove) {
                    remove.addEventListener('click', function () {
                        var tbody = row.parentNode;
                        if (tbody && tbody.children.length > 1) {
                            row.remove();
                        } else {
                            row.querySelectorAll('input').forEach(function (input) {
                                input.value = '';
                            });
                        }
                    });
                }
            }

            document.querySelectorAll('[data-modfarm-author-socials]').forEach(function (wrap) {
                var tbody = wrap.querySelector('[data-modfarm-author-social-rows]');
                var add = wrap.querySelector('[data-add-social]');
                if (!tbody || !add) {
                    return;
                }

                tbody.querySelectorAll('tr').forEach(bindRow);
                add.addEventListener('click', function () {
                    var template = tbody.querySelector('tr');
                    if (!template) {
                        return;
                    }
                    var row = template.cloneNode(true);
                    row.querySelectorAll('input').forEach(function (input) {
                        input.value = '';
                    });
                    tbody.appendChild(row);
                    bindRow(row);
                });
            });
        })();
    </script>
    <?php
}
