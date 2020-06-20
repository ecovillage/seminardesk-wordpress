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
     * Get option (also serialized) or use default, if option empty or does not exist
     * 
     * @param string $option 
     * @param string $default value returned if option is not
     * @param string $key used to get value of a serialized option
     * @return string 
     */
    public static function get_option_or_default( $option, $default = '', $key = null ) {
        if ( !empty(get_option( $option )) ){
            if ( !empty($key) ) {
                $value = !empty(get_option( $option )[$key]) ? get_option( $option )[$key] : $default;
            } else {
                $value = get_option( $option );
            } 
        } else {
            $value = $default;
        }
        return $value;
    }
}