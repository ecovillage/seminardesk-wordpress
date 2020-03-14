<?php
/**
 * @package SDCP
 */

/*
Plugin Name: SeminarDesk for Wordpress
Plugin URI: https://www.seminardesk.com/sd-connector
Description: First Prototyp of the SeminarDesk Connector Plugin
Version: 1.0.0
Author: SeminarDesk – Danker, Smaluhn & Tammen GbR
Author URI: https://www.seminardesk.com/
License: GPLv2 or later
Text Domain: seminardesk-connector
*/

/*
*Copyright 2020 by SeminarDesk and the contributors
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// security check if plugin trickered by wordpress
defined( 'ABSPATH' ) or die ('not allowed to access this file');

// custom variables
//define( 'SD_CONNECTOR_FILE', __FILE__ );

class SdConnector{
    function __construct() {

    }

    function register() {
        // generate CPT for events
        add_action( 'init', array( $this, 'custom_post_type' ) );
        // enqueue assets
        add_action('wp_enqueue_scripts', array ( $this, 'enqueue'));
    }

    function activate(){
        // generate a CPT
        $this->custom_post_type();
        // flush rewrite rules
        flush_rewrite_rules();
    }

    function deactivate(){
        // flush  rewite rules
        flush_rewrite_rules();
    }

    function custom_post_type() {
        register_post_type( 'sd_event', ['public' => true, 'label' => 'SD Events'] );
    }

    function enqueue(){
        // enqueue scripts
        wp_enqueue_style( 'sdstyle', plugins_url( '/assets/mystyle.css', __FILE__ ));
        wp_enqueue_script( 'sdscript', plugins_url( '/assets/myscript.js', __FILE__ ));
    }
}

// check if class exists and create optject
if (class_exists( 'SdConnector') ) {
    $sdPlugin = new SdConnector();
    $sdPlugin->register();
}

// hooks
// activation hook for plugin
register_activation_hook(__FILE__, array( $sdPlugin, 'activate' ) );

// deactivation hook for plugin
register_deactivation_hook(__FILE__, array( $sdPlugin, 'deactivate' ) );
