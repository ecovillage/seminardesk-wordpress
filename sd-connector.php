<?php

/**
 * @package SeminardeskPlugin
 */

/*
Plugin Name: SeminarDesk for WordPress
Plugin URI: https://www.seminardesk.com/wordpress
Description: First Prototype of the SeminarDesk Connector Plugin
Version: 1.0.0
Author: SeminarDesk – Danker, Smaluhn & Tammen GbR
Author URI: https://www.seminardesk.com/
License: GPLv2 or later
Text Domain: seminardesk-connector
*/

/*
* Copyright 2020 by SeminarDesk and the contributors
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

// TODO: security checks to handel errors and return error messages

// security check if plugin tricked by WordPress
defined( 'ABSPATH' ) or die ('not allowed to access this file');

// init composer autoload
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php') ) {
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

// global constant variables
define( 'SD_PLUGIN_FILE', __FILE__ );
define( 'SD_PLUGIN_PATH', plugin_dir_path( SD_PLUGIN_FILE ) );
define( 'SD_PLUGIN_URL', plugin_dir_url( SD_PLUGIN_FILE ) );
define( 'SD_PLUGIN', plugin_basename( SD_PLUGIN_FILE ) );

// activation hook for plugin
register_activation_hook( SD_PLUGIN_FILE, array( 'Inc\Base\Activate', 'activate' ) );

// deactivation hook for plugin
register_deactivation_hook( SD_PLUGIN_FILE, array( 'Inc\Base\Deactivate', 'deactivate' ) );

// register services utilizing the init class
if ( class_exists ( 'Inc\\Init' ) ) {
    $services = new Inc\Init();
    $services->register_services();
    // alternative method to register services
    // Inc\Init::register_services();
}