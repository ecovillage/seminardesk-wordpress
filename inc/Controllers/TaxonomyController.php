<?php

/**
 * The template for taxonomy dates with past event dates
 * 
 * @package SeminardeskPlugin
 */

namespace Inc\Controllers;

use Inc\Utils\OptionUtils;

// Show/Edit Taxonomy:
// http://localhost/wpsdp/wp-admin/edit-tags.php?taxonomy=dates
class TaxonomyController
{
    public $txns = array();

    /**
     * Register taxonomies via controller 
     *
     * @return void
     */
    public function register()
    {
        $this->public = OptionUtils::get_option_or_default('sd_debug', false);
        add_action( 'init', array($this, 'create_taxonomies') );
        add_action('pre_get_posts', array( $this, 'set_taxonomy_queries'));
    }
    
    /**
     * modify the taxonomy query for dates
     * 
     * @param mixed $query 
     * @return void 
     */
    public function set_taxonomy_queries( $query ) {
        // modify query of taxonomy dates
        if ( $query->is_tax() && array_key_exists('dates', $query->query) &&$query->is_main_query() ) {
            //set some additional query parameters
            $query->set( 'meta_key', 'sd_date_begin' );
            $query->set( 'orderby', 'meta_value_num' );
            $query->set( 'order', 'ASC' );
            $query->set( 'posts_per_page', '5' );
        }
    }

    /**
     * create taxonomies for SeminarDesk 
     * 
     * @return void
     */
    public function create_taxonomies()
    {
        $this->txns = array(
            array(
                'name' => 'Date',
                'names' => 'Dates',
                'slug' => OptionUtils::get_option_or_default( 'sd_slug_txn_dates', SD_SLUG_TXN_DATES ),
                'object_type' => array( 'sd_date' ),
            ),
        );

        foreach ($this->txns as $txn){
            $name = ucfirst($txn['name']);
            $names = ucfirst($txn['names']);
            $name_lower = strtolower($txn['name']);
            $names_lower = strtolower($txn['names']);
            $public = OptionUtils::get_option_or_default( 'sd_debug', false );

            // Add new taxonomy, make it hierarchical (like categories)
            $labels = array(
                'name'              => _x( $names, 'taxonomy general name', 'seminardesk' ),
                'singular_name'     => _x( $name, 'taxonomy singular name', 'seminardesk' ),
                'search_items'      => __( 'Search ' . $names, 'seminardesk' ),
                'all_items'         => __( 'All ' . $names, 'seminardesk' ),
                'parent_item'       => __( 'Parent ' . $name, 'seminardesk' ),
                'parent_item_colon' => __( 'Parent ' . $name . ':', 'seminardesk' ),
                'edit_item'         => __( 'Edit ' . $name, 'seminardesk' ),
                'update_item'       => __( 'Update ' . $name, 'seminardesk' ),
                'add_new_item'      => __( 'Add New ' . $name, 'seminardesk' ),
                'new_item_name'     => __( 'New ' . $name . ' Name', 'seminardesk' ),
                'menu_name'         => __( $names, 'seminardesk' ),
                'back_to_items'     => __( $names, 'seminardesk' ),
                'not_found'         => __( $names, 'seminardesk'),
            );
        
            $args = array(
                'hierarchical'      => true,
                'labels'            => $labels,
                'public'            => $public,
                'show_admin_column' => true,
                'query_var'         => true,
                'show_in_rest'      => true, // http://localhost/wpsdp/wp-json/wp/v2/dates
                //'rest_base'         => 'txn',
                'hierarchical'      => true,
                'rewrite'           => array( 
                    'slug'              => $txn['slug'], 
                    'hierarchical'      => true,
                    'with_front'        => false, 
                ),
            );
    
            register_taxonomy( $names_lower, $txn['object_type'], $args );
        }

    }
}