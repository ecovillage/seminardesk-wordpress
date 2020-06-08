<?php
/**
 * The fallback template for single post
 * used when specific single post template for CPT doesn't exist.
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
                the_content();
            echo '</div>';
        echo '</div>';
        // echo '</header>';
    endwhile;
else :
    _e('<strong>Sorry, no posts matched your criteria.</strong>', 'seminardesk');
endif;

get_footer();
?>