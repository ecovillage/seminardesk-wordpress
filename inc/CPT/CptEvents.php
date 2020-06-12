<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\CPT;

use Inc\Base\OptionUtils;

class CptEvents extends CptBaseClass
{
    //define parameters of the custom post type
    public function set_parameters()
    {
        $this->name = 'Event';
        $this->names = 'Events';
        $this->menu_position = 1;
        $this->slug = OptionUtils::get_option_or_default( 'sd_slug_cpt_events', SD_SLUG_CPT_EVENTS );
        $test = 1;
    }
}