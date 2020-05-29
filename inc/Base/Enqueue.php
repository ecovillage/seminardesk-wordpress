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
        add_action('admin_enqueue_scripts', array ( $this, 'enqueue_admin_assets'));
    }

    public function enqueue_admin_assets()
    {
        wp_enqueue_style( 'sdstyle', SD_PLUGIN_URL . 'assets/sd-admin-style.css' );
        wp_enqueue_script( 'sdscript', SD_PLUGIN_URL . 'assets/sd-admin-script.js' );
    }
}