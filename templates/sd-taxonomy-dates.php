<?php
/**
 * The template for taxonomy dates by year or month
 * 
 * @package SeminardeskPlugin
 */

use Inc\Base\TemplateTaxonomyDates;

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
            <div class="sd-event">
                <div class="entry-header-inner section-inner small">
                    <a href="<?php echo esc_url(get_permalink($post->event_wp_id)); ?>">
                        <?php the_title('<h3>', '</h3>'); ?>
                    </a>
                    <?php
                    // echo get_permalink( $post->event_wp_id );
                    TemplateTaxonomyDates::get_date('<div class="sd-event-date">', '</div>');
                    TemplateTaxonomyDates::get_facilitators('<div class="sd-event-facilitators">', '</div>');
                    TemplateTaxonomyDates::get_price('<div class="sd-event-price">', '</div>');
                    TemplateTaxonomyDates::get_venue('<div class="sd-event-venue">', '</div>');
                    ?>
                    <div class="sd-event-container">
                        <?php
                        TemplateTaxonomyDates::get_img_remote( $post->teaser_picture_url, '300', '', $alt = __('remote image failed', 'seminardesk'));
                        ?>
                        <div class=sd-event-container-text>
                        <?php the_excerpt(); ?>
                        </div>
                    </div>
                    <a class="button sd-event-more-link" href="<?php echo esc_url(get_permalink($post->event_wp_id)); ?>" class="sd-event-more">
                        <?php esc_html_e('More', 'seminardesk')?>
                    </a>
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