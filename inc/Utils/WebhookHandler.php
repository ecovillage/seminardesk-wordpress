<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Utils;

use WP_Error;
use WP_REST_Response;
use WP_Query;
use Inc\Utils\TemplateUtils as Utils;

/**
 * Handler for webhook actions
 * !serialized meta data (custom field sd_data) cannot and should not be queried!
 * https://wordpress.stackexchange.com/questions/16709/meta-query-with-meta-values-as-serialize-arrays
 */
class WebhookHandler
{
    /**
     * Create or update event via webhook from SeminarDesk
     *
     * @param array $request_json 
     * @return WP_REST_Response|WP_Error
     */
    public static function put_event( $request_json )
    {
        $payload = (array)$request_json['payload']; // payload of the request in JSON
        $sd_webhook = $request_json;
        unset($sd_webhook['payload']);
        // checks if event_id exists and sets corresponding post_id
        $query = self::get_query_by_meta( 'sd_cpt_event', 'sd_event_id', $payload['id']);
        $post_id = $query->post->ID ?? null;
        $event_count = $query->post_count;

        // define metadata of the event
        $meta_input = [
            'sd_event_id'  => $payload['id'],
            'sd_data'      => $payload,
            // 'sd_data'      => Utils::kses_array_values($payload),
            // 'sd_data'      => Utils::strip_array_values($payload),
            'sd_webhook'    => $sd_webhook,
        ];

        // set attributes of the new event
        $event_attr = [
            'post_type'     => 'sd_cpt_event',
            'post_title'    => Utils::get_value_by_language( $payload['title'] ),
            // 'post_title'    => Utils::strip_get_value_by_language( $payload['title'] ),
            'post_author'   => get_current_user_id(),
            'post_status'   => 'publish',
            'meta_input'    => $meta_input,
        ];
        
        // create new post or update post with existing post id
        if ( isset($post_id) ) {
            $event_attr['ID'] = $post_id;
            $message = 'Event updated';
            $post_id = wp_update_post( wp_slash($event_attr), true );
        } else {
            $message = 'Event created';
            $post_id = wp_insert_post(wp_slash($event_attr), true);
        }

        // return error if $post_id is of type WP_Error
        if (is_wp_error($post_id)){
            return $post_id;
        }
        
        return new WP_REST_Response( [
            'message'       => $message,
            'requestId'     => $request_json['requestId'],
            'action'        => $request_json['action'],
            'eventId'       => $payload['id'],
            'eventWpId'     => $post_id,
            'eventWpCount'  => $event_count,
        ], 200);
    }

    /**
     * Create event via webhook from SeminarDesk utilizing put_event
     * Incase event id already exists, it will update existing event
     *
     * @param array $request_json 
     * @return WP_REST_Response|WP_Error
     */
    public static function create_event($request_json)
    {
       return self::put_event($request_json);
    }

