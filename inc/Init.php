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

    public function __construct(){
        // register services utilizing the init class
        // $this->register_services();
    }

    /**
     * Store all the classes inside an array
     *
     * @return array    full list of classes
     */
    public static function get_services() 
    {
        return [
            Pages\Admin::class,
            Base\Enqueue::class,
            Base\SettingsLinks::class,
            Base\RestController::class,
            Base\CustomFields::class,
            Base\TemplateHandler::class,
            CPT\CptEvents::class,
            CPT\CptDates::class,
            CPT\CptFacilitators::class,
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
         foreach (self::get_services() as $class) {
             $service = self::instantiate( $class );
             if ( method_exists( $service, 'register') ) {
                 $service->register();
             }
         }
    }

    /**
     * Initialize the class
     *
     * @param class $class      class from the services array
     * @return class instance   new instance of the class
     */
    private static function instantiate( $class ) 
    {
        $service = new $class();
        
        return $service;
    }
 }