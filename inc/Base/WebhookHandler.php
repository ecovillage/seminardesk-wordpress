<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Base;

use WP_Error;
use WP_REST_Response;
use WP_Query;

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
    public static function create_event($request_json)
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
            'event_id'  => $payload['id'],
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

        if (isset($payload['teaserPictureUrl']))
        {
            self::set_thumbnail($post_id, $payload['teaserPictureUrl']['0']['value']);
        }
        
        return new WP_REST_Response( 'Event ' . $payload['id'] . ' created', 201);
    }

    /**
     * Update event via webhook from SeminarDesk
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function update_event($request_json)
    {
        $payload = (array)$request_json['payload']; // payload of the request in JSON
        
        // TODO: possible to get corresponding post_id from event_id directly with out parsing all posts
        // checks if event_id exists and get post_id
        $query = self::get_query_by_meta( 'sd_event', 'event_id', $payload['id']);
        $post_id = $query->post->ID;
        if ($query->post_count > 1) {
            $unique = false;
        }
        else{
            $unique = true;
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

        self::set_thumbnail($post_id, $payload['teaserPictureUrl']['0']['value']);

        return new WP_REST_Response( [
            'message'       => 'Event updated',
            'requestId'     => $request_json['requestId'],
            'action'        => $request_json['action'],
            'postId'        => $post_id,
            'eventId'       => $payload['id'],
            'eventUnique'   => $unique,
        ], 200);
    }

    /**
     * Delete event via event id
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function delete_event($request_json)
    {
        // TODO: Delete also all dates associated with this event???
        $payload = (array)$request_json['payload'];
        $response = self::trash_post_by_meta('sd_event', 'event_id', $payload['id']);
        return $response;
    }

    /**
     * Create event date via webhook from SeminarDesk
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function create_event_date($request_json)
    {   
        $payload = (array)$request_json['payload'];

        // check if with event date associated event exists
        $query = self::get_query_by_meta( 'sd_event', 'event_id', $payload['eventId']);
        $event_post_id = $query->post->ID;

        if (!isset($event_post_id)){
            return new WP_Error('not_found' ,'associated event with the ID ' . $payload['eventId'] . ' does not exist', [ 
                'status' => 404,
                'requestId' => $request_json['requestId'],
                'action'    => $request_json['action'],
                'eventDateId' => $payload['id'],
                'eventId' => $payload['eventId'],
                ]);
        }

         // define metadata of the new sd_event_date
         $meta = [
            'date_id'       => $payload['id'],
            'event_id'      => $payload['eventId'],
            'event_wp_id'   => $event_post_id,
            'status'        => $payload['status'],
            'begin_date'    => (int)$payload['beginDate'],
            'end_date'      => (int)$payload['endDate'],
            'facilitators'  => [null],
            'has_board'     => $payload['hasBoard'],
            'has_lodging'   => $payload['hasLodging'],
            'has_misc'      => $payload['hasMisc'],
            'price_info'    => $payload['priceInfo']['0']['value'],
            'venue'         => $payload['venue']['name'],
            'json_dump'     => $request_json,
        ];

        // define attributes of the new sd_event_date using $payload of the 
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

        self::set_thumbnail($post_id, $payload['teaserPictureUrl']['0']['value']);
        
        return new WP_REST_Response( [
            'message'       => 'Event Date created',
            'requestId'     => $request_json['requestId'],
            'action'        => $request_json['action'],
            'postId'        => $post_id,
            'eventDateId'   => $payload['id'],
            'eventPostId'   => $event_post_id,
            'eventId'       => $payload['eventId'],
        ], 200);
    }

    /**
     * Update event date via webhook from SeminarDesk
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function update_event_date($request_json)
    {   
        $payload = (array)$request_json['payload'];

        // checks if event date exists
        $query = self::get_query_by_meta( 'sd_date', 'date_id', $payload['id']);
        $post_id = $query->post->ID;

        if ( !isset($post_id) ){
            return new WP_Error('no_post', 'Event date not updated. Event date ID ' . $payload['id'] . ' does not exists', array('status' => 404));
        }

        // define metadata of the new sd_event
        $meta = [
            'date_id'       => $payload['id'],
            'event_id'      => $payload['eventId'],
            // 'event_wp_id' // not updated
            'status'        => $payload['status'],
            'begin_date'    => (int)$payload['beginDate'],
            'end_date'      => (int)$payload['endDate'],
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

        self::set_thumbnail($post_id, $payload['teaserPictureUrl']['0']['value']);

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
    public static function delete_event_date($request_json)
    {   
        // TODO: security if WP_Post/Post_type correct which is returned by wp_trash_post
        // TODO: add status/action deleted http status code 200 (OK) not 201 (created)
        $payload = (array)$request_json['payload'];
        $response = self::trash_post_by_meta('sd_date', 'date_id', $payload['id']);
        return $response;
    }
    
    /**
     * Create facilitator event via webhook from SeminarDesk
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function create_facilitator($request_json)
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

        self::set_thumbnail($post_id, $payload['pictureUrl']);

        return new WP_REST_Response( 'Facilitator ' . $payload['id'] . ' created', 200);
    }

    /**
     * Update facilitator event via webhook from SeminarDesk
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function update_facilitator($request_json)
    {
        $payload = (array)$request_json['payload'];
        $query = self::get_query_by_meta( 'sd_facilitator', 'facilitator_id', $payload['id'] );
        $post_id = $query->post->ID;

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

        self::set_thumbnail($post_id, $payload['pictureUrl']);

        return new WP_REST_Response( 'Facilitator ' . $payload['id'] . ' updated', 200);
    }

    /**
     * Delete facilitator event via webhook from SeminarDesk
     *
     * @param Array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function delete_facilitator($request_json)
    {
        $payload = (array)$request_json['payload'];
        $response = self::trash_post_by_meta('sd_facilitator', 'facilitator_id', $payload['id']);
        return $response;
    }

    /**
     * Retrieves query data by given meta key and its requested value.
     *
     * @param string $post_type
     * @param string $meta_key
     * @param string $meta_value
     * @return WP_Query
     */
    public static function get_query_by_meta( $post_type, $meta_key, $meta_value )
    {
        $query = new WP_Query(
            array(
                'post_type'     => $post_type,
                'post_status'   => 'publish',
                'meta_query'    => array(
                    array(
                        'key'       => $meta_key,
                        'value'     => $meta_value,
                        'compare'   => '=',
                        'type'      => 'CHAR',
                    ),
                ),
            ),
        );

        return $query;
    }

    /**
     * move custom post to trash
     * 
     * @param Int   $post_id
     * @param Array $payload
     */
    public static function trash_post_by_meta($post_type, $meta_key, $meta_value)
    {
        //$get_sd_id = str_replace('sd_', '', $post_type) . '_id'; // variable variable names
        $query = self::get_query_by_meta( $post_type, $meta_key, $meta_value);
        $sd_id = $query->post->$meta_value;
        $post_id = $query->post->ID;
        if ( !isset($sd_id) ){
            return Err::no_post($meta_value);
        }
        $post_deleted = wp_trash_post($post_id);

        if ( !isset($post_deleted) ){
            return new WP_Error('no_post', 'Nothing to delete. Event date ID ' . $meta_value . ' does not exists', array('status' => 404));
        }
        return new WP_REST_Response( $post_type . ' ' . $meta_value . ' moved to trash', 204);
    }

    /**
     * upload image and set us featured image for custom post
     *
     * @param Int   $post_id
     * @param Array $payload
     */
    // TODO: image already exists in media lib?
    public static function set_thumbnail($post_id, $img_url){
        // validate image url https://www.w3schools.com/php/php_form_url_email.asp
        if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$img_url))
        {
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $img_id = media_handle_sideload(
                [
                    'name' => basename($img_url),
                    'tmp_name' => download_url($img_url),
                ]
            );
            set_post_thumbnail($post_id, $img_id);
        }
    }
}