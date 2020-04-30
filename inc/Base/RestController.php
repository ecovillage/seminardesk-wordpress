<?php

/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

// TODO: review includes
use WP_REST_Controller;
use WP_REST_Server;
use Inc\Api\Callbacks\RestCallbacks;

// TODO: error handling, implementation of WP_Error
//         - return client error 400, if payload doesn't include required fields
// TODO: POST endpoint
//        - define required fields 
// TODO: permission check
// TODO: handle scheme and it's callbacks proper
// TODO: guaranty unique IDs ... evaluate ids before executing POST, update and delete request request_id, event_id, date_id, facilitator_id

/**
 * SeminarDesk's HTTP client supporting Webhooks
 * Custom Rest Controller class for sd_events,
 * following the WordPress pattern: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
 */
class RestController extends WP_REST_Controller
{

    /**
     * base name to build a route
     *
     * @var string
     */
    protected $base_webhook, $base_event, $base_date, $base_facilitator;
    
    function __construct()
    {
        $this->register();
    }

    public function register()
    {
        $this->namespace = 'seminardesk/v1';
        $this->base_webhook = 'webhooks';
        $this->base_event = 'events';
        $this->base_date = 'dates';
        $this->base_facilitator = 'facilitators';

        // add custom REST API for sd_events
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register custom namespace, its route and methods for sd_events
     * /wp-json/seminardesk/v1/events
     * @return void
     */
    public function register_routes()
    {
        $rest = new RestCallbacks;

        // Webhook route registration for HTTP POSTs from SeminarDesk
        register_rest_route($this->namespace, '/' . $this->base_webhook, array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array($rest, 'create_webhooks'),
                'permission_callback' => array($rest, 'check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
        ),);

        // Event route registration to get all events
        register_rest_route($this->namespace, '/' . $this->base_event, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($rest, 'get_events'),
                'permission_callback' => array($rest, 'check_permissions'),
                // 'args'                => array(),
            ),
        ),);

        // Event ID route registration to get specific event
        register_rest_route($this->namespace, '/' . $this->base_event . '/(?P<event_id>[a-z0-9]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($rest, 'get_event'),
                'permission_callback' => array($rest, 'check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
        ),);
    }
}
