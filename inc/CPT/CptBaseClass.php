<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\CPT;

// TODO: implement and utilize shortcode [] to generate posts

abstract class CptBaseClass 
{
    public $attr = array(
        'name',
        'names',
        'menu_position',
    );
    public $name; 
    public $name_lower;
    public $names; 
    public $names_lower;
    public $menu_position;
    public $slug;

    /**
     * Define dynamic parameters of the unique custom post type
     *
     * @return void
     */
    abstract function set_parameters();

    public function __construct()
    {
        $this->set_parameters();
    }

    public function register()
    {
        $this->set_parameters();
        $this->name = ucfirst($this->name);
        $this->names = ucfirst($this->names);

        // register cpt in WordPress
        add_action( 'init', array( $this, 'create_cpt' ) );
    }
  
    /**
     * Create a custom post type using a template and register it
     *
     * @return void
     */
    public function create_cpt()
    {
        $this->name_lower = strtolower($this->name);
        $this->names_lower = strtolower($this->names);

        /**
         * array to configure labels for the CPT 
         */
        $labels = [
            'name'                  => _x( $this->names, 'post type general name', 'seminardesk' ),
            'singular_name'         => _x( $this->name, 'post type singular name', 'seminardesk'),
            'name_admin_bar'        => _x( $this->name, 'add new on admin bar', 'seminardesk' ),
            'add_new'               => _x( 'Add New', 'event', 'seminardesk' ),
            'add_new_item'          => __( 'Add New ' . $this->name, 'seminardesk' ),
            'new_item'              => __( 'New ' . $this->name, 'seminardesk' ),
            'edit_item'             => __( 'Edit ' . $this->name, 'seminardesk' ),
            'view_item'             => __( 'View ' . $this->name, 'seminardesk' ),
            'view_items'            => __( 'View ' . $this->names, 'seminardesk' ),
            // 'all_items'          => __( 'All ' . $this->name_plural, 'seminardesk' ),
            'all_items'             => __( $this->name . ' Editor', 'seminardesk' ),
            'search_items'          => __( 'Search ' . $this->names, 'seminardesk' ),
            'parent_item_colon'     => __( 'Parent ' . $this->names . ':', 'seminardesk' ),
            'not_found'             => __( 'No ' . $this->names_lower . ' found.', 'seminardesk' ),
            'not_found_in_trash'    => __( 'No ' . $this->names_lower . ' found in Trash.', 'seminardesk' ),
            'parent_item_colon'     => __( 'Parent ' . $this->name, 'seminardesk' ),
            'archives'              => __( $this->name . ' Archives', 'seminardesk' ),
            'attributes'            => __( $this->name . ' Attributes', 'seminardesk' ),
            'insert_into_item'      => __( 'Insert into ' . $this->name_lower, 'seminardesk' ),
            'uploaded_to_this_item' => __( 'Uploaded to this ' . $this->name_lower, 'seminardesk' ),
        ];

        /**
         * array to set rewrite rules for the CPT (sub CPT option)
         */
        $rewrite = [
            'slug' => $this->slug, // 'sd_' . $this->names_lower,
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
            'description'       => __( $this->name . ' post type for SeminarDesk.', 'seminardesk' ),
            'has_archive'       => true, // false,
            'show_in_rest'      => true,  //enable rest api
            'rest_base'         => 'sd_' . $this->names_lower,
            // 'rest_controller_class' => 'Inc\Base\RestController', // use custom WP_REST_Controller for custom post type ... CPT will not be within the wp/v2 namespace
            'public'            => true,
            'show_in_menu'      => 'seminardesk_plugin', // add post type to the seminardesk menu
            'menu_position'     => $this->menu_position,
            // 'hierarchical'      => true, // hierarchical must be true for parent option
            'supports'          => $supports,
            'capability_type'   => 'post',
            'rewrite'           => $rewrite,
            //'taxonomies' => array( 'category', 'post_tag' ),
        ];

        register_post_type( 'sd_' . $this->name_lower, $cptOptions ); 

        // for debugging custom post type features... expensive operation. should usually only be called when activate and deactivate the plugin
        //flush_rewrite_rules();
    }
}