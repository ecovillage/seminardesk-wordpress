<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Base;

use WP_Error;

class Err
{
    /**
     * Undocumented function
     *
     * @param string $action
     * @return void
     */
    public static function not_implemented( $action )
    {
        return new WP_Error('not_implemented', $action . ' method not implemented yet', array('status' => 404));
    }

    /**
     * Undocumented function
     *
     * @param int $post_id
     * @return void
     */
    public static function not_found($post_id)
    {
        return new WP_Error('not_found' ,'associated post with the ID ' . $post_id . ' does not exist', 404);
    }

    /**
     * null         default error message \
     * $post_id     error message including the post id \
     * $message     custom error message 
     *
     * @param string $post_id
     * @param string $message
     * @return void
     */
    public static function no_post( $post_id = null, $message = null )
    {
        if ( is_string($post_id)){
            return new WP_Error('no_post', 'Post ID ' . $post_id . ' does not exists', array('status' => 404));
        }

        if ( is_string($message)){
            return new WP_Error('no_post', $message, array('status' => 404));
        }

        return new WP_Error('no_post', 'Post ID does not exists', array('status' => 404));
    }
}