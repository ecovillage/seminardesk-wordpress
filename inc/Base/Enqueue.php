<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

use Inc\Api\Callbacks\BlockCallbacks;

class Enqueue
{
    /**
     * Register service Enqueue
     *
     * @return void
     */
    public function register() 
    {
        // TODO: choose appropriated hook to register custom blocks
        add_action('init', array ( $this, 'enqueue_block_assets'));
        // add_action('enqueue_block_editor_assets', array ( $this, 'enqueue_block_assets'));
        add_action('admin_enqueue_scripts', array ( $this, 'enqueue_admin_assets'));
    }

    public function enqueue_block_assets()
    {
        wp_register_script( 'customBlock', SD_PLUGIN_URL . 'assets/CustomBlocks.js', array('wp-blocks', 'wp-i18n', 'wp-editor') );
        // wp_enqueue_script( 'customBlock', SD_PLUGIN_URL . 'assets/CustomBlocks.js', array('wp-blocks', 'wp-i18n', 'wp-editor') );
        $block = new BlockCallbacks;
        register_block_type( 'seminardesk/test-block' , [
            'editor_script' => 'customBlock', // enqueue registered script
            'render_callback' => array( $block, 'block_render_callback'),
        ]);
    }

    public function enqueue_admin_assets()
    {
        wp_enqueue_style( 'sdstyle', SD_PLUGIN_URL . 'assets/sdstyle.css' );
        wp_enqueue_script( 'sdscript', SD_PLUGIN_URL . 'assets/sdscript.js' );
    }

    public function test_callback($params)
    {
        echo 'test';
        return '<h3>Test Block</h3>';
    }
}