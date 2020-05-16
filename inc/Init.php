<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc;

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
            new Pages\Admin(),
            new Base\SettingsLinks(),
            new Base\RestController(),
            new Base\Enqueue(),
            new Base\CustomFields(),
            new Base\TemplateController(),
            new Base\BlockController(),
            new Base\CptController(array(
                new CPT\CptEvents(),
                new CPT\CptDates(),
                new CPT\CptFacilitators(),
            )),
            new Base\TaxonomyController(),
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