    /**
     * Update event via webhook from SeminarDesk utilizing put_event
     * Incase event id doesn't exists, it will create the event
     *
     * @param array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function update_event($request_json)
    {
        return self::put_event($request_json);
    }

    /**
     * Delete event via event id
     *
     * @param array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function delete_event($request_json) 
    {
        $payload = (array)$request_json['payload'];
        $post_deleted = self::trash_post_by_meta('sd_cpt_event', 'sd_event_id', $payload['id']);
        if ( !isset($post_deleted) ){
            return new WP_Error('not_found', 'Nothing to delete. Event ID ' . $payload['id'] . ' does not exists', array(
                'status'        => 404,
                'requestId'     => $request_json['requestId'],
                'action'        => $request_json['action'],
                'eventId'   => $payload['id'],
            ));
        }
        return new WP_REST_Response( [
            'message'       => 'Event deleted',
            'requestId'     => $request_json['requestId'],
            'action'        => $request_json['action'],
            'eventId'       => $payload['id'],
            'eventWpId'     => $post_deleted->ID,
        ], 200);
    }

    /**
     * Create or update event date via webhook from SeminarDesk
     *
     * @param array $request_json 
     * @return WP_REST_Response|WP_Error
     */
    public static function put_event_date( $request_json )
    {
        $payload = (array)$request_json['payload'];

        $sd_webhook = $request_json;
        unset($sd_webhook['payload']);

        // check if with event date associated event exists and get its WordPress ID
        $event_query = self::get_query_by_meta( 'sd_cpt_event', 'sd_event_id', $payload['eventId']);
        $event_post_id = $event_query->post->ID ?? null;
        if (!isset($event_post_id)){
            return new WP_Error('not_found' ,'Event date not created. Associated event with the ID ' . $payload['eventId'] . ' does not exist', array( 
                'status'        => 404,
                'requestId'     => $request_json['requestId'],
                'action'        => $request_json['action'],
                'eventDateId'   => $payload['id'],
                'eventId'       => $payload['eventId'],
            ));
        }
        $event_count = $event_query->post_count;

        // check if event date exists and sets corresponding date_post_id
        $date_query = self::get_query_by_meta( 'sd_cpt_date', 'sd_date_id', $payload['id']);
        $date_post_id = $date_query->post->ID ?? null;
        $date_count = $date_query->post_count;

        // define attributes of the new event date using request data of the webhook
        $txn_input = self::set_event_date_taxonomy($payload);
        $meta_input = [
            'sd_date_id'    => $payload['id'],
            'sd_date_begin' => $payload['beginDate'],
            'sd_date_end'   => $payload['endDate'],
            'sd_event_id'   => $payload['eventId'],
            'wp_event_id'   => $event_post_id,
            'sd_data'       => $payload,
            // 'sd_data'       => Utils::kses_array_values($payload),
            'sd_webhook'    => $sd_webhook,
        ];
        $date_attr = [
            'post_type'     => 'sd_cpt_date',
            'post_title'    => $payload['title']['0']['value'],
            'post_author'   => get_current_user_id(),
            'post_status'   => 'publish',
            'meta_input'    => $meta_input,
            'tax_input'     => $txn_input,
        ];
        
        // create new post or update post with existing post id
        if ( isset($date_post_id) ) {
            $date_attr['ID'] = $date_post_id;
            $message = 'Event Date updated';
            $date_post_id = wp_update_post( wp_slash($date_attr), true );
        } else {
            $message = 'Event Date created';
            $date_post_id = wp_insert_post(  wp_slash($date_attr), true);
        }
        
        // return error if $date_post_id is of type WP_Error
        if (is_wp_error($date_post_id)){
            return $date_post_id;
        }
        return new WP_REST_Response( [
            'message'           => $message,
            'requestId'         => $request_json['requestId'],
            'action'            => $request_json['action'],
            'eventDateId'       => $payload['id'],
            'eventDateWpId'     => $date_post_id,
            'EventDateWpCount'  => $date_count,
            'eventId'           => $payload['eventId'],
            'eventWpId'         => $event_post_id,
            'eventWpCount'      => $event_count,
        ], 200);
    }

    /**
     * Create event date via webhook from SeminarDesk utilizing put_event_date
     * Incase event date id already exists, it will update existing event date
     *
     * @param array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function create_event_date($request_json)
    {   
        return self::put_event_date( $request_json );
    }

    /**
     * Update event date via webhook from SeminarDesk utilizing put_event_date
     * Incase event date id doesn't exists, it will create the event date
     *
     * @param array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function update_event_date($request_json)
    {   
        return self::put_event_date( $request_json );
    }

    /**
     * Delete event date via webhook from SeminarDesk
     *
     * @param array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function delete_event_date($request_json)
    {   
        $payload = (array)$request_json['payload'];
        $post_deleted = self::trash_post_by_meta('sd_cpt_date', 'sd_date_id', $payload['id']);
        
        if ( !isset($post_deleted) ){
            return new WP_Error('not_found', 'Nothing to delete. Event date ID ' . $payload['id'] . ' does not exists', array(
                'status'        => 404,
                'requestId'     => $request_json['requestId'],
                'action'        => $request_json['action'],
                'eventDateId'   => $payload['id'],
            ));
        }
        return new WP_REST_Response( [
            'message'       => 'Event date deleted',
            'requestId'     => $request_json['requestId'],
            'action'        => $request_json['action'],
            'eventDateId'   => $payload['id'],
            'eventDateWpId' => $post_deleted->ID,
        ], 200);
    }
    
    /**
     * Create or update facilitator via webhook from SeminarDesk
     *
     * @param array $request_json 
     * @return WP_REST_Response|WP_Error
     */
    public static function put_facilitator( $request_json )
    {
        $payload = (array)$request_json['payload'];

        $sd_webhook = $request_json;
        unset($sd_webhook['payload']);

        $query = self::get_query_by_meta( 'sd_cpt_facilitator', 'sd_facilitator_id', $payload['id'] );
        $post_id = $query->post->ID ?? null;
        
        // define metadata of the new sd_cpt_facilitator
        $meta_input = [
            'sd_facilitator_id' => $payload['id'],
            'sd_data'           => $payload,
            // 'sd_data'           => Utils::kses_array_values($payload),
            'sd_webhook'           => $sd_webhook,
        ];
        // define attributes of the new facilitator using $payload of the 
        $facilitator_attr = [
            'post_type'         => 'sd_cpt_facilitator',
            'post_title'        => $payload['name'],
            'post_author'       => get_current_user_id(),
            'post_status'       => 'publish',
            'meta_input'        => $meta_input,
        ];

        // create new post or update post with existing post id
        if ( isset($post_id) ) {
            $facilitator_attr['ID'] = $post_id;
            $message = 'Facilitator updated';
            $post_id = wp_update_post( wp_slash($facilitator_attr), true );
        } else {
            $message = 'Facilitator created';
            $post_id = wp_insert_post(wp_slash($facilitator_attr), true);
        }

        // return error if $post_id is of type WP_Error
        if (is_wp_error($post_id)){
            return $post_id;
        }

        return new WP_REST_Response( [
            'message'           => $message,
            'requestId'         => $request_json['requestId'],
            'action'            => $request_json['action'],
            'facilitatorId'     => $payload['id'],
            'facilitatorWpId'   => $post_id,
        ], 200);
    }

