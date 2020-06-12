<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\CPT;

use Inc\Base\OptionUtils;

class CptFacilitators extends CptBaseClass
{
    //define parameters of the custom post type
    public function set_parameters()
    {
        $this->name = 'Facilitator';
        $this->names = 'Facilitators';
        $this->menu_position = 3;
        $this->slug = OptionUtils::get_option_or_default( 'sd_slug_cpt_facilitators', SD_SLUG_CPT_FACILITATORS );
    }
}