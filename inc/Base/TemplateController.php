<?php

/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

class TemplateController
{
    public function register()
    {
        // register a custom template for custom post type and taxonomy, if exists
        add_filter('single_template', [ $this, 'custom_post_template']);
        add_filter('taxonomy_template', [ $this, 'custom_taxonomy_template']);
    }

    public function custom_post_template( $single )
    {
        // post object for the current (custom) post
        global $post;

        // checks for custom template by given (custom) post type if $single not defined
        if ( empty( $single ) && strpos($post->post_type, 'sd_' ) !== false)
        {
            if ( file_exists( SD_PLUGIN_PATH . 'templates/single-' . $post->post_type . '.php' ) ) {
                return SD_PLUGIN_PATH . 'templates/single-' . $post->post_type . '.php';
            }
            return SD_PLUGIN_PATH . 'templates/singular.php';
        }
        return $single;
    }

    public function custom_taxonomy_template( $taxonomy )
    {
        // global $wp_query;
        // if ( empty($taxonomy) && $wp_query->queried_object->taxonomy === 'dates'){
        if ( empty($taxonomy) && is_tax()){
            if ( file_exists(SD_PLUGIN_PATH . 'templates/taxonomy-' . get_query_var( 'taxonomy' ) . '.php')){
                return SD_PLUGIN_PATH . 'templates/taxonomy-' . get_query_var( 'taxonomy' ) . '.php';
            }
            return SD_PLUGIN_PATH . 'templates/taxonomy.php';
        }
        return $taxonomy;
    }
}