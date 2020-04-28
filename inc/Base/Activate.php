<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

use Inc\CPT\Events;

class Activate
{
    /**
     * code that runs during plugin activation
     *
     * @return void
     */
     public static function activate() {
        if ( class_exists( 'Inc\\CPT\\Events' ) ) {
            Events::create_cpt();
        }
        flush_rewrite_rules();
     }

 }