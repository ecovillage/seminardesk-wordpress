<?php
/**
 * 
 * @package SeminardeskPlugin
 */

namespace Inc\Utils;

use WP_Query;
use Inc\Utils\TemplateUtils as Utils;

/**
 * Set of utilities for data used in the templates of the plugin
 */
class TemplateUtils
{
    /**
     * Escape URL and check if remote file exists
     * 
     * @param string $url 
     * @return bool
     */
    public static function url_exists( $url )
    {
        // TODO: since wait for response increases the loading time of the page

        // via wp_remote_get HEAD
        // $url = esc_url( $url );
        // // via wp http api
        // $args = array(
        //     'method' => 'HEAD',
        // );
        // $response = wp_remote_get( $url, $args );
        // if ( $response['response']['code'] === 200 ){
        //     return true;
        // }
        // return false;

        // via curl
        $ch = curl_init( $url );
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_exec ( $ch );
        $retcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        // $retcode >= 400 -> not found, $retcode = 200, found.
        curl_close($ch);
        return $retcode === 200 ? true : false; 
    }



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

        $response = !empty($array[$key]['value']) ? $before . $array[$key]['value'] . $after : null;
        
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
        if ( is_string( $url ) ){
            $url = esc_url( $url );
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
     * @param array $facilitators 
     * @param string $before 
     * @param string $after 
     * @param bool $echo 
     * @return string|null 
     */
    public static function get_facilitators( $facilitators, $before = '', $after = '' , $echo = false )
    {
        $facilitators_html = array();
        foreach ($facilitators as $facilitator){
            // query facilitator by id from the database
            $custom_query = new WP_Query(
                array(
                    'post_type'     => 'sd_cpt_facilitator',
                    'post_status'   => 'publish',
                    'meta_key'      => 'sd_facilitator_id',
                    'meta_query'    => array(
                        'key'       => 'sd_facilitator_id',
                        'value'     => $facilitator['id'],
                        'type'      => 'numeric',
                        'compare'   => '=',
                    ),
                )
            );
            // loop to get facilitator name and link, create html code to push in array
            if ( $custom_query->have_posts() ){
                while ($custom_query->have_posts()){
                    $custom_query->the_post();
                    $facilitator_html = '<a href="' . esc_url(get_permalink($custom_query->post->ID)) . '">' . get_the_title($custom_query->post) . '</a>';
                        array_push($facilitators_html, $facilitator_html);
                }

            }
        }
        // wp_reset_query();
        wp_reset_postdata();

        if ( !empty($facilitators_html) ){
            // sort array of received facilitator names ascending
            sort($facilitators_html);
            $response = $before . implode(" | ",$facilitators_html) . $after;
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
                'post_type'     => 'sd_cpt_date',
                'post_status'   => 'publish',
                'meta_key'      => 'sd_date_begin',
                'orderby'       => 'meta_value_num',
                'order'         => 'ASC',
                'meta_query'    => array(
                    'relation'      => 'AND',
                    'condition1'    => array(
                        'key'           => 'sd_date_begin',
                        'value'         => $timestamp_today*1000, //in ms
                        'type'          => 'NUMERIC',
                        'compare'       => '>=',
                    ),
                    'condition2'    => array(
                        'key'           => 'sd_event_id',
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
                // $date_begin = wp_date( 'D, d.m.Y', $date_post->sd_date_begin/1000 );
                // $date_end = wp_date( 'D, d.m.Y', $date_post->sd_date_end/1000 );
                // $date = $date_begin . ' - ' . $date_end;
                $date = Utils::get_date( $date_post->sd_date_begin,  $date_post->sd_date_end );
                // rtrim() or wp_strip_all_tags...
                $title = wp_strip_all_tags($date_post->post_title) . ': ';
                $price = wp_strip_all_tags(Utils::get_value_by_language($date_post->sd_data['priceInfo']));
                $status = $date_post->sd_data['status'];
                $status_lib = array(
                    'available'     => __('Booking Available', 'seminardesk'),
                    'fully_booked'  => __('Fully Booked', 'seminardesk'),
                    'limited'       => __('Limited Booking', 'seminardesk'),
                    'wait_list'     => __('Waiting List', 'seminardesk'),
                );
                $status_msg = $status_lib[$status];
                $venue_props = $date_post->sd_data['venue'];
                $venue = Utils::get_venue($venue_props);

                $date_props = array();
                array_push($date_props, $date, $price, $status_msg, $venue);
                $date_props = array_filter($date_props); // remove all empty values from array
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

    /**
     * Get start and end date of the event date
     *
     * @param integer $begin_date
     * @param integer $end_date
     * @param string $format https://www.php.net/manual/en/function.date.php
     * @param string $before
     * @param string $after
     * @param boolean $echo
     * @return string|null
     */
    public static function get_date( $begin_date, $end_date, $before = '', $after = '', $echo = false )
    {
        global $post;
        $date_day = array(
            'begin' => wp_date( 'D. d.m.Y', $begin_date/1000),
            'end'  => wp_date( 'D. d.m.Y', $end_date/1000),
        );
        $date = array(
            'begin' => wp_date( 'D. d.m.Y H:i', $begin_date/1000),
            'end'  => wp_date( 'D. d.m.Y H:i', $end_date/1000),
        );

        if ( $date_day['begin'] === $date_day['end'] ){
            $response = $before . $date['begin'] . ' – ' . wp_date( 'H:i', $end_date/1000) . $after;
        }elseif ( !empty($date['begin']) && !empty($date['end']) ){
            $response = $before . $date['begin'] . ' – ' . $date['end'] . $after;
        }elseif (!empty($date['begin'])) {
            $response = $before . $date['begin'] . $after;
        }else{
            $response = null;
        }
        
        if ( $echo ){
            echo $response;
        }
        return $response;
    }

    /**
     * get venue information of the event date
     * 
     * @param array $venue_props
     * @param string $before 
     * @param string $after 
     * @param bool $echo 
     * @return string|null 
     */
    public static function get_venue( $venue_props, $before = '', $after = '' , $echo = false )
    {
        if ( !empty($venue_props) ){
            $venue_link = esc_url($venue_props['weblink']);
            $venue_props['weblink'] = '';
            $venue_props = array_filter($venue_props); // remove all empty values from array
            if (!empty($venue_link)){
                $venue = '<a href="' . $venue_link . '">' . implode(', ', $venue_props) . '</a>';
            } elseif (!empty($venue_props)) {
                $venue = implode(', ', $venue_props);
            }
            else {
                $venue = '';
            }
            $response = $before .$venue . $after;
        }else{
            $response = null;
        }

        if ( $echo ){
            echo $response;
        }
        return $response;
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