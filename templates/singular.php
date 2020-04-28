<?php
/**
 * @package SeminardeskPlugin
 * singular post template is used, when no specific post template for CPT is found.
 * simple template for CPT sd_event
 * source: https://developer.wordpress.org/themes/basics/the-loop/#the-loop-in-detail
 */
?>

<?php get_header(); ?>

<h2> <?php the_title(); ?> </h2>

<?php
if (have_posts()) :
    while (have_posts()) : the_post();
        // check if the post or page has a Featured Image assigned to it.
        if ( has_post_thumbnail() ) {
            add_image_size( 'event_thumb_100', 100, 100, true);
            the_post_thumbnail('event_thumb_100');
            echo '<p></p>';
        }
        the_content();
    endwhile;
else :
    _e('<strong>Sorry, no posts matched your criteria.</strong>', 'textdomain');
endif;

//get_sidebar();
get_footer();
?>