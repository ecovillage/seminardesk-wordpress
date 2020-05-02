<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Base;

use WP_Error;
use WP_REST_Response;

/**
 * Callbacks for webhook actions
 */
class WebhookHandler
{
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
        //     'post_status' => 'publish',
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

        // upload image and set us featured image for the new event#
        // TODO: create method or class for this kind
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

        return new WP_REST_Response( 'Event ' . $payload['id'] . ' created', 200);
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
            'post_status' => 'publish',
        ],);
        foreach ($events as $current) {
            if ( $current->event_id == $payload['id']){
                $post_id = $current->ID;
                break;
            }
        }

        if ( !isset($post_id) ){
            return new WP_Error('no_post', 'Event not updated. Event ID ' . $payload['id'] . ' does not exists', array('status' => 404));
        }

        // define metadata of the new sd_event
        $meta = [
            'event_id' =>  $payload['id'],
            'json_dump' => $request_json,
        ];

        // TODO: update of corresponding event dates if title, content, excerpt ..... is changed!?
          
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

        // TODO: Update thumbnail

        return new WP_REST_Response( 'Event ' . $payload['id'] . ' updated', 200);
    }

    /**
     * Delete event via event id
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public function event_delete($request_json)
    {
        // TODO: Delete also all dates associated with this event???
        $payload = (array)$request_json['payload'];

        $events = get_posts([
            'numberposts' => -1, // all events
            'post_type' => 'sd_event',
            'post_status' => 'publish',
        ],);
        foreach ($events as $current) {
            if ( $current->event_id == $payload['id']){
                $date_id = $current->event_id; 
                $post_id = $current->ID;
                break;
            }
        }
        if ( !isset($date_id) ){
            return Err::no_post($payload['id']);
        }
        $post_deleted = wp_trash_post($post_id);

        if ( !isset($post_deleted) ){
            return Err::no_post($payload['id']);
            // return new WP_Error('no_post', 'Nothing to delete. Event date ID ' . $payload['id'] . ' does not exists', array('status' => 404));
        }
        return new WP_REST_Response( 'Event ' . $payload['id'] . ' moved to trash', 200);
    }

    /**
     * Create event date via webhook from SeminarDesk
     *
     * @param Array $request_json
     * @return WP_Post|WP_Error
     */
    public function event_date_create($request_json)
    {   
        $payload = (array)$request_json['payload'];

        // check if with event date associated event exists
        $posts = get_posts([
            'numberposts' => -1,
            'post_type' => 'sd_event',
            'post_status' => 'publish',
        ],);
        foreach ($posts as $current) {
            if ( $current->event_id == $payload['eventId']){
                $event_post_id = $current->ID;
                break;
            }
        }
        if (!isset($event_post_id)){
            return new WP_Error('not_found' ,'associated event with the ID ' . $payload['eventId'] . ' does not exist', 404);
        }

         // define metadata of the new sd_event
         $meta = [
            'date_id'       => $payload['id'],
            'event_id'      => $payload['eventId'],
            'event_wp_id'   => $event_post_id,
            'status'        => $payload['status'],
            'begin_date'    => $payload['beginDate'],
            'end_date'      => $payload['endDate'],
            'facilitators'  => [null],
            'has_board'     => $payload['hasBoard'],
            'has_lodging'   => $payload['hasLodging'],
            'has_misc'      => $payload['hasMisc'],
            'price_info'    => $payload['priceInfo']['0']['value'],
            'venue'         => $payload['venue']['name'],
            'json_dump'     => $request_json,
        ];

        // define attributes of the new sd_event using $payload of the 
        $event_attr = [
            'post_type'     => 'sd_date',
            'post_title'    => $payload['title']['0']['value'],
            'post_content'  => $payload['description']['0']['value'],
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
        
        return new WP_REST_Response( 'Event Data ' . $payload['id'] . ' created', 200);
    }

    /**
     * Update event date via webhook from SeminarDesk
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public function event_date_update($request_json)
    {   
        $payload = (array)$request_json['payload'];

        // checks if event date exists
        $dates = get_posts([
            'numberposts' => -1,
            'post_type' => 'sd_date',
            'post_status' => 'publish',
        ],);
        foreach ($dates as $current) {
            if ( $current->date_id == $payload['id']){
                $post_id = $current->ID;
                break;
            }
        }

        if ( !isset($post_id) ){
            return new WP_Error('no_post', 'Event date not updated. Event date ID ' . $payload['id'] . ' does not exists', array('status' => 404));
        }

        // define metadata of the new sd_event
        $meta = [
            'date_id'       => $payload['id'],
            'event_id'      => $payload['eventId'],
            // 'event_wp_id' // not updated
            'status'        => $payload['status'],
            'begin_date'    => $payload['beginDate'],
            'end_date'      => $payload['endDate'],
            'facilitators'  => [null],
            'has_board'     => $payload['hasBoard'],
            'has_lodging'   => $payload['hasLodging'],
            'has_misc'      => $payload['hasMisc'],
            'price_info'    => $payload['priceInfo']['0']['value'],
            'venue'         => $payload['venue']['name'],
            'json_dump'     => $request_json,
        ];

        // define attributes of the new sd_event using $payload of the 
        $event_attr = [
            'ID'            => $post_id,
            'post_type'     => 'sd_date',
            'post_title'    => $payload['title']['0']['value'],
            'post_content'  => $payload['description']['0']['value'],
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

        // TODO: Update thumbnail

        // get updated event via post id and return it
        $event = get_post($post_id);

        return new WP_REST_Response( 'Event Data ' . $payload['id'] . ' updated', 200);
    }

    /**
     * Delete event date via webhook from SeminarDesk
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public function event_date_delete($request_json)
    {   
        $payload = (array)$request_json['payload'];

        $event_dates = get_posts([
            'numberposts' => -1, // all events
            'post_type' => 'sd_date',
            'post_status' => 'publish',
        ],);
        foreach ($event_dates as $current) {
            if ( $current->date_id == $payload['id']){
                $date_id = $current->date_id; 
                $post_id = $current->ID;
                break;
            }
        }
        if ( !isset($date_id) ){
            return Err::no_post($payload['id']);
        }
        $post_deleted = wp_trash_post($post_id);

        if ( !isset($post_deleted) ){
            return Err::no_post($payload['id']);
            // return new WP_Error('no_post', 'Nothing to delete. Event date ID ' . $payload['id'] . ' does not exists', array('status' => 404));
        }

        // TODO: security if WP_Post/Post_type correct which is returned by wp_trash_post
        // TODO: add status/action deleted http status code 200 (OK) not 201 (created)
        return new WP_REST_Response( 'Event Data ' . $payload['id'] . ' moved to trash', 200);
    }
    
    /**
     * Create facilitator event via webhook from SeminarDesk
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public function facilitator_create($request_json)
    {
        $payload = (array)$request_json['payload'];

        // define metadata of the new sd_facilitator
        $meta = [
            'facilitator_id'    =>  $payload['id'],
            'facilitator_name'  =>  $payload['name'],
            'json_dump' => $request_json,
        ];

        
        // define attributes of the new sd_event using $payload of the 
        $facilitator_attr = [
            'post_type'     => 'sd_facilitator',
            'post_title'    =>  $payload['name'],
            'post_content'  => $payload['about']['0']['value'],
            // 'post_excerpt'  => $payload['teaser']['0']['value'],
            'post_author'   => get_current_user_id(),
            'post_status'   => 'publish',
            'meta_input'    => $meta,
        ];

        $post_id = wp_insert_post(wp_slash($facilitator_attr), true);

        // return error if $post_id is of type WP_Error
        if (is_wp_error($post_id)){
            return $post_id;
        }

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

        return new WP_REST_Response( 'Facilitator ' . $payload['id'] . ' created', 200);
    }

    /**
     * Update facilitator event via webhook from SeminarDesk
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public function facilitator_update($request_json)
    {
        $payload = (array)$request_json['payload'];
        
        $facilitator = get_posts([
            'numberposts' => -1, // all events
            'post_type' => 'sd_facilitator',
            'post_status' => 'publish',
        ],);
        foreach ($facilitator as $current) {
            if ( $current->facilitator_id == $payload['id']){
                $post_id = $current->ID;
                break;
            }
        }

        if ( !isset($post_id) ){
            return new WP_Error('no_post', 'Facilitator not updated. Facilitator ID ' . $payload['id'] . ' does not exists', array('status' => 404));
        }

         // define metadata of the new sd_facilitator
         $meta = [
            'facilitator_id'    =>  $payload['id'],
            'facilitator_name'  =>  $payload['name'],
            'json_dump' => $request_json,
        ];

        
        // define attributes of the new sd_event using $payload of the 
        $facilitator_attr = [
            'ID'            => $post_id,
            'date_id'       => $payload['id'],
            'post_title'    =>  $payload['name'],
            'post_content'  => $payload['about']['0']['value'],
            // 'post_excerpt'  => $payload['teaser']['0']['value'],
            'post_author'   => get_current_user_id(),
            'post_status'   => 'publish',
            'meta_input'    => $meta,
        ];

         // Update event data in the database
         $post_id = wp_update_post( wp_slash($facilitator_attr), true );

         // return error if $post_id is of type WP_Error
         if (is_wp_error($post_id)){
             return $post_id;
         }

        // TODO: Update thumbnail

        return new WP_REST_Response( 'Facilitator ' . $payload['id'] . ' updated', 200);
    }

    /**
     * Delete facilitator event via webhook from SeminarDesk
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public function facilitator_delete($request_json)
    {
        $payload = (array)$request_json['payload'];

        $facilitator = get_posts([
            'numberposts' => -1, // all events
            'post_type' => 'sd_facilitator',
            'post_status' => 'publish',
        ],);
        foreach ($facilitator as $current) {
            if ( $current->facilitator_id == $payload['id']){
                $date_id = $current->facilitator_id; 
                $post_id = $current->ID;
                break;
            }
        }
        if ( !isset($date_id) ){
            return Err::no_post($payload['id']);
        }
        $post_deleted = wp_trash_post($post_id);

        if ( !isset($post_deleted) ){
            return Err::no_post($payload['id']);
            // return new WP_Error('no_post', 'Nothing to delete. Event date ID ' . $payload['id'] . ' does not exists', array('status' => 404));
        }

        return new WP_REST_Response( 'Facilitator ' . $payload['id'] . ' moved to trash', 200);

        // return new WP_Error('not_implemented', 'action ' . $request_json['action'] . ' not implemented yet', array('status' => 404));
    }
}