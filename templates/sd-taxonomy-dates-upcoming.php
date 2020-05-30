<?php
/**
 * The template for taxonomy dates with upcoming event dates
 * 
 * @package SeminardeskPlugin
 */

use Inc\Base\TemplateTaxonomyDates;

global $wp_query;

$title = __( 'Upcoming Event Dates', 'seminardesk');
$timestamp_today = strtotime(wp_date('Y-m-d'));
// $timestamp_today = strtotime('2020-04-01');

$wp_query = new WP_Query(
     array(
         'post_type'    => 'sd_date',
         'post_status'  => 'publish',
         'meta_key'     => 'begin_date',
         'orderby'      => 'meta_value_num',
         'order'        => 'ASC',
         'meta_query'   => array(
            'key'       => 'begin_date',
            'value'     => $timestamp_today*1000, //in ms
            'type'      => 'numeric',
            'compare'   => '>=',
         ),
     )
);

get_header();
?>
<main id="site-content" role="main">
    
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
            <div class="entry-header-inner section-inner small">
                <a href="<?php echo esc_url(get_permalink($post->event_wp_id)); ?>">
                    <?php the_title('<h4>', '</h4>'); ?>
                </a>
                <?php
                // echo get_permalink( $post->event_wp_id );
                TemplateTaxonomyDates::get_date('<p>', '</p>');
                TemplateTaxonomyDates::get_facilitators('<p>', '</p>');
                TemplateTaxonomyDates::get_price('<p>', '</p>');
                TemplateTaxonomyDates::get_venue('<p>', '</p>');
                TemplateTaxonomyDates::get_img_remote( $post->teaser_picture_url, '300', '', $alt = "remote image load failed", '<p>', '</p>' );
                // check if the post or page has a Featured Image assigned to it.
                if ( has_post_thumbnail() ) {
                    add_image_size( 'event_thumb_300', 300, 300, true);
                    the_post_thumbnail('event_thumb_300');
                    echo '<p></p>';
                }
                the_excerpt();
                ?>
                <a href="<?php echo esc_url(get_permalink($post->event_wp_id)); ?>">
                    <?php esc_html_e('More ...', 'seminardesk')?>
                </a>
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
                _e('<strong>Sorry, no events for this date.</strong>', 'seminardesk');
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


