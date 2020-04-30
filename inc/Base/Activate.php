<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

use Inc\CPT;

final class Activate
{
    /**
     * Store all cpt classes inside an array
     *
     * @return array    full list of all CPTs
     */
    public static function get_cpts()
    {
        return [
            new CPT\CptEvents(),
            new CPT\CptDates(),
            new CPT\CptFacilitators(),
        ];
    }

    /**
     * code that runs during plugin activation
     *
     * @return void
     */
     public static function activate() 
     {
        // rewrites rules/premalinks on activation to include CPTs and its slugs
        foreach ( self::get_cpts() as $cpt ){
            if ( method_exists( $cpt, 'create_cpt') ){
                $cpt->create_cpt();
            }
        }

        // if ( class_exists( 'Inc\\CPT\\Events' ) ) {
        //     $cpt_events = new CPT\Events();
        //     $cpt_events->create_cpt();
        // }
        // if ( class_exists( 'Inc\\CPT\\Dates' ) ) {
        //     $cpt_dates = new CPT\Dates();
        //     $cpt_dates->create_cpt();
        // }
        // if ( class_exists( 'Inc\\CPT\\Facilitators' ) ) {
        //     $cpt_facilitators = new CPT\Facilitators();
        //     $cpt_facilitators->create_cpt();
        // }

        flush_rewrite_rules();
    }
}