<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

class Enqueue
{
    /**
     * Register service Enqueue
     *
     * @return void
     */
    public function register() 
    {
        // enqueue assets
        add_action('admin_enqueue_scripts', array ( $this, 'enqueue'));
    }

    public function enqueue()
    {
        // enqueue style and scripts
        wp_enqueue_style( 'sdstyle', SD_PLUGIN_URL . 'assets/sdstyle.css' );
        wp_enqueue_script( 'sdscript', SD_PLUGIN_URL . 'assets/sdscript.js' );
    }

}