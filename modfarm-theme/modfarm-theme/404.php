<?php
/**
 * Not-found template.
 *
 * @package ModFarm
 */

defined('ABSPATH') || exit;

get_header();

$render_template_part = static function (string $slug, string $area): void {
    echo do_blocks(
        sprintf(
            '<!-- wp:template-part {"slug":"%s","area":"%s"} /-->',
            esc_attr($slug),
            esc_attr($area)
        )
    ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core renders registered blocks.
};

$render_template_part('header', 'header');

$destinations = [
    [
        'label' => __('Home', 'modfarm-author'),
        'url'   => home_url('/'),
    ],
];

foreach (
    [
        'webcomic_series' => __('Browse Webcomics', 'modfarm-author'),
        'mf_update'       => __('Read Updates', 'modfarm-author'),
        'modfarm_event'   => __('View Events', 'modfarm-author'),
    ] as $post_type => $label
) {
    if (!post_type_exists($post_type) || !get_post_type_archive_link($post_type)) {
        continue;
    }

    $destinations[] = [
        'label' => $label,
        'url'   => get_post_type_archive_link($post_type),
    ];
}
?>

<main id="primary" class="site-main mf-not-found">
    <section class="mf-not-found__panel" aria-labelledby="mf-not-found-title">
        <p class="mf-not-found__code" aria-hidden="true">404</p>
        <p class="mf-not-found__eyebrow"><?php esc_html_e('Page not found', 'modfarm-author'); ?></p>
        <h1 id="mf-not-found-title"><?php esc_html_e('That page is no longer here.', 'modfarm-author'); ?></h1>
        <p class="mf-not-found__message">
            <?php esc_html_e('The address may have changed, or the page may have been removed. Try a search or choose another place to continue.', 'modfarm-author'); ?>
        </p>

        <div class="mf-not-found__search">
            <?php get_search_form(); ?>
        </div>

        <nav class="mf-not-found__links" aria-label="<?php esc_attr_e('Helpful destinations', 'modfarm-author'); ?>">
            <?php foreach ($destinations as $index => $destination) : ?>
                <a class="mf-not-found__link<?php echo $index === 0 ? ' is-primary' : ''; ?>" href="<?php echo esc_url($destination['url']); ?>">
                    <?php echo esc_html($destination['label']); ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </section>
</main>

<?php
$render_template_part('footer', 'footer');
get_footer();
