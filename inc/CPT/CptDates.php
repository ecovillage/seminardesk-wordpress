<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\CPT;

use Inc\Base\OptionUtils;

class CptDates extends CptBaseClass
{
    public function set_parameters()
    {
        $this->name = 'Date';
        $this->names = 'Dates';
        $this->menu_position = 2;
        $this->slug = OptionUtils::get_option_or_default( 'sd_slug_cpt_dates', SD_SLUG_CPT_DATES );
    }
}