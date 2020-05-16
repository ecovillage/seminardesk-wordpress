
<h3>Event Dates for 2020</h3>
<ul>

    <?php
    global $post;
    //$wp_date = date('Y-m-d\TH:i:s', '1585845000000'); //convert timestamp to wordpress date
    $query = new WP_Query(
        array(
            'post_type'     => 'sd_date',
            'post_status'   => 'publish',
            'meta_query'    => array(
                array(
                    'key'       => 'begin_date',
                    'value'     => array(
                        strtotime('01.01.2020')*1000,
                        strtotime('31.12.2020')*1000,
                    ),
                    'type'      => 'numeric',
                    'compare'   => 'BETWEEN',
                ),
            ),
        ),
    );

    // the loop to generate block view/content
    if ( $query->have_posts() ){
        while ( $query->have_posts() ) {
            $query->the_post();
            ?>
            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
            <?php
        }
    }
    else {
        // echo '<p><strong>Sorry, no event date for this period of time found.</strong></p>';
        echo '<p><strong>';
        _e( 'Sorry, no event date for this period of time found.', 'textdomain' );
        echo '</p></strong>';
    }
    wp_reset_query(); // reset query and post

    ?>
</ul>
