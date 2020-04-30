<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\CPT;

class CptEvents extends CptBaseClass
{
    //define parameters of the custom post type
    public function set_parameters()
    {
        $this->name = 'Event';
        $this->names = 'Events';
        $this->menu_position = 1;
    }
}