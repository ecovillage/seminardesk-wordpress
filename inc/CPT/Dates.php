<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\CPT;

use Inc\Base\CptController;

class Dates extends CptController
{
    //define parameters of the custom post type
    public function __construct()
    {
        $this->name = 'Date';
        $this->names = 'Dates';
        $this->menu_position = 2;
    }
}