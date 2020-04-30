<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\CPT;

use Inc\Base\CptController;

class Facilitators extends CptController
{
    //define parameters of the custom post type
    public function __construct()
    {
        $this->name = 'Facilitator';
        $this->names = 'Facilitators';
        $this->menu_position = 3;
    }
}