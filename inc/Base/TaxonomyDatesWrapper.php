<?php
/**
 * The template for taxonomy dates
 * 
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

use WP_Query;

class TaxonomyDatesWrapper
{
    /**
     * Get start and end date of the event
     *
     * @param string $before
     * @param string $after
     * @param boolean $echo
     * @return string
     */
    public static function get_date( $before = '', $after = '', $echo = true )
    {
        global $post;
        $date = array(
            'begin' => date_i18n('l d.m.Y', $post->begin_date/1000),
            'end'  => date_i18n('l d.m.Y', $post->end_date/1000),
        );

        $response = $before . __('<strong>Date: </strong>', 'seminardesk') . $date['begin'] . ' - ' . $date['end'] . $after;
        if ( $echo ){
            echo $response;
        }
        return $response;
    }

    public static function get_price( $before = '', $after = '' , $echo = true )
    {
        global $post;
        $price = $post->price_info;
        if ( !empty($price) ){
            $response = $before . __('<strong>Price Info: </strong>', 'seminardesk') . $price . $after;
        }else{
            $response = null;
        }

        if ( $echo ){
            echo $response;
        }
        return $response;
    }

    public static function get_venue( $before = '', $after = '' , $echo = true )
    {
        global $post;
        $venue = $post->venue;
        if ( !empty($venue) ){
            $response = $before . __('<strong>Venue: </strong>', 'seminardesk') . $venue . $after;
        }else{
            $response = null;
        }

        if ( $echo ){
            echo $response;
        }
        return $response;
    }

    public static function get_facilitators( $before = '', $after = '' , $echo = true )
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
        $ids = $post->facilitator_ids;
        if (is_array($ids)){
            foreach ( $ids as $key => $value){
                foreach ( $facilitator_posts as $facilitator_post){
                    if ( $facilitator_post->facilitator_id == $value['id']){
                        $facilitator_html = '<a href="' . esc_url(get_permalink($facilitator_post->ID)) . '">' . get_the_title($facilitator_post) . '</a>';
                        array_push($facilitators, $facilitator_html);
                    }
                }
            }
        }
        // sort array of received facilitator names ascending
        sort($facilitators);

        if ( !empty($facilitators) ){
            $response = $before . __('<strong>Facilitators: </strong>', 'seminardesk'). implode(" | ",$facilitators) . $after;
        }else{
            $response = null;
        }
        if ( $echo ){
            echo $response;
        }
        return $response;
    }

    public static function get_img_remote( $url, $width = '', $height = '', $alt = "remote image load failed", $before = '', $after = '', $echo = true )
    {
        global $post;
        // $url = $post->teaser_picture_url;
        if ( $url ){
            $response = $before . '<img src="' . $post->teaser_picture_url . '" alt="' . $alt . '" width="' . $width . '" height="' . $height . '"/>' . $after;
        }else{
            $response = null;
        }
        if ( $echo ){
            echo $response;
        }
        return $response;
    }
}