<?php
/**
 * The template for taxonomy dates
 * 
 * @package SeminardeskPlugin
 */

/**
 * Get start and end date of the event
 *
 * @param string $before
 * @param string $after
 * @param boolean $echo
 * @return string
 */
function sd_get_date( $before = '', $after = '', $echo = true )
{
    global $post;
    $date = array(
        'begin' => date_i18n('l d.m.Y', $post->begin_date/1000),
        'end'  => date_i18n('l d.m.Y', $post->end_date/1000),
    );
    $response = $before . $date['begin'] . ' - ' . $date['end'] . $after;

    if ( $echo ){
        echo $response;
    }

    return $response;
}

function sd_get_facilitators( $before = '', $after = '' , $echo = true )
{
    global $post;
    $facilitators = array();
    // query all facilitators from the database
    $query = new WP_Query(
        array(
            'post_type'     => 'sd_facilitator',
            'post_status'   => 'publish',
        )
    );
    $facilitator_posts = $query->get_posts();
    // get facilitator name from CPT sd_facilitators for all facilitator ids of $posts
    $ids = $post->facilitator_ids;
    if (is_array($ids)){
        foreach ( $ids as $key => $value){
            foreach ( $facilitator_posts as $facilitator_post){
                $test_id = $facilitator_post->facilitator_id;
                if ( $facilitator_post->facilitator_id == $value['id']){
                    array_push($facilitators, get_the_title($facilitator_post));
                }
            }
        }
    }
    // sort array of received facilitator names ascending
    sort($facilitators);

    if ( !empty($facilitators) ){
        $response = $before . __('Facilitators: ', 'seminardesk'). implode(" | ",$facilitators) . $after;
    }else{
        $response = null;
    }

    if ( $echo ){
        echo $response;
    }

    return $response;
}

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
	if ( have_posts() ) {
		while ( have_posts() ) {
            the_post();
            ?>
            <div class="entry-header-inner section-inner small">
                <?php
                the_title('<h4>', '</h4>');
                sd_get_date( '<p>', '</p>');
                sd_get_facilitators( '<p>', '</p>');
                // check if the post or page has a Featured Image assigned to it.
                if ( has_post_thumbnail() ) {
                    add_image_size( 'event_thumb_100', 300, 300, true);
                    the_post_thumbnail('event_thumb_100');
                    echo '<p></p>';
                }
                the_excerpt();
                ?>
            </div>
            <?php
            
		}
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
	?>

</main><!-- #site-content -->

<?php
get_footer();
