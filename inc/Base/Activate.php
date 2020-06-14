<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

use Inc\Controllers\CptController;
use Inc\Controllers\TaxonomyController;
final class Activate
{
    /**
     * code that runs during plugin activation
     *
     * @return void
     */
     public static function activate() 
     {
        // create CPTs, Taxonomies and rewrite rules/permalinks to include their slugs
        $cpt_ctrl = new CptController();
        $cpt_ctrl->create_cpts();
        $txn_ctrl = new TaxonomyController();
        $txn_ctrl->create_taxonomies();
        flush_rewrite_rules();
    }
}