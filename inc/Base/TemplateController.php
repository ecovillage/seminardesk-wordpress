<?php

/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

class TemplateController
{
    public function register()
    {
        // register a custom templates
        add_filter('single_template', array( $this, 'custom_template_post' ));
        add_filter('taxonomy_template', array( $this, 'custom_template_taxonomy' ));
        add_filter('request', array( $this, 'modify_request_schedule' ));
        add_filter('template_include', array( $this, 'custom_template_schedule'));
    }

    public function modify_request_schedule( $vars )
    {
        if ( isset($vars['name']) && $vars['name'] === 'schedule' ){
            $vars += [ 'upcoming' => true ];
        }
        if ( isset($vars['dates']) && $vars['dates'] === 'past' ){
            $vars += [ 'past' => true ];
        } 
        if ( isset($vars['dates']) && $vars['dates'] === 'upcoming' ){
            $vars += [ 'upcoming' => true ];
        } 
        return $vars;
    }

    public function custom_template_schedule( $template )
    {
        if ( get_query_var('upcoming') == true) {
            if ( file_exists(SD_PLUGIN_PATH . 'templates/taxonomy-dates-upcoming.php') ){
                return SD_PLUGIN_PATH . 'templates/taxonomy-dates-upcoming.php';
            }
        }
        if ( get_query_var('past') == true ) {
            if ( file_exists(SD_PLUGIN_PATH . 'templates/taxonomy-dates-past.php') ){
                return SD_PLUGIN_PATH . 'templates/taxonomy-dates-past.php';
            }
        }
        return $template;
    }
    
    public function custom_template_post( $template )
    {
        $post_type = get_post_type();

        // checks for custom template by given (custom) post type if $single not defined
        if ( empty( $template ) && strpos($post_type, 'sd_' ) !== false)
        {
            if ( file_exists( SD_PLUGIN_PATH . 'templates/single-' . $post_type . '.php' ) ) {
                return SD_PLUGIN_PATH . 'templates/single-' . $post_type . '.php';
            }
            return SD_PLUGIN_PATH . 'templates/singular.php';
        }
        return $template;
    }

    public function custom_template_taxonomy( $template )
    {
        // global $wp_query;
        // if ( empty($taxonomy) && $wp_query->queried_object->taxonomy === 'dates'){
        if ( empty($template) && is_tax()){
            if (file_exists(SD_PLUGIN_PATH . 'templates/taxonomy-' . get_query_var( 'taxonomy' ) . '-' . get_query_var( 'dates' ) .  '.php')){
                return SD_PLUGIN_PATH . 'templates/taxonomy-' . get_query_var( 'taxonomy' ) . '-' . get_query_var( 'dates' ) .  '.php';
            }
            if ( file_exists(SD_PLUGIN_PATH . 'templates/taxonomy-' . get_query_var( 'taxonomy' ) . '.php')){
                return SD_PLUGIN_PATH . 'templates/taxonomy-' . get_query_var( 'taxonomy' ) . '.php';
            }
            // return SD_PLUGIN_PATH . 'templates/taxonomy.php';
        }
        return $template;
    }

}