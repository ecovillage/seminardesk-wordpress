<?php
/**
 * The template for single post of CPT sd_events
 * 
 * @package SeminardeskPlugin
 */

get_header();
if (have_posts()) :
    while (have_posts()) : the_post();
        // echo '<header class="has-text-align-center">';
        echo '<div class="entry-header-inner section-inner small">';
            the_title ('<h2>', '</h2>');
            echo '<div class="post-meta-wrapper post-meta-single post-meta-single-top">';
                // check if the post or page has a Featured Image assigned to it.
                if ( has_post_thumbnail() ) {
                    add_image_size( 'event_thumb_100', 100, 100, true);
                    the_post_thumbnail('event_thumb_100');
                    echo '<p></p>';
                }
                the_content();
                // next_post_link();
            echo '</div>';
        echo '</div>';
        // echo '</header>';
    endwhile;
else :
    _e('<strong>Sorry, no posts matched your criteria.</strong>', 'seminardesk');
endif;

//get_sidebar();
get_footer();
?>