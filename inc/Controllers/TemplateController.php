<?php

/**
 * @package SeminardeskPlugin
 */

namespace Inc\Controllers;

use Inc\Utils\OptionUtils;
use Inc\Utils\TemplateUtils;

class TemplateController
{

    public $assets = array(
        'style'  => null,
        'script' => null,
    );

    /**
     * Register templates via controller class
     * 
     * @return void 
     */
    public function register()
    {
        // add_action( 'init', array( $this, 'add_endpoint' ) );
        // add_action( 'template_redirect', array( $this, 'template_redirect' ) );
        add_filter( 'request', array( $this, 'customize_request' ));
        add_filter( 'template_include', array( $this, 'custom_all_template'));  
    }

    public function template_redirect( $template )
    {
        // currently not used amd action deactivated
    }

    /**
     * add rewrite rule for custom endpoints
     * can not override existing endpoints
     * @return void 
     */
    public function add_endpoint()
    {
        // currently not used and action deactivated
        $name = 'blub';
        add_rewrite_endpoint( $name, EP_ROOT );
    }

    /**
     * enqueue assets (style, script)
     * 
     * @param string|null $style filename of the style
     * @param string|null $script filename of the script
     * @param string $url base url of the assets
     * @return void 
     */
    public function enqueue_assets( $style = null, $script = null, $url = SD_DIR['url'] . 'assets/' )
    {
        if ( !empty( $style ) )
        {
            $exists = TemplateUtils::url_exists( $url . $style );
            if ( $exists ){
                wp_register_style( $style, $url . $style );
                wp_enqueue_style( $style );
            }
        }
        if ( !empty( $script ) )
        {
            $exists = TemplateUtils::url_exists( $url . $script );
            if ( $exists ){
                wp_register_script( $script, $url . $script, array(), '1.0.0', true );
                wp_enqueue_script( $script );
            }
        }
    }

    /**
     * set template file and enqueue its assets
     * 
     * @param array $templates list of template name (without extension) sorted by priority
     * @param string $dir directory of template files
     * @return string template path or empty string if not exists
     */
    public function set_template_enqueue_assets( $templates, $dir = SD_DIR['path'] . 'templates/' )
    {
        foreach ( $templates as $template ){
            $template_path = $dir . $template . '.php';
            if ( file_exists( $template_path ) ){
                $style = $template . '.css';
                $script = $template . '.js';
                $this->enqueue_assets( $style, $script);
                return $template_path;
            }
        }
        return '';
    }

    public function customize_request( $vars )
    {
        $slug_txn_dates = OptionUtils::get_option_or_default( SD_OPTION['slugs'], SD_TXN['sd_txn_dates']['slug_default'], SD_TXN['sd_txn_dates']['slug_option_key'] );
        $slug_txn_dates_past = OptionUtils::get_option_or_default( SD_OPTION['slugs'], SD_TXN_TERM['past']['slug_default'], SD_TXN_TERM['past']['slug_option_key'] );
        $slug_txn_dates_upcoming = OptionUtils::get_option_or_default( SD_OPTION['slugs'], SD_TXN_TERM['upcoming']['slug_default'], SD_TXN_TERM['upcoming']['slug_option_key'] );

        // set custom endpoint to upcoming
        // if ( isset($vars['blub']) ){
        //     $vars += [ 'upcoming' => true ];
        //     if ( strpos($vars['blub'], 'page') !== false ){
        //         $vars += [ 'page' => trim($vars['blub'], 'page/') ];
        //     }
        // }
        // set base taxonomy link to upcoming4
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
        // term upcoming
        if ( get_query_var('upcoming') === true) {
            $templates = array( 
                'sd_txn_dates-upcoming-custom',
                'sd_txn_dates-upcoming',
                'sd_txn', // fallback template
            );
            return $this->set_template_enqueue_assets( $templates );
        }
        // term past
        if ( get_query_var('past') === true ) {
            $templates = array( 
                'sd_txn_dates-past-custom',
                'sd_txn_dates-past',
                'sd_txn', // fallback template
            );
            return $this->set_template_enqueue_assets( $templates );
        }
        // taxonomies
        if ( is_tax() === true ){
            // all other terms of taxono
            $templates = array( 
                get_query_var( 'taxonomy' ) . '-custom',
                get_query_var( 'taxonomy' ),
                'sd_txn', // fallback template
            );
            return $this->set_template_enqueue_assets( $templates );
        };
        // custom post types
        $test = is_single();
        if ( is_single() === true) {
            $post_type = get_post_type();
            // checks for custom template by given (custom) post type if $single not defined
            if ( strpos($post_type, 'sd_cpt' ) !== false)
            {
                $templates = array(
                    $post_type . '-custom',
                    $post_type,
                    'sd_cpt'
                );
                return $this->set_template_enqueue_assets( $templates );
            }
        }
        return $template;
    }
}