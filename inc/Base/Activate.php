<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

use Inc\CPT;

final class Activate
{
    /**
     * code that runs during plugin activation
     *
     * @return void
     */
     public static function activate() 
     {
        // Store all cpt classes inside an array
        $cpts = [
            new CPT\Events(),
            new CPT\Dates(),
            new CPT\Facilitators(),
        ];

        foreach ( $cpts as $cpt ){
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