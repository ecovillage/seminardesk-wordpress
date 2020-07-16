<?php
/**
 * The template for taxonomy sd_txn_dates with upcoming event dates
 * 
 * @package SeminardeskPlugin
 */

use Inc\Utils\TemplateUtils as Utils;

$title = __( 'Upcoming Event Dates', 'seminardesk');
$timestamp_today = strtotime(wp_date('Y-m-d'));
// $timestamp_today = strtotime('2020-04-01');

// nav page and pagination for custom query
if ( get_query_var( 'paged' ) ) {
	$paged = get_query_var( 'paged' );
} else if ( get_query_var( 'page' ) ) {
	// This will occur if on front page.
	$paged = get_query_var( 'page' );
} else {
	$paged = 1;
}

$wp_query = new WP_Query(
     array(
        'post_type'        => 'sd_cpt_date',
        'post_status'      => 'publish',
        'posts_per_page'   => '5',
        'paged'            => $paged,
        'meta_key'         => 'sd_date_begin',
        'orderby'          => 'meta_value_num',
        'order'            => 'ASC',
        'meta_query'       => array(
            'key'       => 'sd_date_begin',
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
                <h1 class="archive-title"><?php echo $title; ?></h1>
            <?php } ?>
        </div><!-- .archive-header-inner -->
    </header><!-- .archive-header -->
    
    <?php
    $term_set = '';
	if ( have_posts() ) {
		while ( have_posts() ) {
            the_post();
            $post_event = get_post( $post->wp_event_id );
            ?>
            <div class="entry-header-inner section-inner small">
                <?php
                $term = get_the_terms( $post, 'sd_txn_dates' );
                if ($term['0']->description != $term_set ){
                    $term_set = $term['0']->description;
                    echo '<h4>' . $term_set . '</h4>';
                }
                ?>
                <a href="<?php echo esc_url(get_permalink($post->wp_event_id)); ?>">
                    <?php Utils::get_value_by_language( $post->sd_data['title'], 'DE', '<h4>', '</h4>', true); ?>
                </a>
                <?php
                Utils::get_date( $post->sd_data['beginDate'], $post->sd_data['endDate'], '<p><strong>' . __('Date: ', 'seminardesk') . '</strong>', '</p>', true);
                Utils::get_facilitators( $post_event->sd_data['facilitators'], '<p><strong>' . __('Facilitator: ', 'seminardesk') . '</strong>', '</p>', true );
                echo Utils::get_value_by_language( $post->sd_data['priceInfo'], 'DE', '<p><strong>' . __('Price: ', 'seminardesk') . '</strong>', '</p>' );
                Utils::get_venue( $post->sd_data['venue'], '<p><strong>' . __('Venue: ', 'seminardesk') . '</strong>', '</p>', true);
                Utils::get_img_remote( Utils::get_value_by_language($post_event->sd_data['teaserPictureUrl']), '300', '', $alt = "remote image load failed", '<p>', '</p>', true );
                Utils::get_value_by_language( $post_event->sd_data['teaser'], 'DE',  '<p>', '</p>', true );
                ?>
                <a href="<?php echo esc_url(get_permalink($post->wp_event_id)); ?>">
                    <?php esc_html_e('More ...', 'seminardesk')?>
                </a>
            </div>
            <?php
            
        }?>
        <div class="has-text-align-center">
            <br><p>
                <?php

                // // post nav for custom query
                // next_posts_link( __('« Previous events', 'seminardesk'), $wp_query->max_num_pages );  
                // $next_nav = get_previous_posts_link( __('Next events »', 'seminardesk'), $wp_query->max_num_pages );
                // $separator = ' — ';
                // if ( $next_nav ){
                //     echo $separator;
                //     echo $next_nav;
                // }
                
                // pagination for custom query
                $big = 999999999; // need an unlikely integer
                echo paginate_links( array(
                    'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                    'format'    => '?paged=%#%',
                    'current'   => max( 1, get_query_var('paged') ),
                    'total'     => $wp_query->max_num_pages,
                    'prev_text' => __('« Previous', 'seminardesk'),
                    'next_text' => __('Next »', 'seminardesk'),
                ) );
                ?>
            </p>
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
    wp_reset_query();
	?>

</main><!-- #site-content -->

<?php
get_footer();


