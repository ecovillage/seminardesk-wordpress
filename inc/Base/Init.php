<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

use Inc\Controllers;

// thread class es as service
/**
 * Init Plugin - plugin classes treated as service and register them
 */
final class Init
{
    /**
     * Store all the service classes inside an array
     *
     * @return array    full list of classes as a service
     */
    public static function get_services() 
    {
        return [
            new \Inc\Controllers\AdminController(),
            new \Inc\Controllers\SettingsLinksController(),
            new \Inc\Controllers\RestController(),
            new \Inc\Controllers\EnqueueController(),
            new \Inc\Controllers\CustomFieldsController(),
            new \Inc\Controllers\TemplateController(),
            new \Inc\Controllers\BlockController(),
            new \Inc\Controllers\CptController(),
            new \Inc\Controllers\TaxonomyController(),
        ];
    }

    /**
     * Loop through the classes, initialize them 
     * and call the register() method if it exists
     *
     * @return void
     */
    public static function register_services() 
    {
        foreach (self::get_services() as $service) {
            if ( method_exists( $service, 'register') ) {
                $service->register();
            }
        }
    }
 }