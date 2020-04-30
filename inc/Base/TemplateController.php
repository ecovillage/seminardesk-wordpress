<?php

/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

class TemplateController
{
    public function register()
    {
        // loads custom template for given post, if exists
        add_filter('single_template', [ $this, 'custom_template' ]);
    }

    public function custom_template( $single )
    {
        // TODO: allow individual custom templates for each slug of CPT sd_ ... checks for custom template for current post by given post type and slug
        // post object for the current (custom) post
        global $post;

        // checks for custom template by given (custom) post type if $single not defined
        if ( empty( $single ) && strpos($post->post_type, 'sd_' ) !== false)
        {
            if ( file_exists( SD_PLUGIN_PATH . 'templates/single-' . $post->post_type . '.php' ) ) 
            {
                return SD_PLUGIN_PATH . 'templates/single-' . $post->post_type . '.php' ;
            }
            return SD_PLUGIN_PATH . 'templates/singular.php';
        }
        return $single;
    }
}