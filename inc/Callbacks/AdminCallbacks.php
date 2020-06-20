<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Callbacks;

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
		return require_once( SD_DIR['path'] . '/templates/admin.php' );
	}

	/**
     * Call the event list admin page for SeminarDesk.
     *
     * @return void
     */
	public function adminEventList()
	{
		return require_once( SD_DIR['path'] . '/templates/eventlist.php' );
	}
}