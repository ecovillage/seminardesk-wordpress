<?php
/**
 * The template tools for CPT events
 * 
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

use WP_Query;

class TemplateCptEvents
{
    /**
     * Get value by parsing and stripe (kses) a l10n array received in a payload generated and formatted by seminardesk
     *
     * @param array $array l10n formatted array by seminardesk
     * @param string $lang_tag Optional. select language by tag (e.g DE, EN, ES...)
     * @param string $before custom html code before value
     * @param string $after custom html after value
     * @param boolean $echo returned string
     * @return string localized value of the field
     */
    public static function get_value_by_language( $array, $lang_tag = 'DE', $before = '', $after = '', $echo = false )
    {

        $key = array_search($lang_tag, array_column($array, 'language'));
        // on failure get default language or first entry of the array
        if ( $key === false){
            $lang_default = 'DE';
            $key = array_search($lang_default, array_column($array, 'language'));
            if ( $key === false ){
                $key = '0';
            }
        }
        $value = $array[$key]['value'];
        $response = $before . $value . $after;
        if ( $echo ){
            echo $response;
        }

        return $response;
    }

    /**
     * Get html code for remote image url
     *
     * @param string $url
     * @param string $width image width
     * @param string $height image height
     * @param string $alt alternative text
     * @param string $before custom html code before <img ... />
     * @param string $after custom html after <img .../>
     * @param boolean $echo returned string
     * @return string html element <img .../> with remote link
     */
    public static function get_img_remote( $url, $width = '', $height = '', $alt = "remote image failed", $before = '', $after = '', $echo = false )
    {
        global $post;

        if ( $url ){
            $response = $before . '<img src="' . $url . '" alt="' . $alt . '" width="' . $width . '" height="' . $height . '"/>' . $after;
        }else{
            $response = null;
        }
        if ( $echo ){
            echo $response;
        }
        return $response;
    }

    /**
     * Get list of facilitators in html code including links for facilitator details
     * 
     * @param array $facilitator_ids 
     * @param string $before 
     * @param string $after 
     * @param bool $echo 
     * @return string|null 
     */
    public static function get_facilitators( $facilitator_ids, $before = '', $after = '' , $echo = false )
    {
        global $post;
        $facilitators = array();
        // query all facilitators from the database
        $custom_query = new WP_Query(
            array(
                'post_type'     => 'sd_facilitator',
                'post_status'   => 'publish',
            )
        );
        $facilitator_posts = $custom_query->get_posts();
        // get facilitator name from CPT sd_facilitators for all facilitator ids of $posts
        if ( $custom_query->have_posts() ){
            foreach ( $facilitator_ids as $value){
                foreach ( $facilitator_posts as $facilitator_post){
                    if ( $facilitator_post->facilitator_id == $value){
                        $facilitator_html = '<a href="' . esc_url(get_permalink($facilitator_post->ID)) . '">' . get_the_title($facilitator_post) . '</a>';
                        array_push($facilitators, $facilitator_html);
                    }
                }
            }
        }
        // sort array of received facilitator names ascending
        sort($facilitators);

        if ( !empty($facilitators) ){
            $response = $before . implode(" | ",$facilitators) . $after;
        }else{
            $response = null;
        }
        if ( $echo ){
            echo $response;
        }
        return $response;
    }

    /**
     * Get html list of all upcoming event dates for this event
     * 
     * @param mixed $event_id 
     * @param string $before 
     * @param string $after 
     * @param bool $echo 
     * @return string
     */
    public static function get_event_dates_list( $event_id, $before = '', $after = '' , $echo = false )
    {
        $timestamp_today = strtotime(wp_date('Y-m-d'));
        // $timestamp_today = strtotime('2020-04-01');
        $custom_query = new WP_Query(
            array(
                'post_type'     => 'sd_date',
                'post_status'   => 'publish',
                'meta_key'      => 'begin_date',
                'orderby'       => 'meta_value_num',
                'order'         => 'ASC',
                'meta_query'    => array(
                    'relation'      => 'AND',
                    'condition1'    => array(
                        'key'           => 'begin_date',
                        'value'         => $timestamp_today*1000, //in ms
                        'type'          => 'NUMERIC',
                        'compare'       => '>=',
                    ),
                    'condition2'    => array(
                        'key'           => 'event_id',
                        'value'         => $event_id,
                        'type'          => 'CHAR',
                        'compare'       => '='
                        
                    ),
                ),
            )
        );
        $date_posts = $custom_query->get_posts();
        $dates = array();
        if ( $custom_query->have_posts() ){
            foreach ( $date_posts as $date_post) {
                $begin_date = date_i18n( 'D, d.m.Y', $date_post->begin_date/1000 );
                $end_date = date_i18n( 'D, d.m.Y', $date_post->end_date/1000 );
                $date = $begin_date . ' - ' . $end_date;
                // rtrim() or wp_strip_all_tags...
                $title = wp_strip_all_tags($date_post->post_title) . ': ';
                $price = wp_strip_all_tags($date_post->price_info);
                $status = $date_post->status;
                $status_lib = array(
                    'available'     => __('Booking Available', 'seminardesk'),
                    'fully_booked'  => __('Fully Booked', 'seminardesk'),
                    'limited'       => __('Limited Booking', 'seminardesk'),
                    'wait_list'     => __('Waiting List', 'seminardesk'),
                );
                $status_msg = $status_lib[$status];
                $venue = $date_post->venue;

                $date_props = array();
                array_push($date_props, $date, $price, $status_msg, $venue);
                $date_props = array_filter($date_props); // remove empty values from array
                $date_html = '<li>' . $title . implode(', ', $date_props) . '</li>';
                array_push($dates, $date_html);
            }
        }

        if ( !empty($dates) ){
            $response = $before . '<ol>' . implode('', $dates) . '</ol>' . $after;
        }else{
            $response = null;
        }
        if ( $echo ){
            echo $response;
        }
        return $response;
    }

}