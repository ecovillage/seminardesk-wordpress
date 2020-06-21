<?php
/**
 * The template for taxonomy sd_txn_dates by year or month
 * 
 * @package SeminardeskPlugin
 */

use Inc\Utils\TemplateUtils as Utils;

get_header();
?>

<main id="site-content" role="main">

    <?php
    $txn = get_taxonomy(get_query_var( 'taxonomy' ));
    $title = ucfirst($txn->rewrite['slug']) . ': '. get_queried_object()->name;
    ?>
    
    <header class="archive-header has-text-align-center header-footer-group">

        <div class="archive-header-inner section-inner medium">

            <?php if ( $title ) { ?>
                <h1 class="archive-title"><?php echo wp_kses_post( $title ); ?></h1>
            <?php } ?>

        </div><!-- .archive-header-inner -->

    </header><!-- .archive-header -->

    <?php
	if ( have_posts() ) {
		while ( have_posts() ) {
            the_post();
            ?>
            <div class="sd-event">
                <div class="entry-header-inner section-inner small">
                    <div class="sd-event-title">
                        <a href="<?php echo esc_url(get_permalink($post->wp_event_id)); ?>">
                            <?php 
                            Utils::get_value_by_language( $post->sd_data['title'], 'DE', '<h3>', '</h3>', true); 
                            ?>
                        </a>
                    </div>
                    <div class="sd-event-container">
                        <div class="sd-event-props">
                            <?php
                            Utils::get_date( $post->sd_date_begin, $post->sd_date_end, '<div class="sd-event-date">' . __('<strong>Date: </strong>', 'seminardesk'), '</div>', true);
                            Utils::get_facilitators($post->sd_data['facilitators'], '<div class="sd-event-facilitators"><strong>' . __('Facilitator: ', 'seminardesk') . '</strong>', '</div>', true);
                            Utils::get_value_by_language($post->sd_data['priceInfo'], 'DE', '<div class="sd-event-price"><strong>' . __('Price: ', 'seminardesk') . '</strong>', '</div>', true );
                            Utils::get_venue($post->sd_data['venue'], '<div class="sd-event-venue"><strong>' . __('Venue: ', 'seminardesk') . '</strong>', '</div>', true);
                            ?>
                        </div>
                        <div class=sd-event-image>
                            <?php
                            Utils::get_img_remote(  Utils::get_value_by_language($post->sd_data['teaserPictureUrl']), '300', '', $alt = __('remote image failed', 'seminardesk'), '', '', true);
                            ?>
                        </div>
                        <div class=sd-event-teaser>
                            <?php 
                            echo Utils::get_value_by_language($post->sd_data['teaser']) 
                            ?>
                            <a class="button sd-event-more-link" href="<?php echo esc_url(get_permalink($post->wp_event_id)); ?>" class="sd-event-more">
                                <?php esc_html_e('More', 'seminardesk')?>
                            </a>
                        </div>
                    </div>
                    
                </div>
            </div>
        <?php
        }?>
        <div class="has-text-align-center">
            <br><p><?php echo get_posts_nav_link();?></p>
        </div>
        <?php
	} else {
        ?>
        <div class="entry-header-inner section-inner small has-text-align-center">
            <h5>
                <?php
                _e('Sorry, no events for this date.', 'seminardesk');
                ?>
            </h5>
            <br>
        </div>
        <?php

    }
	?>

</main><!-- #site-content -->

<?php
get_footer();