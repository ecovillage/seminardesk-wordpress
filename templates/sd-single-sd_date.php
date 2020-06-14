<?php
/**
 * The template for single post of CPT sd_date
 * 
 * @package SeminardeskPlugin
 */

use Inc\Utils\TemplateUtils as Utils;

get_header();
if (have_posts()) {
    while (have_posts()) {
        the_post();
        ?>
        <main id="site-content" role="main">
            
            <header class="archive-header has-text-align-center header-footer-group">
                <div class="archive-header-inner section-inner medium">
                    <?php 
                    Utils::get_value_by_language( $post->sd_data['title'], 'DE', '<h1 class="archive-title">', '</h1>', true); 
                    ?>
                </div><!-- .archive-header-inner -->
            </header><!-- .archive-header -->
            <div class="entry-header-inner section-inner small">
                <p></p>
                <?php
                Utils::get_date( $post->sd_date_begin, $post->sd_date_end, '<p><strong>' . __('Date: ', 'seminardesk') . '</strong>', '</p>', true);
                Utils::get_facilitators( $post->sd_data['facilitators'], '<p><strong>' . __('Facilitator: ', 'seminardesk') . '</strong>', '</p>', true );
                Utils::get_value_by_language( $post->sd_data['priceInfo'], 'DE', '<p><strong>' . __('Price: ', 'seminardesk') . '</strong>', '</p>', true );
                Utils::get_venue( $post->sd_data['venue'], '<p><strong>' . __('Venue: ', 'seminardesk') . '</strong>', '</p>', true);
                Utils::get_img_remote( Utils::get_value_by_language($post->sd_data['teaserPictureUrl']), '300', '', $alt = "remote image load failed", '<p>', '</p>', true );
                Utils::get_value_by_language( $post->sd_data['teaser'], 'DE',  '<p>', '</p>', true );
                ?>
                <a href="<?php echo esc_url(get_permalink($post->wp_event_id)); ?>">
                    <?php esc_html_e('More ...', 'seminardesk')?>
                </a>
                <p></p>
            </div>
        </main><!-- #site-content -->
        <?php
    }
} else {
    _e('<strong>Sorry, no posts matched your criteria.</strong>', 'seminardesk');
}   

get_footer();
?>