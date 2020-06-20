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
         
         // create CPTs, Taxonomies, check Terms and rewrite rules/permalinks for slugs
         $cpt_ctrl = new CptController();
         $cpt_ctrl->create_cpts();
         $txn_ctrl = new TaxonomyController();
         $txn_ctrl->create_taxonomies();
         $txn_ctrl->check_term_exists( 'upcoming', 'sd_txn_dates', 'Upcoming Dates', 'upcoming' );
         $txn_ctrl->check_term_exists( 'past', 'sd_txn_dates', 'Past Dates', 'past' );
         flush_rewrite_rules();
    }
}