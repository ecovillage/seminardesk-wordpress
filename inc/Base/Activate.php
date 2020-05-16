<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

use Inc\CPT;
use Inc\Base\CptController;
final class Activate
{
    /**
     * code that runs during plugin activation
     *
     * @return void
     */
     public static function activate() 
     {
        // create and register CPTs for rewrite rules/permalinks to include CPTs slugs
        $cpt_ctrl = new CptController(array(
            new CPT\CptEvents(),
            new CPT\CptDates(),
            new CPT\CptFacilitators(),
        ));
        $cpt_ctrl->create_cpts();
        flush_rewrite_rules();
    }
}