<?php
/**
 * The template for single post of CPT sd_events
 * 
 * @package SeminardeskPlugin
 */

use Inc\Base\TemplateCptEvents;


get_header();
?>
<main id="site-content" role="main">
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            ?>
            <header class="entry-header has-text-align-center<?php echo esc_attr( $entry_header_classes ); ?>">
                <div class="entry-header-inner section-inner medium">
                    <?php 
                    the_title( '<h1 class="archive-title">', '</h1>' );
                    echo TemplateCptEvents::get_value_by_language($post->data['subtitle']); 
                    $url = TemplateCptEvents::get_value_by_language($post->data['headerPictureUrl']);
                    echo TemplateCptEvents::get_img_remote( $url, '300', '', $alt = __('remote image failed', 'seminardesk'))
                    ?>
                </div>
            </header>
            <div class="post-meta-wrapper post-meta-single post-meta-single-top">
                <p>
                    <strong>
                    <?php
                    _e('<strong>Facilitators: </strong>', 'seminardesk');
                    ?>
                    </strong>
                    <?php
                    echo TemplateCptEvents::get_facilitators($post->data['facilitators']);
                    ?>
                </p>
                <p>
                    <?php
                    echo TemplateCptEvents::get_value_by_language($post->data['teaser']);
                    ?>
                </p>
                <p><button class="sd-modal-more-btn">
                    <?php 
                    _e('More', 'seminardesk');
                    ?>
                </button></p>
                <p><strong>
                    <?php 
                    _e('List of available dates:', 'seminardesk');
                    ?>
                </strong></p>
                <p>
                    <?php
                    // get list of all dates for this event
                    echo TemplateCptEvents::get_event_dates_list( $post->data['id'] );
                    ?>
                </p>
                <p><button class="sd-modal-booking-btn">
                    <?php 
                    _e('Booking', 'seminardesk');
                    ?>
                </button></p>

                <?php
                // check if the post or page has a Featured Image assigned to it.
                // if ( has_post_thumbnail() ) {
                //     add_image_size( 'event_thumb_100', 100, 100, true);
                //     the_post_thumbnail('event_thumb_100');
                //     echo '<p></p>';
                // }
                ?>
            </div>
            <!-- BEGIN modal content -->
            <div class="sd-modal">
                <div class="sd-modal-content">
                    <span class="sd-modal-close-btn">×</span>
                    <div class="sd-modal-more">
                        <?php 
                        echo TemplateCptEvents::get_value_by_language($post->data['description']);
                        ?>
                    </div>
                    <div>
                        <!-- TODO: only load booking page, when pressing button to show booking content?  -->
                        <iframe class="sd-modal-booking" src="https://booking.seminardesk.de/en/schloss-tempelhof/927cd29a247a4cfdba3fad6b3335a430/market-garden-system---nachhaltig--regenerativ/embed" title="Seminardesk Booking">Booking Offline</iframe>
                    </div>
                </div>
            </div>
            <!-- END modal content -->
            <?php
        }
    } else {
        ?>
        <div class="entry-header-inner section-inner small has-text-align-center">
            <h5>
                <?php
                _e('<strong>Sorry, event does not exist.</strong>', 'seminardesk');
                ?>
            </h5>
            <br>
        </div>
        <?php
    }
    // TODO: reset query necessary ... better use wp_reset_postdata()?
    wp_reset_query();
    ?>

</main><!-- #site-content -->

<?php
get_footer();