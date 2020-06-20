<h1>Event List</h1>

<ul>
    <?php
    global $post;
 
    $myposts = get_posts( array(
        'numberposts' => -1, // all events
        'post_type' => 'sd_cpt_event',
        'post_status' => 'any',
    ) );
 
    if ( $myposts ) {
        foreach ( $myposts as $post ) : 
            setup_postdata( $post ); ?>
            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
        <?php
        endforeach;
        wp_reset_postdata();
    }
    ?>
</ul>