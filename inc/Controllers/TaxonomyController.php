<?php

/**
 * The template for taxonomy sd_txn_dates with past event dates
 * 
 * @package SeminardeskPlugin
 */

namespace Inc\Controllers;

use Inc\Utils\OptionUtils;

// Show/Edit Taxonomy:
// http://localhost/wpsdp/wp-admin/edit-tags.php?taxonomy=sd_txn_dates
class TaxonomyController
{
    // public $txns = array();

    /**
     * Register taxonomies via controller class
     *
     * @return void
     */
    public function register()
    {
        $this->public = OptionUtils::get_option_or_default('seminardesk', false);
        add_action( 'init', array($this, 'create_taxonomies') );
        add_action('pre_get_posts', array( $this, 'set_taxonomy_queries'));
    }
    
    /**
     * Get term id and create term if doesn't exist
     * 
     * @param string $term 
     * @param string $txn 
     * @param string $description 
     * @param string $slug 
     * @return array|WP_Error 
     */
    public function check_term_exists( $term, $txn, $description, $slug )
    {
        $term_ids = term_exists( $term, $txn );
        if ( !isset( $term_ids ) ) {
            $term_ids = wp_insert_term( $term, $txn, array(
                'description' => __( $description , 'seminardesk' ),
                'slug' => $slug,
            ));
        }
        return $term_ids;
    }

    /**
     * Update slug of the terms
     * 
     * @return void 
     */
    public function update_terms_slug()
    {
        foreach  (SD_TXN_TERM as $key => $value ) {
			$term = get_term_by('name', $key, $value['taxonomy'], ARRAY_A);
			$slug = OptionUtils::get_option_or_default( SD_OPTION['slugs'], $value['slug_default'],  $value['slug_option_key']);
			wp_update_term( $term['term_id'], $term['taxonomy'],  array( 'slug' => $slug ) );
		}
    }

    /**
     * modify the taxonomy query for sd_txn_dates
     * 
     * @param mixed $query 
     * @return void 
     */
    public function set_taxonomy_queries( $query ) {
        if ( $query->is_tax() && array_key_exists('sd_txn_dates', $query->query) &&$query->is_main_query() ) {
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
        foreach (SD_TXN as $key => $value){
            $name = ucfirst($value['name']);
            $names = ucfirst($value['names']);
            $name_lower = strtolower($value['name']);
            $names_lower = strtolower($value['names']);
            $public = OptionUtils::get_option_or_default( SD_OPTION['debug'], false );
            $slug = OptionUtils::get_option_or_default( SD_OPTION['slugs'], $value['slug_default'], $value['slug_option_key'] );

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
                'show_in_rest'      => false, // true, // http://localhost/wpsdp/wp-json/wp/v2/sd_txn_dates
                //'rest_base'         => 'txn',
                'hierarchical'      => true,
                'rewrite'           => array( 
                    'slug'              => $slug, 
                    'hierarchical'      => true,
                    'with_front'        => false, 
                ),
            );
    
            register_taxonomy( $key, $value['object_type'], $args );

            // for debugging custom post type features... expensive operation. should usually only be called when activate and deactivate the plugin
            //flush_rewrite_rules();
        }

    }
}