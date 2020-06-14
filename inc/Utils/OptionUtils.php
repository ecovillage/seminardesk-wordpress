<?php
/**
 * 
 * @package SeminardeskPlugin
 */

namespace Inc\Utils;

/**
 * Set of utilities to extend the option API
 */
class OptionUtils
{
    /**
     * Get slug from the option or use default, if option empty or does not exist
     * 
     * @param string $option 
     * @param string $default 
     * @return string 
     */
    public static function get_option_or_default( $option, $default ) {
        $test = get_option( $option );
        if ( get_option( $option ) ){
            return get_option( $option );
        }
        return $default;
    }
}