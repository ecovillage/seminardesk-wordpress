<?php
/**
 * The template for taxonomy dates
 * 
 * @package SeminardeskPlugin
 */

use Inc\Base\TaxonomyDatesWrapper;

get_header();
?>

<main id="site-content" role="main">

    <?php

	// $archive_title    = get_the_archive_title();
    // $term_title = get_the_archive_description();
    // $test = single_term_title( '', false );
    // $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
    // $is_tax = is_tax();
    // $is_archive = is_archive();
    // $term1 = get_term(131);

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
    // TODO: include template part for repeating code
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
                TaxonomyDatesWrapper::get_date('<p>', '</p>');
                TaxonomyDatesWrapper::get_facilitators('<p>', '</p>');
                TaxonomyDatesWrapper::get_price('<p>', '</p>');
                TaxonomyDatesWrapper::get_venue('<p>', '</p>');
                TaxonomyDatesWrapper::get_img_remote( $post->teaser_picture_url, '300', '', $alt = "remote image load failed", '<p>', '</p>' );
                // check if the post or page has a Featured Image assigned to it.
                if ( has_post_thumbnail() ) {
                    add_image_size( 'event_thumb_300', 300, 300, true);
                    the_post_thumbnail('event_thumb_300');
                    echo '<p></p>';
                }
                the_excerpt();
                ?><p><?php esc_html_e( 'Meta information for this post:', 'textdomain' ); ?></p>
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
