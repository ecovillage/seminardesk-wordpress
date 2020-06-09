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
        // TODO: register custom fields for native WordPress REST API necessary??? For now deactivated
        // $this->register_custom_fields();
    }

    /**
     * Register custom fields for SeminarDesk as meta keys for native WordPress Rest API (.../wp-json/wp/v2)
     *
     * @return void
     */
    public function register_custom_fields()
    {
        // sd_event meta data
        register_post_meta(
            'sd_event',
            'sd_event_id',
            array(
                'show_in_rest'  => true,
                'type'          => 'string',
                'description'   => 'Event ID from SeminarDesk',
                'auth_callback' => array( $this, 'check_permissions' ),
            ),
        );
        register_post_meta(
            'sd_event',
            'sd_data',
            array(
                'show_in_rest'  => true,
                'type'          => 'object',
                'description'   => 'Data from SeminarDesk',
                'auth_callback' => array( $this, 'check_permissions' ),
            ),
        );
        register_post_meta(
            'sd_event',
            'sd_webhook',
            array(
                'show_in_rest'  => true,
                'type'          => 'object',
                'description'   => 'Webhook data of the last POST request from SeminarDesk',
                'auth_callback' => array( $this, 'check_permissions' ),
            ),
        );

        // sd_dates meta data
        register_post_meta(
            'sd_date',
            'sd_date_id',
            array(
                'show_in_rest'  => true,
                'type'          => 'integer',
                'description'   => 'ID of event date in SeminarDesk',
                'auth_callback' => array( $this, 'check_permissions' ),
            ),
        );
        register_post_meta(
            'sd_date',
            'sd_event_id',
            array(
                'show_in_rest'  => true,
                'type'          => 'string',
                'description'   => 'ID of corresponding event in SeminarDesk',
                'auth_callback' => array( $this, 'check_permissions' ),
            ),
        );
        register_post_meta(
            'sd_date',
            'wp_event_id',
            array(
                'show_in_rest'  => true,
                'type'          => 'integer',
                'description'   => 'ID of corresponding event in WordPress',
                'auth_callback' => array( $this, 'check_permissions' ),
            ),
        );
        register_post_meta(
            'sd_date',
            'sd_data',
            array(
                'show_in_rest'  => true,
                'type'          => 'object',
                'description'   => 'Data from SeminarDesk',
                'auth_callback' => array( $this, 'check_permissions' ),
            ),
        );
        register_post_meta(
            'sd_date',
            'sd_webhook',
            array(
                'show_in_rest'  => true,
                'type'          => 'object',
                'description'   => 'Webhook data of the last POST request from SeminarDesk',
                'auth_callback' => array( $this, 'check_permissions' ),
            ),
        );

        // sd_facilitators meta data
        register_post_meta(
            'sd_facilitator',
            'sd_facilitator_id',
            array(
                'show_in_rest'  => true,
                'type'          => 'integer',
                'description'   => 'ID of event date in SeminarDesk',
                'auth_callback' => array( $this, 'check_permissions' ),
            ),
        );
        register_post_meta(
            'sd_facilitator',
            'sd_data',
            array(
                'show_in_rest'  => true,
                'type'          => 'object',
                'description'   => 'Data from SeminarDesk',
                'auth_callback' => array( $this, 'check_permissions' ),
            ),
        );
        register_post_meta(
            'sd_facilitator',
            'sd_webhook',
            array(
                'show_in_rest'  => true,
                'type'          => 'object',
                'description'   => 'Webhook data of the last POST request from SeminarDesk',
                'auth_callback' => array( $this, 'check_permissions' ),
            )
        );

    }
    public function check_permissions( $request )
    {
        return current_user_can( 'edit_posts' );
        // return true;
    }

}