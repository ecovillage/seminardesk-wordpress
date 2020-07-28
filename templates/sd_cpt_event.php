ALL<?php
/**
 * The template for single post of CPT sd_cpt_event
 * 
 * @package SeminardeskPlugin
 */

use Inc\Utils\TemplateUtils as Utils;

get_header();
?>
<main id="site-content" role="main">
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            ?>
            <header class="entry-header has-text-align-center">
                <div class="entry-header-inner section-inner medium">
                    <?php 
                    Utils::get_value_by_language( $post->sd_data['title'], 'DE', '<h1 class="archive-title">', '</h1>', true); 
                    echo Utils::get_value_by_language($post->sd_data['subtitle']); 
                    $url = Utils::get_value_by_language($post->sd_data['headerPictureUrl']);
                    echo Utils::get_img_remote( $url, '300', '', $alt = __('remote image failed', 'seminardesk'))
                    ?>
                </div>
            </header>
            <div class="post-meta-wrapper post-meta-single post-meta-single-top">
                <p>
                    <?php
                    $facilitators = Utils::get_facilitators($post->sd_data['facilitators']);
                    if ($facilitators) {
                        echo '<strong>';
                        _e('Facilitator: ', 'seminardesk');
                        echo '</strong>';
                        echo $facilitators;
                    }
                    ?>
                </p>
                <p>
                    <?php
                    echo Utils::get_value_by_language($post->sd_data['description']);
                    ?>
                </p>
                <?php
                    // get list of all dates for this event
                    $booking_list = Utils::get_event_dates_list( $post->sd_event_id );
                    $booking_url = esc_url( Utils::get_value_by_language( $post->sd_data['bookingPageUrl'] ?? null ) );
                    if ( $booking_list ){
                        ?>
                        <h4>
                            <?php 
                            _e('List of available dates:', 'seminardesk');
                            ?>
                        </h4>
                        <p>
                        <?php
                        echo $booking_list;
                        
                        if ( !empty($booking_url) ) {
                            ?>
                            <br><p><button class="sd-modal-booking-btn">
                                <?php 
                                _e('Booking', 'seminardesk');
                                ?>
                            </button></p>
                            </p>
                            <?php
                        }
                    } else {
                        echo '<h4>';
                        _e('No dates for this event available :(', 'seminardesk');
                        echo '</h4>';
                    }
                    ?>
            </div>
            <!-- BEGIN modal content -->
            <div class="sd-modal">
                <div class="sd-modal-content">
                    <span class="sd-modal-close-btn">&times;</span>
                    <h4 class="sd-modal-title"><?php _e('Booking', 'seminardesk');?></h4>
                    <iframe class="sd-modal-booking" src="<?php echo $booking_url ?>/embed" title="Seminardesk Booking"></iframe>
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
    wp_reset_query();
    ?>

</main><!-- #site-content -->

<?php
get_footer();