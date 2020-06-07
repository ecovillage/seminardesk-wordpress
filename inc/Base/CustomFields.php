<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

// TODO: create modular custom fields manager class (CF API) to handel different custom fields with the Meta API (event, calender, facilitators)

/**
 * Handel custom fields for custom post types utilizing Meta API of WordPress for
 */
class CustomFields
{
    public function register ()
    {
        $this->register_custom_fields();
    }

    /**
     * Register custom fields for SeminarDesk as meta keys
     *
     * @return void
     */
    public function register_custom_fields()
    {
        $meta_args = [

        ];

        // sd_event meta data
        register_post_meta(
            'sd_event',
            'event_id',
            [
                'show_in_rest'  => true,
                'type'          => 'string',
                'description'   => 'Event ID',
                'auth_callback' => function(){
                    // return current_user_can( 'edit_posts' );
                    return false;
                }
            ]
        );

        register_post_meta(
            'sd_event',
            'json_dump',
            [
                'show_in_rest'  => true,
                'type'          => 'object',
                'description'   => 'holds the plain JSON data of the last POST request',
                'auth_callback' => function(){
                    // return current_user_can( 'edit_posts' );
                    return false;
                }
            ]
        );

        // sd_dates meta data
        register_post_meta(
            'sd_date',
            'begin_date',
            [
                'show_in_rest'  => true,
                'type'          => 'integer',
                'description'   => 'Start of the event date as a timestamp',
                'auth_callback' => function(){
                    // return current_user_can( 'edit_posts' );
                    return false;
                }
            ]
        );

        register_post_meta(
            'sd_date',
            'end_date',
            [
                'show_in_rest'  => true,
                'type'          => 'integer',
                'description'   => 'End of the event date as a timestamp',
                'auth_callback' => function(){
                    // return current_user_can( 'edit_posts' );
                    return false;
                }
            ]
        );
    }

    public function check_permissions( $request )
    {
        // return current_user_can( 'edit_posts' );
        return true;
    }

}