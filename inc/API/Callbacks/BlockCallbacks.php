<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Api\Callbacks;

class BlockCallbacks{

    public function block_render_callback($params)
    {
        return '<h3>Test Block</h3>';
    }
}