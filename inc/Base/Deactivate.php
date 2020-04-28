<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

class Deactivate
{
    /**
     * code that runs during plugin deactivation
     *
     * @return void
     */
     public static function deactivate() {
         flush_rewrite_rules();
     }
 }  