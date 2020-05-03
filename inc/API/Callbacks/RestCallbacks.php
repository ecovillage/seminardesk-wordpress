<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Api\Callbacks;

// TODO: review includes
use WP_Error;
use WP_REST_Response;
use Inc\Base\WebhookHandler;

/**
 * Callbacks for Rest API
 */
class RestCallbacks{
    
    function __construct()
    {
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
        $response = $this->get_event_attr($event);

        // return event response data.
        return rest_ensure_response($response);
    }

    /**
    * Get all events
    *
    * @param WP_REST_Request $request
    * @return WP_REST_Response|WP_Error
    */
    public function get_events($request)
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

        foreach ( $posts as $current ) {
            // get event attributes and add to $response of the endpoint
            // TODO: get_event review
            $event_attr = $this->get_event_attr( $current );
            array_push( $response, $event_attr );
        }
        return rest_ensure_response( $response );
    }

    public function get_event_attr($post)
    {
        $event_attr = [
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
        return $event_attr;
    }

    /**
     * Process HTTP POSTs from SeminarDesk
     *
     * @param WP_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function create_webhooks($request)
    {
        $request_json = (array)$request->get_json_params(); // complete JSON data of the request#
        switch ($request_json['action']) {
            case 'event.create':
                $response = WebhookHandler::create_event($request_json);
                break;
            case 'event.update':
                $response = WebhookHandler::update_event($request_json);
                break;
            case 'event.delete':
                $response = WebhookHandler::delete_event($request_json);
                break;
            case 'eventDate.create':
                $response = WebhookHandler::create_event_date($request_json);
                break;
            case 'eventDate.update':
                $response = WebhookHandler::update_event_date($request_json);
                break;
            case 'eventDate.delete':
                $response = WebhookHandler::delete_event_date($request_json);
                break;
            case 'facilitator.create':
                $response = WebhookHandler::create_facilitator($request_json);
                break;
            case 'facilitator.update':
                $response = WebhookHandler::update_facilitator($request_json);
                break;
            case 'facilitator.delete':
                $response = WebhookHandler::delete_facilitator($request_json);
                break;
            default:  
                $response = new WP_Error('not_supported', 'action ' . $request_json['action'] . ' not supported', array('status' => 400));
        }

        // return error if $response is of type WP_Error
        if ( is_wp_error($response) )
        {
            return $response;
        }

        return new WP_REST_Response($response);
    }

}