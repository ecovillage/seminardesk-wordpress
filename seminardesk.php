<?php

/**
 * @package SeminardeskPlugin
 */

/*
Plugin Name: SeminarDesk for WordPress
Plugin URI: https://www.seminardesk.com/wordpress
Description: First Prototype of the SeminarDesk Plugin
Version: 1.0.0
Author: SeminarDesk – Danker, Smaluhn & Tammen GbR
Author URI: https://www.seminardesk.com/
License: GPLv2 or later
Text Domain: seminardesk
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

// security check if plugin tricked by WordPress
defined( 'ABSPATH' ) or die ('not allowed to access this file');

// init composer autoload
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php') ) {
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

/**
 * global constant variables to define environment
 */
define( 'SD_ENV', array(
    'file' => __FILE__,
    'path'  => plugin_dir_path( __FILE__ ),
    'url'   => plugin_dir_url( __FILE__ ),
    'base'  => plugin_basename( __FILE__ )
));

/**
 * constant variables to define admin page
 */

 define( 'SD_ADMIN', array(
    'page' => 'seminardesk_plugin',
    'position' => 65, // below Plugins
    'group_settings' => 'seminardesk_plugin_settings',
 ) );

/**
 * constant variables to define options
 */
define( 'SD_OPTION', array(
    'slugs'  => 'seminardesk_slugs',
    'debug' => 'seminardesk_debug',
    'delete' => 'seminardesk_delete',
) );

/**
 * constant variables to define custom post type
 */
define( 'SD_CPT', array(
    'sd_cpt_event'         => array( // don't rename this key
        'name'                  => 'Event',
        'names'                 => 'Events',
        'title'                 => 'CPT Events',
        'public'                => true,
        'exclude_from_search'   => false,
        'has_archive'           => true,
        'menu_position'         => 1, // position in submenu
        'taxonomies'            => array (),
        'slug_default'          => 'events',
        'slug_option_key'       => 'sd_slugs_cpt_events',
    ),
    'sd_cpt_date'          => array( // don't rename this key 
        'name'                  => 'Date',
        'names'                 => 'Dates',
        'title'                 => 'CPT Dates',
        'public'                => false,
        'exclude_from_search'   => false,
        'has_archive'           => true,
        'menu_position'         => 2,
        'taxonomies'            => array ( 'sd_txn_dates' ),
        'slug_default'          => 'dates',
        'slug_option_key'       => 'sd_slugs_cpt_dates',
    ),
    'sd_cpt_facilitator'   => array( // don't rename this key
        'name'                  => 'Facilitator',
        'names'                 => 'Facilitators',
        'title'                 => 'CPT Facilitators',
        'public'                => true,
        'exclude_from_search'   => false,
        'has_archive'           => true,
        'menu_position'         => 3,
        'taxonomies'            => array (),
        'slug_default'          => 'facilitators',
        'slug_option_key'       => 'sd_slugs_cpt_facilitator',
    ),
));

/**
 * Constant variables define custom taxonomies
 */
define( 'SD_TXN', array(
    'sd_txn_dates' => array( // don't rename this key
        'name'              => 'Date',
        'names'             => 'Dates',
        'title'             => 'TXN Dates',
        'public'            => true,
        'menu_position'     => 4,
        'object_type'       => array( 'sd_cpt_date' ),
        'slug_default'      => 'schedule',
        'slug_option_key'   => 'sd_slug_txn_dates',
    ),
));

/**
 * Constant variables define special terms of custom taxonomies
 */
define( 'SD_TXN_TERM', array(
    'upcoming'  => array( // don't rename this key
        'title'             => 'Term upcoming',
        'taxonomy'          => 'sd_txn_dates',
        'slug_default'      => 'upcoming',
        'slug_option_key'   => 'sd_slug_txn_dates_upcoming',
    ),
    'past'      => array( // don't rename this key
        'title'             => 'Term past',
        'taxonomy'          => 'sd_txn_dates',
        'slug_default'      => 'past',
        'slug_option_key'   => 'sd_slug_txn_dates_past',
    ),
));

// activation hook for plugin
register_activation_hook( SD_ENV['file'], array( 'Inc\Base\Activate', 'activate' ) );

// deactivation hook for plugin
register_deactivation_hook( SD_ENV['file'], array( 'Inc\Base\Deactivate', 'deactivate' ) );

// uninstall hook for plugin
register_uninstall_hook( SD_ENV['file'], array( 'Inc\Base\Uninstall', 'uninstall' ));

// register services utilizing the init class
if ( class_exists ( 'Inc\\Base\\Init' ) ) {
    $services = new Inc\Base\Init();
    $services->register_services();
    // alternative method to register services
    // Inc\Base\Init::register_services();
}