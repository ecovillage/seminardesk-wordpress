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
    protected $base_webhook, $base_event, $base_date, $base_facilitator;
    
    function __construct()
    {
        $this->namespace = 'seminardesk/v1';
        $this->base_webhook = 'webhooks';
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
        

        // Webhook route registration for HTTP POSTs from SeminarDesk
        register_rest_route($this->namespace, '/' . $this->base_webhook, array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array($this, 'webhook_action'),
                'permission_callback' => array($this, 'check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
        ),);

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
     * Process HTTP POSTs from SeminarDesk
     *
     * @param WP_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function webhook_action($request)
    {
        $request_json = (array)$request->get_json_params(); // complete JSON data of the request#
        switch ($request_json['action']) {
            case 'event.create':
                $response = $this->event_create($request_json);
                break;
            case 'event.update':
                $response = $this->event_update($request_json);
                break;
            case 'event.delete':
                $response = $this->event_delete($request_json);
                break;
            case 'eventDate.create':
                $response = $this->event_date_create($request_json);
                break;
            case 'eventDate.update':
                $response = $this->event_date_update($request_json);
                break;
            case 'eventDate.delete':
                $response = $this->event_date_delete($request_json);
                break;
            case 'facilitator.create':
                $response = $this->facilitator_create($request_json);
                break;
            case 'facilitator.update':
                $response = $this->facilitator_update($request_json);
                break;
            case 'facilitator.delete':
                $response = $this->facilitator_delete($request_json);
                break;
            default:  
                $response = new WP_Error('not_supported', 'action ' . $request_json['action'] . ' not supported', array('status' => 400));
        }

        // return error if $response is of type WP_Error
        if ( is_wp_error($response) )
        {
            return $response;
        }
        
        return new WP_REST_Response($response, 201);
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
    * Get all events
    *
    * @param WP_REST_Request $request
    * @return WP_REST_Response|WP_Error
    */
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
                $new_item = $this->event_create($request_json);
                $type = is_wp_error($new_item);
                if ( is_wp_error($new_item) )
                {
                    return $new_item;
                }
                break;
            case 'eventDate.create':
                $new_item = $this->event_date_create($request_json);
                if ( is_wp_error($new_item) )
                {
                    return $new_item;
                }
                break;
            case 'facilitator.create':
                $new_item = $this->facilitator_create($request_json);
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

    /**
     * Create event via webhook from SeminarDesk
     *
     * @param Array $request_json 
     * @return WP_REST_Response|WP_Error
     */
    public function event_create($request_json)
    {
        $payload = (array)$request_json['payload']; // payload of the request in JSON

        // checks if event_id already exists don't create new event and return error message
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
        $post_id = wp_insert_post(wp_slash($event_attr), true);

        // return error if $post_id is of type WP_Error
        if (is_wp_error($post_id)){
            return $post_id;
        }

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
        set_post_thumbnail($post_id, $img_id);

        $event = get_post($post_id);
        return $event;
    }

    /**
     * Update event via webhook from SeminarDesk
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public function event_update($request_json)
    {
        $payload = (array)$request_json['payload']; // payload of the request in JSON
        
        // checks if event_id exists and get post id in WordPress
        $events = get_posts([
            'numberposts' => -1, // all events
            'post_type' => 'sd_event',
            'post_status' => 'any',
        ],);
        foreach ($events as $current) {
            if ( $current->event_id == $payload['id']){
                $post_id = $current->ID;
                break;
            }
        }

        if ( !isset($post_id) ){
            return new WP_Error('no_event', 'Event not updated. Event ID ' . $payload['id'] . ' does not exists', array('status' => 404));
        }

        // define metadata of the new sd_event
        $meta = [
            'event_id' =>  $payload['id'],
            'json_dump' => $request_json,
        ];

        // define attributes of the new sd_event using $payload of the 
        $event_attr = [
            'ID'            => $post_id,
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

        // Update event data in the database
        $post_id = wp_update_post( wp_slash($event_attr), true );

        // return error if $post_id is of type WP_Error
        if (is_wp_error($post_id)){
            return $post_id;
        }

        // get updated event via post id and return it
        $event = get_post($post_id);
        return $event;
    }

    /**
     * Delete event via event id
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public function event_delete($request_json)
    {
        return new WP_Error('not_implemented', 'Update event method not implemented yet', array('status' => 400));
    }

    /**
     * Undocumented function
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public function event_date_create($request_json)
    {   
        return new WP_Error('not_implemented', 'action ' . $request_json['action'] . ' not implemented yet', array('status' => 404));
    }

    /**
     * Undocumented function
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public function event_date_update($request_json)
    {   
        return new WP_Error('not_implemented', 'action ' . $request_json['action'] . ' not implemented yet', array('status' => 404));
    }

    /**
     * Undocumented function
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public function event_date_delete($request_json)
    {   
        return new WP_Error('not_implemented', 'action ' . $request_json['action'] . ' not implemented yet', array('status' => 404));
    }
    
    /**
     * Undocumented function
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public function facilitator_create($request_json)
    {
        return new WP_Error('not_implemented', 'action ' . $request_json['action'] . ' not implemented yet', array('status' => 404));
    }

    /**
     * Undocumented function
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public function facilitator_update($request_json)
    {
        return new WP_Error('not_implemented', 'action ' . $request_json['action'] . ' not implemented yet', array('status' => 404));
    }

    /**
     * Undocumented function
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public function facilitator_delete($request_json)
    {
        return new WP_Error('not_implemented', 'action ' . $request_json['action'] . ' not implemented yet', array('status' => 404));
    }
}
