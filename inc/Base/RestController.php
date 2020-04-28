<?php

/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

// TODO: review includes
use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use WP_REST_Response;

// TODO: error handling, implementation of WP_Error
//         - return client error 400, if payload doesn't include required fields
// TODO: POST endpoint
//        - define required fields 
// TODO: permission check
// TODO: handle scheme and it's callbacks proper
// TODO: guaranty unique IDs ... evaluate ids before executing POST, update and delete request request_id, event_id, date_id, facilitator_id

/**
 * SeminarDesk's HTTP client aka Webhook
 * Custom Rest Controller class for sd_events,
 * following the WordPress pattern: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
 */
class RestController extends WP_REST_Controller
{
    /**
     * The namespace for these routes.
     *
     * @var string
     */
    protected $base_event, $base_date, $base_facilitator;
    
    function __construct()
    {
        $this->namespace = 'seminardesk/v1';
        $this->base_event = 'events';
        $this->base_date = 'dates';
        $this->base_facilitator = 'facilitators';
        $this->register();
    }

    public function register()
    {
        // add custom REST API for sd_events
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register custom namespace, its route and methods for sd_events
     * /wp-json/seminardesk/v1/events
     * @return void
     */
    public function register_routes()
    {

        // General route registration
        register_rest_route($this->namespace, '/' . $this->base_event, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_items'),
                'permission_callback' => array($this, 'check_permissions'),
                // 'args'                => array(),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array($this, 'create_item'),
                'permission_callback' => array($this, 'check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
            // array(
            //     'methods'             => 'PUT',
            //     'callback'            => array($this, 'update_event'),
            //     'permission_callback' => array($this, 'check_permissions'),
            //     // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            // ),
        ),);

        // Event route registration
        register_rest_route($this->namespace, '/' . $this->base_event . '/(?P<event_id>[a-z0-9]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_event'),
                'permission_callback' => array($this, 'check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
            
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array($this, 'create_event'),
                'permission_callback' => array($this, 'check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
        ),);

        // Date route registration
        register_rest_route($this->namespace, '/' . $this->base_date . '/(?P<date_id>[0-9]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_item'),
                'permission_callback' => array($this, 'check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array($this, 'update_item'),
                'permission_callback' => array($this, 'check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array($this, 'delete_item'),
                'permission_callback' => array($this, 'check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
        ),);
        // Register the individual object route for events
        register_rest_route($this->namespace, '/' . $this->base_facilitator . '/(?P<facilitator_id>[a-z0-9]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_facilitator'),
                'permission_callback' => array($this, 'check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array($this, 'update_facilitator'),
                'permission_callback' => array($this, 'check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array($this, 'delete_facilitator'),
                'permission_callback' => array($this, 'check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
        ),);
    }

    /**
     * Check if a given request has access to interact with endpoint
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function check_permissions($request)
    {
        // return current_user_can( 'edit_posts' );
        return true;
    }

    public function get_test($request)
    {
        return new WP_Error('not_implemented', 'endpoint not yet implemented', array('status' => 400));
    }

    /**
     * Get event via event id
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_event($request)
    {
        // invalid ID format returns WP_Error
        $requested_event_id = strtolower($request['event_id']);
        if ( strlen( $requested_event_id ) != 32 ){
            return new WP_Error('invalid_format', 'The requested event ID ' . $requested_event_id . 'does not consists of 32 characters', array('status' => 400));
        }

        // get event with requested event id 
        $events = get_posts([
            'numberposts' => -1, // all events
            'post_type' => 'sd_event',
            'post_status' => 'any',
        ],);
        foreach ($events as $current) {
            $current_event_id = $current->event_id;
            if ( $current_event_id == $requested_event_id){
                $event = $current;
                break;
            }
        }
 
        // no event with event id found, return WP_Error
        if ( empty( $event ) ) {
            return new WP_Error('no_event', 'Requested event ID ' .$requested_event_id . ' does not exist', array('status' => 404));
        }

        // create response data for the event
        $response = $this->get_event_response($event);

        // return event response data.
        return rest_ensure_response($response);
    }

    /**
     * Update event via event id
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function update_event($request)
    {


        $get_event = $this->get_event($request);

        $this->create_event($request);



        return new WP_Error('not_implemented', 'Update event method not implemented yet', array('status' => 400));
    }

    public function get_items($request)
    {
        $args = [
            'numberposts' => -1, // all events
            'post_type' => 'sd_event',
            'post_status' => 'any',
        ];

        $posts = get_posts($args);

        if (empty($posts)) {
            return new WP_Error('no_event', 'No event available', array('status' => 404));
        }

        $response = [];

        foreach ($posts as $current) {

            // get event fields and add to $data of the endpoint
            // TODO: get_event review
            $fields = $this->get_event_response($current);
            array_push($response, $fields);
        }
        return rest_ensure_response($response);
    }

    public function get_event_response($post)
    {
        $response = [
            'post_id'           => $post->ID,
            'event_id'          => $post->event_id,
            'title'             => $post->post_title,
            'content'           => $post->post_content,
            'excerpt'           => $post->post_excerpt,
            'name'              => $post->post_name,
            'slug'              => $post->post_name,
            'link'              => get_post_permalink($post->ID),
            'status'            => $post->post_status,
            'type'              => $post->post_type,
            'author'            => get_the_author_meta( 'display_name', $post->post_author),
            'parent'            => $post->post_parent,
            'featured_image'    => [
                'thumbnail' => get_the_post_thumbnail_url($post->ID, 'thumbnail'),
                'medium'    => get_the_post_thumbnail_url($post->ID, 'medium'),
                'large'     => get_the_post_thumbnail_url($post->ID, 'large'),
            ],
            'json_dump'         => $post->json_dump, // get metadata 'json_dump'
        ];
        // return response for a single event
        return $response;
    }

    /**
     * Create custom post (event, event_date, facilitator)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function create_item($request)
    {
        $request_json = $request->get_json_params(); // complete JSON data of the request#
        switch ($request_json['action']) {
            case 'event.create':
                // echo 'create new event';
                $new_item = $this->create_event($request_json);
                $type = is_wp_error($new_item);
                if ( is_wp_error($new_item) )
                {
                    return $new_item;
                }
                break;
            case 'eventDate.create':
                $new_item = $this->create_event_date($request_json);
                if ( is_wp_error($new_item) )
                {
                    return $new_item;
                }
                break;
            case 'facilitator.create':
                $new_item = $this->create_facilitator($request_json);
                if ( is_wp_error($new_item) )
                {
                    return $new_item;
                }
                break;
            default:  
                return new WP_Error('not_supported', 'action ' . $request_json['action'] . ' not supported', array('status' => 400));
        }
        return new WP_REST_Response($new_item, 201);
    }

    public function create_event($request_json)
    {
        $payload = $request_json['payload']; // payload of the request in JSON

        // if event_id already exists don't create new event and return error message
        // $posts = get_posts([
        //     'numberposts' => -1, // all events
        //     'post_type' => 'sd_event',
        //     'post_status' => 'any',
        // ],);
        // foreach ($posts as $current) {
        //     if ( $current->event_id == $payload['id']){
        //         return new WP_Error('not_created', 'Unique event ID ' . $payload['id'] . ' already exists. Refusing to create new event with existing ID', array('status' => 403));
        //     }
        // }

        // define metadata of the new sd_event
        $meta = [
            'event_id' =>  $payload['id'],
            'json_dump' => $request_json,
        ];

        // define attributes of the new sd_event using $payload of the 
        $event_attr = [
            'post_type'     => 'sd_event',
            'post_title'    =>  $payload['title']['0']['value'],
            //'post_title'    =>  wp_strip_all_tags($payload['title']['0']['value']), // remove HTML, JavaScript, or PHP tags from the title of the post
            'post_content'  => $payload['description']['0']['value'],
            // 'post_content'  => wp_strip_all_tags($payload['description']['0']['value']),
            'post_excerpt'  => $payload['teaser']['0']['value'],
            'post_author'   => get_current_user_id(),
            'post_status'   => 'publish',
            'meta_input'    => $meta,
        ];

        //$event_attr = wp_slash($event_attr);

        // create new event in the WordPress database
        $event_id = wp_insert_post($event_attr);

        // update metadata of CPT sd_event
        // $event_update = [
        //   'ID' => $event_id,
        //   'meta_input' => [
        //     'xp' => 456,
        //   ],
        // ];
        // wp_update_post($event_update);

        // upload image and set us featured image for the new event#
        // TODO: create an own class for this
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $img_url = $payload['teaserPictureUrl']['0']['value'];
        $img_id = media_handle_sideload(
            [
                'name' => basename($img_url),
                'tmp_name' => download_url( $payload['teaserPictureUrl']['0']['value'] ),
            ]
        );
        set_post_thumbnail($event_id, $img_id);

        $event = get_post($event_id);
        return $event;
    }

    public function create_event_date($data)
    {   
        return new WP_Error('not_implemented', 'action ' . $data['action'] . ' not implemented yet', array('status' => 404));
    }
    
    public function create_facilitator($data)
    {
        return new WP_Error('not_implemented', 'action ' . $data['action'] . ' not implemented yet', array('status' => 404));
    }
}
