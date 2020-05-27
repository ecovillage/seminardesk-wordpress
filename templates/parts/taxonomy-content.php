<?php
/**
 * The template for taxonomy dates with upcoming event dates
 * 
 * @package SeminardeskPlugin
 */

if ( have_posts() ) {
      while ( have_posts() ) {
      the_post();
      ?>
      <div class="entry-header-inner section-inner small">
            <a href="<?php echo esc_url(get_permalink($post->event_wp_id)); ?>">
                  <?php the_title('<h4>', '</h4>'); ?>
            </a>
      </div>
      <?php
      }
}