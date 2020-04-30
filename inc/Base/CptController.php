<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

// TODO: create modular cpt manager class (CPT API) to handel different CPTs (event, calender, facilitators)
// TODO: implement and utilize shortcode [] to generate posts

class CptController 
{
    public $name;
    public $names;
    public $menu_position;

    public function register()
    {
        // register cpt in WordPress
        add_action( 'init', array( $this, 'create_cpt' ) );
        
    }
  
    public function create_cpt()
    {
        $name_lower = strtolower($this->name);
        $names_lower = strtolower($this->names);

        /**
         * array to configure labels for the CPT 
         */
        $labels = [
            'name'                  => _x( $this->names, 'post type general name', 'seminardesk-connector' ),
            'singular_name'         => _x( $this->name, 'post type singular name', 'seminardesk-connector'),
            'name_admin_bar'        => _x( $this->name, 'add new on admin bar', 'seminardesk-connector' ),
            'add_new'               => _x( 'Add New', 'event', 'seminardesk-connector' ),
            'add_new_item'          => __( 'Add New ' . $this->name, 'seminardesk-connector' ),
            'new_item'              => __( 'New ' . $this->name, 'seminardesk-connector' ),
            'edit_item'             => __( 'Edit ' . $this->name, 'seminardesk-connector' ),
            'view_item'             => __( 'View ' . $this->name, 'seminardesk-connector' ),
            'view_items'            => __( 'View ' . $this->names, 'seminardesk-connector' ),
            // 'all_items'          => __( 'All ' . $this->name_plural, 'seminardesk-connector' ),
            'all_items'             => __( $this->name . ' Editor', 'seminardesk-connector' ),
            'search_items'          => __( 'Search ' . $this->names, 'seminardesk-connector' ),
            'parent_item_colon'     => __( 'Parent ' . $this->names . ':', 'seminardesk-connector' ),
            'not_found'             => __( 'No ' . $names_lower . ' found.', 'seminardesk-connector' ),
            'not_found_in_trash'    => __( 'No ' . $names_lower . ' found in Trash.', 'seminardesk-connector' ),
            'parent_item_colon'     => __( 'Parent ' . $this->name, 'seminardesk-connector' ),
            'archives'              => __( $this->name . ' Archives', 'seminardesk-connector' ),
            'attributes'            => __( $this->name . ' Attributes', 'seminardesk-connector' ),
            'insert_into_item'      => __( 'Insert into ' . $name_lower, 'seminardesk-connector' ),
            'uploaded_to_this_item' => __( 'Uploaded to this ' . $name_lower, 'seminardesk-connector' ),
        ];

        /**
         * array to set rewrite rules for the CPT (sub CPT option)
         */
        $rewrite = [
            'slug' => 'sd_' . $names_lower,
        ];

        /**
         * array to registers supported features for the CPT (sub CPT option)
         */
        $supports = [
            'title', 
            'editor', 
            'author', 
            'thumbnail', 
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
            'description'       => __( $this->name . ' post type for SeminarDesk.', 'seminardesk-connector' ),
            'has_archive'       => true,
            'show_in_rest'      => true,  //enable rest api
            'rest_base'         => 'sd_' . $names_lower,
            // 'rest_controller_class' => 'Inc\Base\RestController', // use custom WP_REST_Controller for custom post type ... CPT will not be within the wp/v2 namespace
            'public'            => true,
            'show_in_menu'      => 'seminardesk_plugin', // add post type to the seminardesk menu
            'menu_position'     => $this->menu_position,
            // 'hierarchical'      => true, // hierarchical must be true for parent option
            'supports'          => $supports,
            'capability_type'     => 'page',
            'rewrite'           => $rewrite,
        ];

        register_post_type( 'sd_' . $name_lower, $cptOptions ); 

        // for debugging custom post type features... expensive operation. should usually only be called when activate and deactivate the plugin
        // flush_rewrite_rules();
    }
}