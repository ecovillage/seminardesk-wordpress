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

    /**
     * Check if a given request has access to interact with endpoint
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function check_permissions($request)
    {
        //  return current_user_can( 'edit_posts' );
        return true;
    }

    /**
     * Get specific post of a custom post type via sd_id
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_custom_post($post_type, $sd_id)
    { 
        $posts = get_posts([
            'numberposts' => -1,
            'post_type' => $post_type,
            'post_status' => 'publish',
        ],);
        $get_sd_id = str_replace('sd_', '', $post_type) . '_id'; // variable variable names
        foreach ($posts as $current) {
            $current_id = $current->$get_sd_id;
            if ( $current_id == $sd_id){
                $post = $current;
                break;
            }
        }
 
        if ( empty( $post ) ) {
            return new WP_Error('no_post', 'Requested ID ' .$sd_id . ' does not exist', array('status' => 404));
        }

        $response = $this->get_custom_post_attr($post);
        return rest_ensure_response($response);
    }
    
    /**
    * Get all posts of a custom post type
    *
    * @param WP_REST_Request $request
    * @return WP_REST_Response|WP_Error
    */
    public function get_custom_posts($post_type)
    {
        $args = [
            'numberposts' => -1, // all events
            'post_type' => $post_type,
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
            $post_attr = $this->get_custom_post_attr( $current );
            array_push( $response, $post_attr );
        }
        return rest_ensure_response( $response );
    }

    /**
     * get post attributes of corresponding custom post type
     * 
     * @param WP_Post $post
     * @return Array|Null post attributes
     */
    public function get_custom_post_attr($post)
    {
        switch ($post->post_type) {
            case 'sd_event':
                $event_attr = [
                    'post_id'           => $post->ID,
                    'event_id'          => $post->event_id,
                    'title'             => $post->post_title,
                    'content'           => $post->post_content,
                    'excerpt'           => $post->post_excerpt,
                    'slug'              => $post->post_name,
                    'link'              => get_post_permalink($post->ID),
                    'status'            => $post->post_status,
                    'author'            => get_the_author_meta( 'display_name', $post->post_author),
                    'featured_image'    => [
                        'thumbnail' => get_the_post_thumbnail_url($post->ID, 'thumbnail'),
                        'medium'    => get_the_post_thumbnail_url($post->ID, 'medium'),
                        'large'     => get_the_post_thumbnail_url($post->ID, 'large'),
                    ],
                    'json_dump'         => $post->json_dump, // get metadata 'json_dump'
                ];
                break;
            case 'sd_date':
                $event_attr = [
                    'post_id'           => $post->ID,
                    'date_id'           => $post->date_id,
                    'event_id'          => $post->event_id,
                    'title'             => $post->post_title,
                    'content'           => $post->post_content,
                    'excerpt'           => $post->post_excerpt,
                    'slug'              => $post->post_name,
                    'link'              => get_post_permalink($post->ID),
                    'status'            => $post->post_status,
                    'featured_image'    => [
                        'thumbnail' => get_the_post_thumbnail_url($post->ID, 'thumbnail'),
                        'medium'    => get_the_post_thumbnail_url($post->ID, 'medium'),
                        'large'     => get_the_post_thumbnail_url($post->ID, 'large'),
                    ],
                    'begin_date'        => $post->begin_date,
                    'end_date'          => $post->end_date,
                    'facilitators'      => $post->facilitators,
                    'has_board'         => $post->has_board,
                    'has_lodging'       => $post->has_lodging,
                    'has_misc'          => $post->has_misc,
                    'price_info'        => $post->price_info,
                    'venue'             => $post->venue, 
                    'json_dump'         => $post->json_dump,
                ];
                break;
            case 'sd_facilitator':
                $event_attr = [
                    'post_id'           => $post->ID,
                    'facilitator_id'    => $post->facilitator_id,
                    'title'             => $post->post_title,
                    'content'           => $post->post_content,
                    'slug'              => $post->post_name,
                    'link'              => get_post_permalink($post->ID),
                    'status'            => $post->post_status,
                    'featured_image'    => [
                        'thumbnail' => get_the_post_thumbnail_url($post->ID, 'thumbnail'),
                        'medium'    => get_the_post_thumbnail_url($post->ID, 'medium'),
                        'large'     => get_the_post_thumbnail_url($post->ID, 'large'),
                    ],
                    'facilitator_name'  => $post->facilitator_name,
                    'json_dump'         => $post->json_dump,
                ];
                break;
            default:
                $event_attr = null;
        }

        // get response for a single post
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

    /**
    * Get list of all events
    *
    * @param WP_REST_Request $request
    * @return WP_REST_Response|WP_Error
    */
    public function get_events($request)
    {
        $response = $this->get_custom_posts('sd_event');
        return rest_ensure_response( $response );
    }

    /**
    * Get list of all event dates
    *
    * @param WP_REST_Request $request
    * @return WP_REST_Response|WP_Error
    */
    public function get_dates($request)
    {
        $response = $this->get_custom_posts('sd_date');
        return rest_ensure_response( $response );
    }

    /**
    * Get list of all facilitators
    *
    * @param WP_REST_Request $request
    * @return WP_REST_Response|WP_Error
    */
    public function get_facilitators($request)
    {
        $response = $this->get_custom_posts('sd_facilitator');
        return rest_ensure_response( $response );
    }

    /**
     * Get specific event via event id
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

        $response = $this->get_custom_post('sd_event', $requested_event_id);

        return rest_ensure_response($response);
    }

    /**
     * Get specific event date via event date id
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_date($request)
    {
        $requested_date_id = strtolower($request['date_id']);

        $response = $this->get_custom_post('sd_date', $requested_date_id);

        return rest_ensure_response($response);
    }

    /**
     * Get specific facilitator via facilitator id
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_facilitator($request)
    {
        // invalid ID format returns WP_Error
        $requested_facilitator_id = strtolower($request['facilitator_id']);

        $response = $this->get_custom_post('sd_facilitator', $requested_facilitator_id);

        return rest_ensure_response($response);
    }
}