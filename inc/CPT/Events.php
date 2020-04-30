<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\CPT;

use Inc\Base\CptController;

class Events extends CptController
{
    //define parameters of the custom post type
    public function __construct()
    {
        $this->name = 'Event';
        $this->names = 'Events';
        $this->menu_position = 1;
    }
}