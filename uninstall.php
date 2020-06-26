<?php

/**
* Run this on Plugin uninstall
*
* @package SeminardeskPlugin
*/

use Inc\Utils\OptionUtils;

// TODO: untested. needs debugging 

// WP security check when uninstall
defined( 'WP_UNINSTALL_PLUGIN' ) or die ('not allowed to access this file');

// Clear database data
$delete_all = OptionUtils::get_option_or_default(SD_OPTION['delete'], false);
if ( $delete_all == true ) {
    // Get all cpts and delete them
    foreach ( SD_CPT as $key => $value ){
        $cpts = get_posts( array (
            'post_type'     => $key,
            'numberposts'   => -1
        ));
        foreach ( $cpts as $cpt){
            wp_delete_post( $cpt->ID, true);
        }
    }
    // Get all terms of the txn and delete them
    foreach ( SD_TXN as $key => $value){
        get_taxonomies();
        $terms = get_terms( array(
            'taxonomy' => $key,
            'hide_empty' => false,
        ) );
        foreach ( $terms as $term ){
            wp_delete_term( $term, $key );
        }
    }
    // Get all options and delete them
    foreach( SD_OPTION as $key => $value ){
        delete_option( $value );
    }    
}

flush_rewrite_rules();