<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

use Inc\CPT;
use Inc\Base\CptController;
use Inc\Base\TaxonomyController;
final class Activate
{
    /**
     * code that runs during plugin activation
     *
     * @return void
     */
     public static function activate() 
     {
        global $t1;
        // create and register CPTs and Taxonomies for rewrite rules/permalinks to include CPTs slugs
        $cpt_ctrl = new CptController(array(
            new CPT\CptEvents(),
            new CPT\CptDates(),
            new CPT\CptFacilitators(),
        ));
        $cpt_ctrl->create_cpts();
        $txn_ctrl = new TaxonomyController();
        $txn_ctrl->create_taxonomy_dates();
        flush_rewrite_rules();
    }
}