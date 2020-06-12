<?php

/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

use Inc\Base\OptionUtils;

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

    public function enqueue_taxonomy_assets()
    {
        wp_enqueue_style( 'sd-taxonomy-script', SD_PLUGIN_URL . 'assets/sd-taxonomy.css' );
        // wp_enqueue_script( 'sd-taxonomy-script', SD_PLUGIN_URL . 'assets/sd-taxonomy-script.js' );
    
    }
    public function enqueue_single_assets()
    {
        wp_enqueue_style( 'sd-single-style', SD_PLUGIN_URL . 'assets/sd-single.css' );
        wp_enqueue_script( 'sd-single-script', SD_PLUGIN_URL . 'assets/sd-single.js', array( 'jquery' ), '1.0.0', true );
    }

    public function modify_request_schedule( $vars )
    {
        $slug_txn_dates = OptionUtils::get_option_or_default( 'sd_slug_txn_dates', SD_SLUG_TXN_DATES );
        $slug_txn_dates_upcoming = OptionUtils::get_option_or_default( 'sd_slug_txn_dates_upcoming', SD_SLUG_TXN_DATES_UPCOMING );
        $slug_txn_dates_past = OptionUtils::get_option_or_default( 'sd_slug_txn_dates_past', SD_SLUG_TXN_DATES_PAST );
        if ( isset($vars['name']) && $vars['name'] === $slug_txn_dates ){
            $vars += [ 'upcoming' => true ];
        }
        // fixing page nav for slug of txn dates
        if ( isset($vars['dates']) && strpos($vars['dates'], 'page') !== false ){
            $vars += [ 'upcoming' => true ];
            $vars += [ 'page' => trim($vars['dates'], 'page/') ];
        }
        if ( isset($vars['dates']) && $vars['dates'] === $slug_txn_dates_past ){
            $vars += [ 'past' => true ];
        }
        if ( isset($vars['dates']) && $vars['dates'] === $slug_txn_dates_upcoming ){
            $vars += [ 'upcoming' => true ];
        } 
        return $vars;
    }

    public function custom_template_schedule( $template )
    {
        if ( get_query_var('upcoming') == true) {
            if ( file_exists(SD_PLUGIN_PATH . 'templates/sd-taxonomy-dates-upcoming.php') ){
                return SD_PLUGIN_PATH . 'templates/sd-taxonomy-dates-upcoming.php';
            }
        }
        if ( get_query_var('past') == true ) {
            if ( file_exists(SD_PLUGIN_PATH . 'templates/sd-taxonomy-dates-past.php') ){
                return SD_PLUGIN_PATH . 'templates/sd-taxonomy-dates-past.php';
            }
        }
        return $template;
    }
    
    public function custom_template_post( $template )
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_single_assets'));

        $post_type = get_post_type();
        // checks for custom template by given (custom) post type if $single not defined
        if ( empty( $template ) && strpos($post_type, 'sd_' ) !== false)
        {
            if ( file_exists( SD_PLUGIN_PATH . 'templates/sd-single-' . $post_type . '.php' ) ) {
                return SD_PLUGIN_PATH . 'templates/sd-single-' . $post_type . '.php';
            }
            if ( file_exists( SD_PLUGIN_PATH . 'templates/sd-single.php') ) {
                return SD_PLUGIN_PATH . 'templates/sd-single.php';
            }
        }
        return $template;
    }

    public function custom_template_taxonomy( $template )
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_taxonomy_assets'));
        // global $wp_query;
        // if ( empty($taxonomy) && $wp_query->queried_object->taxonomy === 'dates'){
        if ( empty($template) && is_tax()){
            if (file_exists(SD_PLUGIN_PATH . 'templates/sd-taxonomy-' . get_query_var( 'taxonomy' ) . '-' . get_query_var( 'dates' ) .  '.php')){
                return SD_PLUGIN_PATH . 'templates/sd-taxonomy-' . get_query_var( 'taxonomy' ) . '-' . get_query_var( 'dates' ) .  '.php';
            }
            if ( file_exists(SD_PLUGIN_PATH . 'templates/sd-taxonomy-' . get_query_var( 'taxonomy' ) . '.php')){
                return SD_PLUGIN_PATH . 'templates/sd-taxonomy-' . get_query_var( 'taxonomy' ) . '.php';
            }
            // return SD_PLUGIN_PATH . 'templates/sd-taxonomy.php';
        }
        return $template;
    }
}