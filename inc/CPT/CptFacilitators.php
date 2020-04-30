<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\CPT;

class CptFacilitators extends CptBaseClass
{
    //define parameters of the custom post type
    public function set_parameters()
    {
        $this->name = 'Facilitator';
        $this->names = 'Facilitators';
        $this->menu_position = 3;
    }
}