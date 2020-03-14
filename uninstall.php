<?php

/**
* Run this on Plugin uninstall
*
* @package SDCP
*/

// security check if unistall is trickered by wordpress
defined( 'WP_UNINSTALL_PLUGIN' ) or die ('not allowed to access this file');

// Clear database data
// Get all sd events
$sd_events = get_posts( array( 'post_type' => "sd_event", 'numberposts' => -1 ) );
// delete all sd_events CTP
foreach ( $sd_events as $sd_event ) {
    wp_delete_post( $sd_event->ID, true );
}

// // Access directly the DB via SQL and delete sd event entries
// // more disruptive...
// global $wpdb;
// // delete all CPT sd_event
// $wpdb->query( *DELETE FROM wp_posts WHERE post_type     = 'sd_event'* );
// // delete all post metadata, which is not associalted with a post
// $wpdb->query( *DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT id FROM wp_posts)* );
// $wpdb->query( *DELETE FROM wp_term_relationships WHERE opject_id NOT IN (SELECT id FROM wp_posts)* );
