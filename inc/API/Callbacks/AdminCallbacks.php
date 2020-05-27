<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Api\Callbacks;

/**
 * Callbacks require once content for admin pages
 */
class AdminCallbacks
{
	/**
     * Call the general admin page for SeminarDesk.
     *
     * @return void
     */
	public function adminGeneral()
	{
		return require_once( SD_PLUGIN_PATH . '/templates/admin.php' );
	}

	/**
     * Call the event list admin page for SeminarDesk.
     *
     * @return void
     */
	public function adminEventList()
	{
		return require_once( SD_PLUGIN_PATH . '/templates/eventlist.php' );
	}
}