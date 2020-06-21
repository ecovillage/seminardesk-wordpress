<?php

/**
 * @package SeminardeskPlugin
 */

namespace Inc\Controllers;

use Inc\Utils\OptionUtils;

class TemplateController
{
    /**
     * Register templates via controller class
     * 
     * @return void 
     */
    public function register()
    {
        add_action( 'init', array( $this, 'add_endpoint' ) );
        add_filter( 'request', array( $this, 'customize_request' ));
        add_filter( 'single_template', array( $this, 'custom_post_template' ));
        add_filter( 'taxonomy_template', array( $this, 'custom_taxonomy_template' ));
        add_filter( 'template_include', array( $this, 'custom_all_template'));  
        
        add_action( 'template_redirect', array( $this, 'template_redirect' ) );
    }

    public function template_redirect( $template )
    {
        global $wp_query;
    }

    public function add_endpoint()
    {
        $name = 'blub';
        add_rewrite_endpoint( $name, EP_ROOT );
    }

    public function enqueue_taxonomy_assets()
    {
        wp_enqueue_style( 'sd-taxonomy-script', SD_DIR['url'] . 'assets/sd-taxonomy.css' );
        // wp_enqueue_script( 'sd-taxonomy-script', SD_DIR['url'] . 'assets/sd-taxonomy-script.js' );
    
    }
    public function enqueue_single_assets()
    {
        wp_enqueue_style( 'sd-single-style', SD_DIR['url'] . 'assets/sd-single.css' );
        wp_enqueue_script( 'sd-single-script', SD_DIR['url'] . 'assets/sd-single.js', array(), '1.0.0', true );
    }

    public function customize_request( $vars )
    {
        $slug_txn_dates = OptionUtils::get_option_or_default( SD_OPTION['slugs'], SD_TXN['sd_txn_dates']['slug_default'], SD_TXN['sd_txn_dates']['slug_option_key'] );
        $slug_txn_dates_past = OptionUtils::get_option_or_default( SD_OPTION['slugs'], SD_TXN_TERM['past']['slug_default'], SD_TXN_TERM['past']['slug_option_key'] );
        $slug_txn_dates_upcoming = OptionUtils::get_option_or_default( SD_OPTION['slugs'], SD_TXN_TERM['upcoming']['slug_default'], SD_TXN_TERM['upcoming']['slug_option_key'] );

        if ( isset($vars['blub']) ){
            $vars += [ 'upcoming' => true ];
            if ( strpos($vars['blub'], 'page') !== false ){
                $vars += [ 'page' => trim($vars['blub'], 'page/') ];
            }
        }
        // set base taxonomy link to upcoming
        if ( isset($vars['name']) && $vars['name'] === $slug_txn_dates ){
            $vars += [ 'upcoming' => true ];
        }
        // fixing page nav for slug of sd_txn_dates
        if ( isset($vars['sd_txn_dates']) && strpos($vars['sd_txn_dates'], 'page') !== false ){
            $vars += [ 'upcoming' => true ];
            $vars += [ 'page' => trim($vars['sd_txn_dates'], 'page/') ];
        }
        if ( isset($vars['sd_txn_dates']) && $vars['sd_txn_dates'] === $slug_txn_dates_past ){
            $vars += [ 'past' => true ];
        }
        if ( isset($vars['sd_txn_dates']) && $vars['sd_txn_dates'] === $slug_txn_dates_upcoming ){
            $vars += [ 'upcoming' => true ];
        } 
        return $vars;
    }

    public function custom_all_template( $template )
    {
        global $wp_query;
        if ( get_query_var('upcoming') === true) {
            if ( file_exists(SD_DIR['path'] . 'templates/sd_txn_dates-upcoming.php') ){
                return SD_DIR['path'] . 'templates/sd_txn_dates-upcoming.php';
            }
        }
        if ( get_query_var('past') === true ) {
            if ( file_exists(SD_DIR['path'] . 'templates/sd_txn_dates-past.php') ){
                return SD_DIR['path'] . 'templates/sd_txn_dates-past.php';
            }
        }
        return $template;
    }
    
    public function custom_post_template( $template )
    {
        $post_type = get_post_type();
        // checks for custom template by given (custom) post type if $single not defined
        if ( empty( $template ) && strpos($post_type, 'sd_cpt' ) !== false)
        {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_single_assets'));
            if ( file_exists( SD_DIR['path'] . 'templates/' . $post_type . '.php' ) ) {
                return SD_DIR['path'] . 'templates/' . $post_type . '.php';
            }
            if ( file_exists( SD_DIR['path'] . 'templates/sd_cpt.php') ) {
                return SD_DIR['path'] . 'templates/sd_cpt.php';
            }
        }
        return $template;
    }

    public function custom_taxonomy_template( $template )
    {
        if ( empty($template)){
            if ( file_exists(SD_DIR['path'] . 'templates/' . get_query_var( 'taxonomy' ) . '.php')){
                add_action('wp_enqueue_scripts', array($this, 'enqueue_taxonomy_assets'));
                return SD_DIR['path'] . 'templates/' . get_query_var( 'taxonomy' ) . '.php';
            }
            return SD_DIR['path'] . 'templates/sd_txn.php';
        }
        return $template;
    }
}