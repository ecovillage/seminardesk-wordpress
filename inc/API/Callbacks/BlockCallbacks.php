<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Api\Callbacks;

class BlockCallbacks{

    public function test($params)
    {
        // $response = wp_remote_get( 'https://schloss-tempelhof.seminardesk.de/API/Events/afd366c32a264cb1b7ef85c0b1895cd0' );
        // $body = json_decode(wp_remote_retrieve_body( $response ));
        // $id = $body->id;
        
        return '<h3>Test Block</h3>';
    }
}