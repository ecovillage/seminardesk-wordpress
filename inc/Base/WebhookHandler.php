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
     * @param array $request_json 
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
        $meta_input = self::set_event_meta( $request_json );

        // define attributes of the new sd_event using $payload of the 
        $event_attr = [
            'post_type'     => 'sd_event',
            'post_title'    => self::strip_get_value_by_language( $payload['title'] ),
            //'post_title'    =>  wp_strip_all_tags($payload['title']['0']['value']), // remove HTML, JavaScript, or PHP tags from the title of the post
            // 'post_content'  => $payload['description']['0']['value'],
            // 'post_content'  => wp_strip_all_tags($payload['description']['0']['value']),
            // 'post_excerpt'  => $payload['teaser']['0']['value'],
            'post_author'   => get_current_user_id(),
            'post_status'   => 'publish',
            'meta_input'    => $meta_input,
        ];

        // create new event in the WordPress database
        $post_id = wp_insert_post(wp_slash($event_attr), true);

        // return error if $post_id is of type WP_Error
        if (is_wp_error($post_id)){
            return $post_id;
        }

        if (isset($payload['teaserPictureUrl']))
        {
            self::set_thumbnail($post_id, self::kses_get_value_by_language( $payload['teaserPictureUrl'] ));
        }
        
        return new WP_REST_Response( [
            'message'       => 'Event created',
            'requestId'     => $request_json['requestId'],
            'action'        => $request_json['action'],
            'postId'        => $post_id,
            'eventId'       => $payload['id'],
        ], 201);
    }

    /**
     * Update event via webhook from SeminarDesk
     *
     * @param array $request_json
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
            return new WP_Error('not_found', 'Event not updated. Event ID ' . $payload['id'] . ' does not exist', array(
                'status'        => 404,
                'requestId'     => $request_json['requestId'],
                'action'        => $request_json['action'],
                'eventId'   => $payload['id'],
            ));
        }

        // define metadata of the new sd_event
        $meta_input = self::set_event_meta( $request_json );

        // TODO: update of corresponding event dates if title, content, excerpt ..... is changed!?
          
        // define attributes of the new sd_event using $payload of the 
        $event_attr = [
            'ID'            => $post_id,
            'post_type'     => 'sd_event',
            'post_title'    =>  self::strip_get_value_by_language( $payload['title'] ),
            //'post_title'    =>  wp_strip_all_tags($payload['title']['0']['value']), // remove HTML, JavaScript, or PHP tags from the title of the post
            // 'post_content'  => $payload['description']['0']['value'],
            // 'post_content'  => wp_strip_all_tags($payload['description']['0']['value']),
            // 'post_excerpt'  => $payload['teaser']['0']['value'],
            'post_author'   => get_current_user_id(),
            'post_status'   => 'publish',
            'meta_input'    => $meta_input,
        ];

        // Update event data in the database
        $post_id = wp_update_post( wp_slash($event_attr), true );

        // return error if $post_id is of type WP_Error
        if (is_wp_error($post_id)){
            return $post_id;
        }

        self::set_thumbnail( $post_id, self::kses_get_value_by_language( $payload['teaserPictureUrl'] ) );

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
     * @param array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function delete_event($request_json) 
    {
        // TODO: Delete also all dates associated with this event???
        $payload = (array)$request_json['payload'];
        $post_deleted = self::trash_post_by_meta('sd_event', 'event_id', $payload['id']);
        if ( !isset($post_deleted) ){
            return new WP_Error('not_found', 'Nothing to delete. Event date ID ' . $payload['id'] . ' does not exists', array(
                'status'        => 404,
                'requestId'     => $request_json['requestId'],
                'action'        => $request_json['action'],
                'eventDateId'   => $payload['id'],
            ));
        }
        return new WP_REST_Response( [
            'message'       => 'Facilitator deleted',
            'requestId'     => $request_json['requestId'],
            'action'        => $request_json['action'],
            'postId'        => $post_deleted->ID,
            'facilitatorId' => $payload['id'],
        ], 200);
    }

    /**
     * Create event date via webhook from SeminarDesk
     *
     * @param array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function create_event_date($request_json)
    {   
        $payload = (array)$request_json['payload'];

        // check if with event date associated event exists and get its WordPress ID
        $query = self::get_query_by_meta( 'sd_event', 'event_id', $payload['eventId']);
        $event_post_id = $query->post->ID;
        if (!isset($event_post_id)){
            return new WP_Error('not_found' ,'Event date not created. Associated event with the ID ' . $payload['eventId'] . ' does not exist', array( 
                'status' => 404,
                'requestId' => $request_json['requestId'],
                'action'    => $request_json['action'],
                'eventDateId' => $payload['id'],
                'eventId' => $payload['eventId'],
            ));
        }

        // define attributes of the new sd_event_date using request data of the webhook
        $txn_input = self::set_event_date_taxonomy($payload);
        $meta_input = self::set_event_date_meta( $request_json, $event_post_id );
        $event_attr = [
            'post_type'     => 'sd_date',
            'post_title'    => $payload['title']['0']['value'],
            //'post_content'  => $payload['description']['0']['value'],
            'post_excerpt'  => $payload['teaser']['0']['value'],
            'post_author'   => get_current_user_id(),
            'post_status'   => 'publish',
            'meta_input'    => $meta_input,
            'tax_input'     => $txn_input,
        ];

        // create new event date in the WordPress database
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
     * @param array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function update_event_date($request_json)
    {   
        $payload = (array)$request_json['payload'];

        // checks if event date exists
        $query = self::get_query_by_meta( 'sd_date', 'date_id', $payload['id']);
        $post_id = $query->post->ID;
        if ( !isset($post_id) ){
            return new WP_Error('not_found', 'Event date not updated. Event date ID ' . $payload['id'] . ' does not exists', array(
                'status'        => 404,
                'requestId'     => $request_json['requestId'],
                'action'        => $request_json['action'],
                'eventDateId'   => $payload['id'],
                'eventId'       => $payload['eventId'],
            ));
        }

        // get corresponding WordPress ID for the corresponding event post
        $event_post_id = $query->post->event_wp_id;

        // define attributes of the new sd_event_date using request data of the webhook 
        $txn_input = self::set_event_date_taxonomy($payload);
        $meta_input = self::set_event_date_meta( $request_json, $event_post_id );
        $event_attr = [
            'ID'            => $post_id,
            'post_type'     => 'sd_date',
            'post_title'    => $payload['title']['0']['value'],
            'post_content'  => $payload['description']['0']['value'],
            'post_excerpt'  => $payload['teaser']['0']['value'],
            'post_author'   => get_current_user_id(),
            'post_status'   => 'publish',
            'meta_input'    => $meta_input,
            'tax_input'     => $txn_input,
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

        return new WP_REST_Response( [
            'message'       => 'Event Date updated',
            'requestId'     => $request_json['requestId'],
            'action'        => $request_json['action'],
            'postId'        => $post_id,
            'eventDateId'   => $payload['id'],
            'eventPostId'   => $event_post_id,
            'eventId'       => $payload['eventId'],
        ], 200);
    }

    /**
     * Delete event date via webhook from SeminarDesk
     *
     * @param array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function delete_event_date($request_json)
    {   
        // TODO: security if WP_Post/Post_type correct which is returned by wp_trash_post
        // TODO: add status/action deleted http status code 200 (OK) not 201 (created)
        $payload = (array)$request_json['payload'];
        $post_deleted = self::trash_post_by_meta('sd_date', 'date_id', $payload['id']);
        
        if ( !isset($post_deleted) ){
            return new WP_Error('not_found', 'Nothing to delete. Event date ID ' . $payload['id'] . ' does not exists', array(
                'status'        => 404,
                'requestId'     => $request_json['requestId'],
                'action'        => $request_json['action'],
                'eventDateId'   => $payload['id'],
            ));
        }
        return new WP_REST_Response( [
            'message'       => 'Facilitator deleted',
            'requestId'     => $request_json['requestId'],
            'action'        => $request_json['action'],
            'postId'        => $post_deleted->ID,
            'facilitatorId' => $payload['id'],
        ], 200);
    }
    
    /**
     * Create facilitator event via webhook from SeminarDesk
     *
     * @param array $request_json
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

        return new WP_REST_Response( [
            'message'       => 'Facilitator created',
            'requestId'     => $request_json['requestId'],
            'action'        => $request_json['action'],
            'postId'        => $post_id,
            'facilitatorId' => $payload['id'],
        ], 200);
    }

    /**
     * Update facilitator event via webhook from SeminarDesk
     *
     * @param array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function update_facilitator($request_json)
    {
        $payload = (array)$request_json['payload'];
        $query = self::get_query_by_meta( 'sd_facilitator', 'facilitator_id', $payload['id'] );
        $post_id = $query->post->ID;

        if ( !isset($post_id) ){
            return new WP_Error('not_found', 'Nothing to update. Facilitator ID ' . $payload['id'] . ' does not exists', array(
                'status'        => 404,
                'requestId'     => $request_json['requestId'],
                'action'        => $request_json['action'],
                'eventDateId'   => $payload['id'],
            ));
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

        return new WP_REST_Response( [
            'message'       => 'Facilitator updated',
            'requestId'     => $request_json['requestId'],
            'action'        => $request_json['action'],
            'postId'        => $post_id,
            'facilitatorId' => $payload['id'],
        ], 200);
    }

    /**
     * Delete facilitator event via webhook from SeminarDesk
     *
     * @param array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function delete_facilitator($request_json)
    {
        $payload = (array)$request_json['payload'];
        $post_deleted = self::trash_post_by_meta('sd_facilitator', 'facilitator_id', $payload['id']);

        if ( !isset($post_deleted) ){
            return new WP_Error('not_found', 'Nothing to delete. Facilitator ID ' . $payload['id'] . ' does not exists', array(
                'status'        => 404,
                'requestId'     => $request_json['requestId'],
                'action'        => $request_json['action'],
                'eventDateId'   => $payload['id'],
            ));
        }
        return new WP_REST_Response( [
            'message'       => 'Facilitator deleted',
            'requestId'     => $request_json['requestId'],
            'action'        => $request_json['action'],
            'postId'        => $post_deleted->ID,
            'facilitatorId' => $payload['id'],
        ], 200);
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
     * @param int   $post_type
     * @param string $meta_key
     * @param string $meta_value
     * @return WP_post|false
     */
    public static function trash_post_by_meta($post_type, $meta_key, $meta_value)
    {
        //$get_sd_id = str_replace('sd_', '', $post_type) . '_id'; // variable variable names
        $query = self::get_query_by_meta( $post_type, $meta_key, $meta_value);
        $post_id = $query->post->ID;
        $sd_id = $query->post->$meta_value;
        if ( !isset($sd_id) ){
            return null;
        }
        $post_deleted = wp_trash_post($post_id);

        return $post_deleted;
    }

    /**
     * upload image and set us featured image for custom post
     *
     * @param int   $post_id
     * @param array $payload
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

    /**
     * define taxonomy 'dates' for the event date and create terms if not exists
     *
     * @param array $payload payload send form seminardesk via webhook
     * @return array custom taxonomy for the event date
     */
    public static function set_event_date_taxonomy($payload)
    {
        $txn = 'dates';
        $year = wp_date('Y', $payload['beginDate']/1000);
        $month = wp_date('m', $payload['beginDate']/1000);
        // get term ID and create if not existing including children (months of the year)
        $term_year = term_exists($year, $txn); 

        if (!isset($term_year)){
            $term_year = wp_insert_term($year, $txn, array(
                'description' => __('Seminare in ' . $year, 'seminardesk'),
                'slug' => $year,
            ));
            for ($m = 1; $m <= 12; $m++){
                $m_padded = sprintf('%02s', $m);
                wp_insert_term($m_padded . '/' . $year, $txn, array(
                    // 'alias_of'      => $year,
                    'description'   => __('Event Dates of the month ' . $m_padded . '/' . $year, 'seminardesk'),
                    'parent'        => $term_year['term_taxonomy_id'],
                    'slug'          => $m_padded,
                ));
            }
        }
        // define taxonomies of the new sd_event_date
        // user to create new sd_event_date needs capability to work with a taxonomy
        $term_month = term_exists($month, $txn);
        $terms = array($term_year['term_taxonomy_id'], $term_month['term_taxonomy_id']);
        $txn_input = array(
            $txn => $terms,
        );

        return $txn_input;
    }

    /**
     * Define metadata for the event date
     *
     * @param array $request_json request sent from seminardesk via webhook
     * @param int $event_post_id WordPress ID of corresponding event
     * @return array metadata for the event date
     */
    public static function set_event_date_meta( $request_json, $event_post_id )
    {
        $payload = (array)$request_json['payload'];
        // define metadata of the new sd_event_date
        $meta_input = [
            'date_id'               => $payload['id'],
            'event_id'              => $payload['eventId'],
            'event_wp_id'           => $event_post_id,
            'status'                => $payload['status'],
            'begin_date'            => (int)$payload['beginDate'],
            'end_date'              => (int)$payload['endDate'],
            'facilitator_ids'       => $payload['facilitators'],
            'has_lodging'           => $payload['hasLodging'],
            'has_misc'              => $payload['hasMisc'],
            'has_board'             => $payload['hasBoard'],
            'teaser_picture_url'    => $payload['teaserPictureUrl']['0']['value'],
            'price_info'            => $payload['priceInfo']['0']['value'],
            'venue'                 => $payload['venue']['name'],
            'json_dump'             => $request_json,
        ];
        return $meta_input;
    }
    /**
     * Define metadata for the event
     *
     * @param array $request_json request sent from seminardesk via webhook
     * @return array metadata for the event
     */
    public static function set_event_meta( $request_json )
    {
        $payload = (array)$request_json['payload'];
        // define metadata of the new sd_event_date
        $meta_input = [
            'event_id'  => $payload['id'],
            'data'      => self::kses_array_values($payload),
            // 'data'      => self::strip_array_values($payload),
            'json_dump' => $request_json,
        ];
        return $meta_input;
    }

    /**
     * Get and kses a value of a l10n field received in payload generated by seminardesk
     *
     * @param array $field l10n formatted field of seminardesk
     * @param string $lang_tag select language by tag (e.g DE, EN, ES...)
     * @param string $allowed_html allowed html code to prevent XSS attack (wp_kses)
     * @param array $allowed_protocols allowed protocols used in html code (e.g https, http, ftp ...) 
     * @return string localized and kses value of the field
     */
    public static function kses_get_value_by_language( $field, $lang_tag = 'DE', $allowed_html = 'post', $allowed_protocols = [ 'http', 'https', 'ftp' ] )
    {
        $key = array_search(strtoupper($lang_tag), array_column($field, 'language'));
        if ($key === false){
            return null;
        }
        $value = wp_kses( $field[$key]['value'], $allowed_html, $allowed_protocols );
        return $value;
    }

    /**
     * Get and strip a value of a l10n field received in payload generated by seminardesk
     *
     * @param array $field l10n formatted field by seminardesk
     * @param string $lang_tag Optional. select language by tag (e.g DE, EN, ES...)
     * @param bool $remove_breaks Optional. Whether to remove left over line breaks and white space chars
     * @return string localized and striped (kses) value of the field
     */
    public static function strip_get_value_by_language( $field, $lang_tag = 'DE', $remove_breaks = false )
    {
        $key = array_search(strtoupper($lang_tag), array_column($field, 'language'));
        if ( $key === false){
            return null;
        }
        $value = wp_strip_all_tags( $field[$key]['value'], $remove_breaks );
        return $value;
    }

    /**
     * Recursively modify every value of an array with kses
     * 
     * @param array $array 
     * @param string $allowed_html 
     * @param array $allowed_protocols 
     * @return array recursively modified array
     */
    public static function kses_array_values( $array, $allowed_html = 'post', $allowed_protocols = [ 'http', 'https', 'ftp' ] )
    {
        // $array_old = $array;
        $custom_data = array(
            'allowed_html' => $allowed_html,
            'allowed_protocols' => $allowed_protocols,
        );
        // apply a callback function recursively to every member of an array
        $success = array_walk_recursive( $array, array ('self', 'kses_array_walk_recursive'), $custom_data);
        return $array;
    }

    /**
     * Recursively modify every value of an array by strip all html tags
     * 
     * @param array $array 
     * @param string $allowed_html 
     * @param array $allowed_protocols 
     * @return array recursively modified array
     */
    public static function strip_array_values( $array, $remove_breaks = false)
    {
        // $array_old = $array;
        $custom_data = array(
            'remove_breaks' => $remove_breaks,
        );
        // apply a callback function recursively to every member of an array
        $success = array_walk_recursive( $array, array ('self', 'strip_array_walk_recursive'), $custom_data);
        return $array;
    }

    /**
     * Callback for array_walk_recursive() to kses from a value 
     *
     * @param mixed $value
     * @param mixed $key
     * @return void
     */
    public static function kses_array_walk_recursive(&$value, $key, $custom_data)
    {
        $value = wp_kses( $value, $custom_data['allowed_html'], $custom_data['allowed_protocols']);
    }

    /**
     * Callback for array_walk_recursive() to strip all tags from a value 
     *
     * @param mixed $value
     * @param mixed $key
     * @return void
     */
    public static function strip_array_walk_recursive(&$value, $key, $custom_data )
    {
        $value = wp_strip_all_tags( $value, $custom_data['remove_breaks'] );
    }
}