    /**
     * Create facilitator event via webhook from SeminarDesk
     * Incase facilitator id already exists, it will update existing facilitator
     *
     * @param array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function create_facilitator($request_json)
    {
        return self::put_facilitator( $request_json );
    }

    /**
     * Update facilitator event via webhook from SeminarDesk
     * Incase facilitator id doesn't exists, it will create the facilitator
     *
     * @param array $request_json
     * @return WP_REST_Response|WP_Error
     */
    public static function update_facilitator($request_json)
    {
        return self::put_facilitator( $request_json );
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
        $post_deleted = self::trash_post_by_meta('sd_cpt_facilitator', 'sd_facilitator_id', $payload['id']);

        if ( !isset($post_deleted) ){
            return new WP_Error('not_found', 'Nothing to delete. Facilitator ID ' . $payload['id'] . ' does not exists', array(
                'status'        => 404,
                'requestId'     => $request_json['requestId'],
                'action'        => $request_json['action'],
                'eventDateId'   => $payload['id'],
            ));
        }
        return new WP_REST_Response( [
            'message'           => 'Facilitator deleted',
            'requestId'         => $request_json['requestId'],
            'action'            => $request_json['action'],
            'facilitatorId'     => $payload['id'],
            'facilitatorWpId'   => $post_deleted->ID,
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
    public static function trash_post_by_meta( $post_type, $meta_key, $meta_value )
    {
        $query = self::get_query_by_meta( $post_type, $meta_key, $meta_value );
        $post_id = $query->post->ID ?? 0;
        $post_deleted = wp_trash_post($post_id);
        return $post_deleted;
    }

    /**
     * define taxonomy 'sd_txn_dates' for the event date and create terms if does not exist
     *
     * @param array $payload payload send form seminardesk via webhook
     * @return array custom taxonomy for the event date
     */
    public static function set_event_date_taxonomy($payload)
    {
        $txn_dates = 'sd_txn_dates';
        $year = wp_date('Y', $payload['beginDate']/1000);
        $month = wp_date('m', $payload['beginDate']/1000);
        // get term ID for date year and create if term doesn't exist incl. months as its children terms
        $term_year = term_exists($year, $txn_dates); 
        if (!isset($term_year)){
            $term_year = wp_insert_term($year, $txn_dates, array(
                'description' => __('Dates of ' . $year, 'seminardesk'),
                'slug' => $year,
            ));
            for ($m = 1; $m <= 12; $m++){
                $m_padded = sprintf('%02s', $m);
                wp_insert_term($m_padded . '/' . $year, $txn_dates, array(
                    // 'alias_of'      => $year,
                    'description'   => __('Dates of ' . $m_padded . '/' . $year, 'seminardesk'),
                    'parent'        => $term_year['term_taxonomy_id'],
                    'slug'          => $m_padded,
                ));
            }
        }

        // define taxonomies of the new sd_event_date
        // user to create new sd_event_date needs capability to work with a taxonomy
        $term_month = term_exists($month, $txn_dates);
        $terms = array($term_year['term_taxonomy_id'], $term_month['term_taxonomy_id']);
        $txn_input = array(
            $txn_dates => $terms,
        );

        return $txn_input;
    }
}