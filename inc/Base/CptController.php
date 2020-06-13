<?php

/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

use Inc\Base\OptionUtils;

/**
 * Handles CPTs during init and activation
 */
class CptController
{
    public $cpts = array();

    /**
     * Register cpts via controller
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
        $this->cpts = array(
            array(
                'name' => 'Event',
                'names' => 'Events',
                'menu_position' => 1,
                'slug' => OptionUtils::get_option_or_default( 'sd_slug_cpt_events', SD_SLUG_CPT_EVENTS ),
                'taxonomies' => array (),
            ),
            array(
                'name' => 'Date',
                'names' => 'Dates',
                'menu_position' => 2,
                'slug' => OptionUtils::get_option_or_default( 'sd_slug_cpt_dates', SD_SLUG_CPT_DATES ),
                'taxonomies' => array ( 'dates' ),
            ),
            array(
                'name' => 'Facilitator',
                'names' => 'Facilitators',
                'menu_position' => 3,
                'slug' => OptionUtils::get_option_or_default( 'sd_slug_cpt_facilitators', SD_SLUG_CPT_FACILITATORS ),
                'taxonomies' => array (),
            ),
        );

        foreach ($this->cpts as $cpt){
            $name = ucfirst($cpt['name']);
            $names= ucfirst($cpt['names']);
            $name_lower = strtolower($cpt['name']);
            $names_lower = strtolower($cpt['names']);
            $public = OptionUtils::get_option_or_default( 'sd_debug', false );

            /**
             * array to configure labels for the CPT 
             */
            $labels = [
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
            ];

            /**
             * array to set rewrite rules for the CPT (sub CPT option)
             */
            $rewrite = [
                'slug' => $cpt['slug'],
            ];

            /**
             * array to registers supported features for the CPT (sub CPT option)
             */
            $supports = [
                'title', 
                'editor', 
                'author', 
                //'thumbnail', 
                'excerpt', 
                'revisions', 
                'custom-fields', // enable support of Meta API
                'page-attributes', // template and parent, hierarchical must be true for parent option
                //'post-formats',
            ];


            /**
             * array to configure CPT options
             */
            // TODO: register custom capabilities
            // TODO: handle scheme and it's callbacks proper
            // FIXME: load RestController class directly as rest_controller_class breaks the WordPress editor
            $cptOptions =  [
                'labels'            => $labels,
                'description'       => __( $name . ' post type for SeminarDesk.', 'seminardesk' ),
                'has_archive'       => true, // false,
                'show_in_rest'      => true,  //enable rest api
                'rest_base'         => 'sd_' . $names_lower,
                // 'rest_controller_class' => 'Inc\Base\RestController', // use custom WP_REST_Controller for custom post type ... CPT will not be within the wp/v2 namespace
                'public'            => $public,
                'show_in_menu'      => 'seminardesk_plugin', // add post type to the seminardesk menu
                'menu_position'     => $cpt['menu_position'],
                // 'hierarchical'      => true, // hierarchical must be true for parent option
                'supports'          => $supports,
                'capability_type'   => 'post',
                'rewrite'           => $rewrite,
                'taxonomies'        => array( 'dates' ),
            ];

            register_post_type( 'sd_' . $name_lower, $cptOptions ); 

            // for debugging custom post type features... expensive operation. should usually only be called when activate and deactivate the plugin
            //flush_rewrite_rules();
        } 

    }
}