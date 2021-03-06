<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Callbacks;

use WP_Error;
use WP_REST_Response;
use Inc\Utils\WebhookHandler;

/**
 * Callbacks for Rest API
 */
class RestCallbacks{

    /**
     * Check if a POST request has access to interact with endpoint
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function post_check_permissions($request)
    {
        return current_user_can( 'edit_posts' );
        // return true;
    
    }

    /**
     * Check if a GET request has access to interact with endpoint
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_check_permissions($request)
    {
        // return current_user_can( 'edit_posts' );
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
        $args = array(
            'numberposts'   => -1,
            'post_type'     => $post_type,
            'post_status'   => 'publish',
        );
        $posts = get_posts( $args );

        $get_sd_id = str_replace('sd_', '', $post_type) . '_id'; // variable variable names
        foreach ($posts as $current) {
            $current_id = $current->$get_sd_id;
            if ( $current_id == $sd_id){
                $post = $current;
                break;
            }
        }
 
        if ( empty( $post ) ) {
            return new WP_Error('no_posgt', 'Requested ID ' .$sd_id . ' does not exist', array('status' => 404));
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
        $args = array(
            'numberposts' => -1, // all events
            'post_type' => $post_type,
            'post_status' => 'any',
        );
        $posts = get_posts($args);

        if (empty($posts)) {
            return new WP_Error('no_event', 'No event available', array('status' => 404));
        }

        $response = array();

        foreach ( $posts as $current ) {
            // get event attributes and add to $response of the endpoint
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
            case 'sd_cpt_event':
                $event_attr = array(
                    'wp_event_id'       => $post->ID,
                    'sd_event_id'       => $post->sd_event_id,
                    'title'             => $post->post_title,
                    'slug'              => $post->post_name,
                    'link'              => get_post_permalink($post->ID),
                    'status'            => $post->post_status,
                    'author'            => get_the_author_meta( 'display_name', $post->post_author),
                    'sd_data'           => $post->sd_data,
                    'sd_webhook'        => $post->sd_webhook, // get metadata 'json_dump'
                );
                break;
            case 'sd_cpt_date':
                $event_attr = array(
                    'wp_date_id'        => $post->ID,
                    'sd_date_id'        => $post->sd_date_id,
                    'sd_date_begin'     => $post->sd_date_begin,
                    'sd_date_end'       => $post->sd_date_end,
                    'wp_event_id'       => $post->wp_event_id,
                    'sd_event_id'       => $post->sd_event_id,
                    'title'             => $post->post_title,
                    'slug'              => $post->post_name,
                    'link'              => get_post_permalink($post->ID),
                    'status'            => $post->post_status,
                    'sd_data'           => $post->sd_data,
                    'sd_webhook'           => $post->sd_webhook,
                );
                break;
            case 'sd_cpt_facilitator':
                $event_attr = array(
                    'wp_facilitator_id' => $post->ID,
                    'sd_facilitator_id' => $post->sd_facilitator_id,
                    'title'             => $post->post_title,
                    'slug'              => $post->post_name,
                    'link'              => get_post_permalink($post->ID),
                    'status'            => $post->post_status,
                    'sd_data'           => $post->sd_data,
                    'sd_webhook'        => $post->sd_webhook,
                );
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
        if ( !empty( $request_json['notifications'] ) ){
            $webhook = new WebhookHandler;
            $response = $webhook->batch_request($request_json);
        } else{
            $response = new WP_Error('not_supported', 'notifications of the request is empty', array('status' => 400));
        }
        return rest_ensure_response($response);
    }

    /**
    * Get list of all events
    *
    * @param WP_REST_Request $request
    * @return WP_REST_Response|WP_Error
    */
    public function get_cpt_events($request)
    {
        $response = $this->get_custom_posts('sd_cpt_event');
        return rest_ensure_response( $response );
    }

    /**
    * Get list of all event dates
    *
    * @param WP_REST_Request $request
    * @return WP_REST_Response|WP_Error
    */
    public function get_cpt_dates($request)
    {
        $response = $this->get_custom_posts('sd_cpt_date');
        return rest_ensure_response( $response );
    }

    /**
    * Get list of all facilitators
    *
    * @param WP_REST_Request $request
    * @return WP_REST_Response|WP_Error
    */
    public function get_cpt_facilitators($request)
    {
        $response = $this->get_custom_posts('sd_cpt_facilitator');
        return rest_ensure_response( $response );
    }

    /**
     * Get specific event via event id
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_cpt_event($request)
    {
        // invalid ID format returns WP_Error
        $requested_event_id = strtolower($request['event_id']);
        if ( strlen( $requested_event_id ) != 32 ){
            return new WP_Error('invalid_format', 'The requested event ID ' . $requested_event_id . 'does not consists of 32 characters', array('status' => 400));
        }

        $response = $this->get_custom_post('sd_cpt_event', $requested_event_id);

        return rest_ensure_response($response);
    }

    /**
     * Get specific event date via event date id
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_cpt_date($request)
    {
        $requested_date_id = strtolower($request['date_id']);

        $response = $this->get_custom_post('sd_cpt_date', $requested_date_id);

        return rest_ensure_response($response);
    }

    /**
     * Get specific facilitator via facilitator id
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_cpt_facilitator($request)
    {
        // invalid ID format returns WP_Error
        $requested_facilitator_id = strtolower($request['facilitator_id']);

        $response = $this->get_custom_post('sd_cpt_facilitator', $requested_facilitator_id);

        return rest_ensure_response($response);
    }
}