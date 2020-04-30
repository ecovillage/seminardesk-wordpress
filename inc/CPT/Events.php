<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\CPT;

use Inc\Base\CptController;

// TODO: create modular cpt manager class (CPT API) to handel different CPTs (event, calender, facilitators)
// TODO: implement and utilize shortcode [] to generate posts

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