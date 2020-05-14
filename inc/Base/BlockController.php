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
        add_action('init', array( $this, 'enqueue_block_assets' ));
        // add_action('enqueue_block_editor_assets', array ( $this, 'enqueue_block_assets'));
        add_filter( 'block_categories', array( $this, 'add_block_categories'));
    }

    public function enqueue_block_assets()
    {
        wp_register_script( 'customBlock', SD_PLUGIN_URL . 'assets/CustomBlocks.js', array('wp-blocks', 'wp-i18n', 'wp-editor', 'wp-api-fetch', 'react') );
        // wp_enqueue_script( 'customBlock', SD_PLUGIN_URL . 'assets/CustomBlocks.js', array('wp-blocks', 'wp-i18n', 'wp-editor') );
        $block = new BlockCallbacks;
        register_block_type( 'seminardesk/test' , [
            'editor_script' => 'customBlock', // enqueue registered script
            'render_callback' => array( $block, 'test'),
        ]);
    }

    public function add_block_categories( $categories )
    {
        // Plugin’s block category title and slug.
        $block_category = array( 
            'title' => esc_html__( 'SeminarDesk', 'text-domain' ), // Required
            'slug'  => 'seminardesk', // Required
            // 'icon'  => 'wordpress', 
         );
        $category_slugs = wp_list_pluck( $categories, 'slug' );
    
        if ( ! in_array( $block_category['slug'], $category_slugs, true ) ) {
            $categories = array_merge(
                $categories,
                array(
                    $block_category,
                )
            );
        }
    
        return $categories;
    }
}