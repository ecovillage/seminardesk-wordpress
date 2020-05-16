<?php

/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

/**
 * Handles CPTs during init and activation
 */
class CptController
{
    /**
     * List of custom post types
     *
     * @var array all cpt classes inside an array
     */
    public $cpts;

    /**
     * Constructor
     *
     * @param array $cpts all cpt classes inside an array
     */
    public function __construct( $cpts )
    {
        // Store all cpt classes inside an array
        $this->set_cpts( $cpts );
    }

    /**
     * Set list of custom post types
     *
     * @param array $cpts all cpt classes inside an array
     * @return array list of set custom post types
     */
    public function set_cpts( $cpts = array() )
    {
        $this->cpts = $cpts;
        return $this->cpts;
    }

    /**
     * Get list of custom post types
     *
     * @return array
     */
    public function get_cpts()
    {
        return $this->cpts;
    }

    /**
     * Register all listed custom post types
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->cpts as $cpt) {
            if ( method_exists( $cpt, 'register') ) {
                $cpt->register();
            }
        }
    }

    /**
     * Create all listed custom post types and register them
     * 
     * @return void
     */
    public function create_cpts()
    {
        foreach ( $this->cpts as $cpt ){
            if ( method_exists( $cpt, 'create_cpt') ){
                $cpt->create_cpt();
            }
        }
    }
}