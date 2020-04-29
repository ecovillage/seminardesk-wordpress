<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Api\Callbacks;

use WP_Error;

/**
 * Callbacks for webhook actions
 */
class WebhookCallbacks
{
    /**
     * Create event via webhook from SeminarDesk
     *
     * @param Array $request_json 
     * @return WP_Post|WP_Error
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
     * @return WP_Post|WP_Error
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
        // TODO: Instead of delete just deactivate flag to not show in event overview... 
        // TODO: Delete also all dates associated with this event
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