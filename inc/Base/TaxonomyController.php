<?php

/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

// Show/Edit Taxonomy:
// http://localhost/wpsdp/wp-admin/edit-tags.php?taxonomy=dates
class TaxonomyController
{
    public $name = 'Date';
    public $name_lower;
    public $names = 'Dates';
    public $names_lower;
    public $slug = 'schedule';

    // public $taxonomy = array(
    //     'names' => 'Dates',
    //     'name' => 'Date',
    // );

    public function register()
    {
        add_action( 'init', array($this, 'create_taxonomy_dates') );
        add_action('pre_get_posts', array( $this, 'posts_orderby_begin_date'));
    }

    public function posts_orderby_begin_date( $query ) {

        if ( $query->is_tax() && array_key_exists('dates', $query->query) &&$query->is_main_query() ) {
            //set some additional query parameters
            $query->set( 'meta_key', 'begin_date' );
            $query->set( 'orderby', 'meta_value_num' );
            $query->set( 'order', 'DESC' );
        }
    }

    public function create_taxonomy_dates()
    {
        $this->name_lower = strtolower($this->name);
        $this->names_lower = strtolower($this->names);

        // Add new taxonomy, make it hierarchical (like categories)
        $labels = array(
            'name'              => _x( $this->names, 'taxonomy general name', 'seminardesk' ),
            'singular_name'     => _x( $this->name, 'taxonomy singular name', 'seminardesk' ),
            'search_items'      => __( 'Search ' . $this->names, 'seminardesk' ),
            'all_items'         => __( 'All ' . $this->names, 'seminardesk' ),
            'parent_item'       => __( 'Parent ' . $this->name, 'seminardesk' ),
            'parent_item_colon' => __( 'Parent ' . $this->name . ':', 'seminardesk' ),
            'edit_item'         => __( 'Edit ' . $this->name, 'seminardesk' ),
            'update_item'       => __( 'Update ' . $this->name, 'seminardesk' ),
            'add_new_item'      => __( 'Add New ' . $this->name, 'seminardesk' ),
            'new_item_name'     => __( 'New ' . $this->name . ' Name', 'seminardesk' ),
            'menu_name'         => __( $this->names, 'seminardesk' ),
            'back_to_items'     => __( $this->names, 'seminardesk' ),
            'not_found'         => __( $this->names, 'seminardesk'),
        );
    
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => true, // http://localhost/wpsdp/wp-json/wp/v2/dates
            //'rest_base'         => 'txn',
            'rewrite'           => array( 
                'slug' => $this->slug, 
                'hierarchical' => true,
                'with_front' => false, 
            ),
        );
    
        register_taxonomy( $this->names_lower, array( 'sd_date' ), $args );
    }
}