<?php

/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

use Inc\Api\Callbacks\BlockCallbacks;

class BlockController
{
    /**
     * Register Bock service
     *
     * @return void
     */
    public function register() 
    {
        // TODO: choose appropriated hook to register custom blocks
        add_action('init', array ( $this, 'enqueue_block_assets'));
        // add_action('enqueue_block_editor_assets', array ( $this, 'enqueue_block_assets'));
    }

    public function enqueue_block_assets()
    {
        wp_register_script( 'customBlock', SD_PLUGIN_URL . 'assets/CustomBlocks.js', array('wp-blocks', 'wp-i18n', 'wp-editor') );
        // wp_enqueue_script( 'customBlock', SD_PLUGIN_URL . 'assets/CustomBlocks.js', array('wp-blocks', 'wp-i18n', 'wp-editor') );
        $block = new BlockCallbacks;
        register_block_type( 'seminardesk/test' , [
            'editor_script' => 'customBlock', // enqueue registered script
            'render_callback' => array( $block, 'test'),
        ]);
    }
}