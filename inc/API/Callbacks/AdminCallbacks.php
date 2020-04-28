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
     * Call the cpt admin page for SeminarDesk.
     *
     * @return void
     */
	public function adminEventList()
	{
		return require_once( SD_PLUGIN_PATH . '/templates/eventlist.php' );
	}

	/**
     * Call the cpt admin page for SeminarDesk.
     *
     * @return void
     */
	public function adminCpt()
	{
		return require_once( SD_PLUGIN_PATH . '/templates/cpt.php' );
	}

	/**
     * Call the taxonomy admin page for SeminarDesk.
     *
     * @return void
     */
	public function adminTaxonomy()
	{
		return require_once( SD_PLUGIN_PATH . '/templates/taxonomy.php' );
	}

	/**
     * Call the widget admin page for SeminarDesk.
     *
     * @return void
     */
	public function adminWidget()
	{
		return require_once( SD_PLUGIN_PATH . '/templates/widget.php' );
	}
}