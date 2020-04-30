<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\CPT;

class CptDates extends CptBaseClass
{
    public function set_parameters()
    {
        $this->name = 'Date';
        $this->names = 'Dates';
        $this->menu_position = 2;
    }
}