<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\CPT;

// TODO: create modular cpt manager class (CPT API) to handel different CPTs (event, calender, facilitators)
// TODO: implement and utilize shortcode [] to generate posts

class Events 
{
    public function register()
    {
        // register cpt in WordPress
        add_action( 'init', array( $this, 'create_cpt' ) );
        // loads custom template for given post, if exists
        add_filter('single_template', [ $this, 'custom_template' ]);
    }

    public function custom_template( $single )
    {
        // TODO: allow individual custom templates for each slug of CPT sd_ ... checks for custom template for current post by given post type and slug
        // post object for the current (custom) post
        global $post;

        // checks for custom template by given (custom) post type if $single not defined
        if ( empty( $single ) && strpos($post->post_type, 'sd_' ) !== false)
        {
            if ( file_exists( SD_PLUGIN_PATH . 'templates/single-' . $post->post_type . '.php' ) ) 
            {
                return SD_PLUGIN_PATH . 'templates/single-' . $post->post_type . '.php' ;
            }
            return SD_PLUGIN_PATH . 'templates/singular.php';
        }
        return $single;
    }
  
    public function create_cpt()
    {
        /**
         * array to configure labels for the CPT 
         */
        $labels = [
            'name'               => _x( 'Events', 'post type general name', 'seminardesk-connector' ),
            'singular_name'      => _x( 'Event', 'post type singular name', 'seminardesk-connector'),
            'name_admin_bar'     => _x( 'Event', 'add new on admin bar', 'seminardesk-connector' ),
            'add_new'            => _x( 'Add New', 'event', 'seminardesk-connector' ),
            'add_new_item'       => __( 'Add New Event', 'seminardesk-connector' ),
            'new_item'           => __( 'New Event', 'seminardesk-connector' ),
            'edit_item'          => __( 'Edit Event', 'seminardesk-connector' ),
            'view_item'          => __( 'View Event', 'seminardesk-connector' ),
            // 'all_items'          => __( 'All Events', 'seminardesk-connector' ),
            'all_items'          => __( 'Event Editor', 'seminardesk-connector' ),
            'search_items'       => __( 'Search Events', 'seminardesk-connector' ),
            'parent_item_colon'  => __( 'Parent Events:', 'seminardesk-connector' ),
            'not_found'          => __( 'No events found.', 'seminardesk-connector' ),
            'not_found_in_trash' => __( 'No events found in Trash.', 'seminardesk-connector' ),
        ];

        /**
         * array to set rewrite rules for the CPT (sub CPT option)
         */
        $rewrite = [
            'slug' => 'sd_events',
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
            'page-attributes',
            'post-formats',
        ];


        /**
         * array to configure CPT options
         */
        // TODO: register custom capabilities
        // TODO: handle scheme and it's callbacks proper
        // FIXME: load RestController class directly as rest_controller_class breaks the WordPress editor
        $cptOptions =  [
            'labels'            => $labels,
            'description'       => __( 'Event post type for SeminarDesk.', 'seminardesk-connector' ),
            'has_archive'       => true,
            'show_in_rest'      => true,  //enable rest api
            'rest_base'         => 'sd_events',
            // 'rest_controller_class' => 'Inc\Base\RestController', // use custom WP_REST_Controller for custom post type ... CPT will not be within the wp/v2 namespace
            'public'            => true,
            'show_in_menu'      => 'seminardesk_plugin', // add post type to the seminardesk menu
            'menu_position'     => 1,
            'supports'          => $supports,
            'capability_type'     => 'page',
            'rewrite'           => $rewrite,
        ];

        register_post_type( 'sd_event', $cptOptions ); 

        // for debugging custom post type features... expensive operation. should usually only be called when activate and deactivate the plugin
        // flush_rewrite_rules();
    }
}