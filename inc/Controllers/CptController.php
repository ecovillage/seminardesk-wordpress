<?php

/**
 * @package SeminardeskPlugin
 */

namespace Inc\Controllers;

use Inc\Utils\OptionUtils;

/**
 * Handles CPTs during init and activation
 */
class CptController
{
    // public $cpts = array();

    /**
     * Register cpts via controller class
     *
     * @return void
     */
    public function register()
    {
        // register cpt in WordPress
        add_action( 'init', array( $this, 'create_cpts' ) );
    }

    /**
     * create custom post types for SeminarDesk
     * 
     * @return void 
     */
    public function create_cpts( )
    {
        foreach (SD_CPT as $key => $value){
            $name = ucfirst($value['name']);
            $names= ucfirst($value['names']);
            $name_lower = strtolower($value['name']);
            $names_lower = strtolower($value['names']);
            $show_ui = OptionUtils::get_option_or_default( SD_OPTION['debug'], false );
            $slug = OptionUtils::get_option_or_default( SD_OPTION['slugs'] , $value['slug_default'], $value['slug_option_key'] );

            /**
             * array to configure labels for the CPT 
             */
            $labels = array(
                'name'                  => _x( $names, 'post type general name', 'seminardesk' ),
                'singular_name'         => _x( $name, 'post type singular name', 'seminardesk'),
                'name_admin_bar'        => _x( $name, 'add new on admin bar', 'seminardesk' ),
                'add_new'               => _x( 'Add New', 'event', 'seminardesk' ),
                'add_new_item'          => __( 'Add New ' . $name, 'seminardesk' ),
                'new_item'              => __( 'New ' . $name, 'seminardesk' ),
                'edit_item'             => __( 'Edit ' . $name, 'seminardesk' ),
                'view_item'             => __( 'View ' . $name, 'seminardesk' ),
                'view_items'            => __( 'View ' . $names, 'seminardesk' ),
                // 'all_items'          => __( 'All ' . $names, 'seminardesk' ),
                'all_items'             => __( 'CPT ' . $name, 'seminardesk' ),
                'search_items'          => __( 'Search ' . $names, 'seminardesk' ),
                'parent_item_colon'     => __( 'Parent ' . $names . ':', 'seminardesk' ),
                'not_found'             => __( 'No ' . $names_lower . ' found.', 'seminardesk' ),
                'not_found_in_trash'    => __( 'No ' . $names_lower . ' found in Trash.', 'seminardesk' ),
                'parent_item_colon'     => __( 'Parent ' . $name, 'seminardesk' ),
                'archives'              => __( $name . ' Archives', 'seminardesk' ),
                'attributes'            => __( $name . ' Attributes', 'seminardesk' ),
                'insert_into_item'      => __( 'Insert into ' . $name_lower, 'seminardesk' ),
                'uploaded_to_this_item' => __( 'Uploaded to this ' . $name_lower, 'seminardesk' ),
            );

            /**
             * array to set rewrite rules for the CPT (sub CPT option)
             */
            $rewrite = array(
                'slug' => $slug,
            );

            /**
             * array to registers supported features for the CPT (sub CPT option)
             */
            $supports = array(
                'title', 
                'editor', 
                'author', 
                //'thumbnail', 
                'excerpt', 
                'revisions', 
                'custom-fields', // enable support of Meta API
                'page-attributes', // template and parent, hierarchical must be true for parent option
                //'post-formats',
            );

            /**
             * array to configure CPT options
             */
            $cptOptions =  array(
                'labels'                => $labels,
                'description'           => __( $name . ' post type for SeminarDesk.', 'seminardesk' ),
                'has_archive'           => $value['has_archive'],
                'show_in_rest'          => false,
                'public'                => $value['public'],
                'exclude_from_search'   => $value['exclude_from_search'],
                'show_ui'               => $show_ui,
                'show_in_menu'          => SD_ADMIN['page'], // add post type to the seminardesk menu
                'menu_position'         => $value['menu_position'],
                'supports'              => $supports,
                'rewrite'               => $rewrite,
                'taxonomies'            => $value['taxonomies'],
            );

            register_post_type( $key, $cptOptions ); 

            // for debugging custom post type features... expensive operation. should usually only be called when activate and deactivate the plugin
            // flush_rewrite_rules();
        } 

    }
}