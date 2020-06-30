<?php

/**
 * @package SeminardeskPlugin
 */

namespace Inc\Controllers;

use WP_REST_Controller;
use WP_REST_Server;
use Inc\Callbacks\RestCallbacks;

/**
 * SeminarDesk's HTTP client supporting Webhooks
 * Custom Rest Controller class for sd_cpt_events,
 * following the WordPress pattern: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
 */
class RestController extends WP_REST_Controller
{

    /**
     * base name to build a route
     *
     * @var string
     */
    protected $base_webhook, $base_cpt_event, $base_cpt_date, $base_cpt_facilitator;
    
    function __construct()
    {
        $this->register();
    }

    /**
     * Register rest via controller class
     * 
     * @return void 
     */
    public function register()
    {
        $this->namespace = 'seminardesk/v1';
        $this->base_webhook = 'webhooks';
        $this->base_cpt_event = 'cpt_events';
        $this->base_cpt_date = 'cpt_dates';
        $this->base_cpt_facilitator = 'cpt_facilitators';

        // add custom REST API for sd_cpt_events
        add_action('rest_api_init', array($this, 'create_routes'));
    }

    /**
     * Register custom namespace, its route and methods for sd_cpt_events
     * /wp-json/seminardesk/v1/events
     * @return void
     */
    public function create_routes()
    {
        $rest = new RestCallbacks;

        // Webhook route registration for HTTP POSTs from SeminarDesk
        register_rest_route($this->namespace, '/' . $this->base_webhook, array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array($rest, 'create_webhooks'),
                'permission_callback' => array($rest, 'post_check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
        ));

        // Event route registration to get all events
        register_rest_route($this->namespace, '/' . $this->base_cpt_event, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($rest, 'get_cpt_events'),
                'permission_callback' => array($rest, 'get_check_permissions'),
                // 'args'                => array(),
            ),
        ));

        // Event route registration to get specific event
        register_rest_route($this->namespace, '/' . $this->base_cpt_event . '/(?P<event_id>[0-9]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($rest, 'get_cpt_event'),
                'permission_callback' => array($rest, 'get_check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
        ));

        // Event date route registration to get all event dates
        register_rest_route($this->namespace, '/' . $this->base_cpt_date, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($rest, 'get_cpt_dates'),
                'permission_callback' => array($rest, 'get_check_permissions'),
                // 'args'                => array(),
            ),
        ));

        // Event date route registration to get specific event date
        register_rest_route($this->namespace, '/' . $this->base_cpt_date . '/(?P<date_id>[0-9]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($rest, 'get_cpt_date'),
                'permission_callback' => array($rest, 'get_check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
        ));

        // Facilitator route registration to get all facilitators
        register_rest_route($this->namespace, '/' . $this->base_cpt_facilitator, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($rest, 'get_cpt_facilitators'),
                'permission_callback' => array($rest, 'get_check_permissions'),
                // 'args'                => array(),
            ),
        ));

        // Facilitator route registration to get specific facilitator
        register_rest_route($this->namespace, '/' . $this->base_cpt_facilitator . '/(?P<facilitator_id>[a-z0-9]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($rest, 'get_cpt_facilitator'),
                'permission_callback' => array($rest, 'get_check_permissions'),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
        ));
    }
}
