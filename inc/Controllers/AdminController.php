<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Controllers;

use Inc\Api\SettingsApi;
use Inc\Callbacks\AdminCallbacks;
use Inc\Callbacks\ManagerCallbacks;
use Inc\Utils\OptionUtils;

/**
 * Define admin pages and sub pages
 */
class AdminController
{

    public $settings;
    
	public $callbacks;
	public $callbacks_mngr;

	public $pages = array();
	public $subpages = array();

    /**
     * Register admin page via controller class
     *
     * @return void
     */
    public function register() 
    {
        $this->settings = new SettingsApi(); 
		$this->callbacks = new AdminCallbacks();
		$this->callbacks_mngr = new ManagerCallbacks();
        
        $this->set_admin_pages();
        $this->set_admin_subpages();
        
        $this->set_settings();
		$this->set_sections();
        $this->set_fields();
		
		// generate admin section for seminardesk plugin
		$this->settings->add_pages( $this->pages )->with_sub_page( 'General' )->add_sub_pages( $this->subpages )->register();

		add_action( 'admin_enqueue_scripts', array ( $this, 'enqueue_assets' ) );

		// add entry to admin submenu of seminardesk
		add_filter( 'parent_file', array( $this, 'set_submenu' ) );

		// rewrite rule for custom slug of CPTs and TXNs, if add or update option
		add_action( 'add_option_' . SD_OPTION['slugs'], array( $this->callbacks_mngr, 'rewrite_slugs' ), 10, 2 );
		add_action( 'update_option_' . SD_OPTION['slugs'], array( $this->callbacks_mngr, 'rewrite_slugs' ), 10, 2 );
	}
	
	/**
	 * enqueue admin assets for seminardesk
	 * 
	 * @return void 
	 */
	public function enqueue_assets()
    {
        wp_enqueue_style( 'sdstyle', SD_DIR['url'] . 'assets/sd-admin-style.css' );
        wp_enqueue_script( 'sdscript', SD_DIR['url'] . 'assets/sd-admin-script.js' );
    }

	/**
	 * Set current menu to seminardesk_plugin to correctly highlight your submenu items with your custom parent menu/page.
	 * 
	 * @param string $parent_file 
	 * @return string 
	 */
	public function set_submenu( $parent_file )
	{
		//global $submenu_file, $current_screen, $pagenow;
		$parent_file = 'seminardesk_plugin';
		return $parent_file;
	}

    /**
     * Add SeminarDesk to the admin pages.
     *
     * @return void
     */
    public function set_admin_pages() {
        // add SeminarDesk to the Admin pages 
        $this->pages = array(
			array(
				'page_title' 	=> 'SeminarDesk Plugin', 
				'menu_title' 	=> 'SeminarDesk', 
				'capability' 	=> 'manage_options', 
				'menu_slug' 	=> 'seminardesk_plugin', 
				'callback'		=> array( $this->callbacks, 'adminGeneral' ), 
				'icon_url'		=> 'dashicons-calendar', 
				'position' 		=> 110
			)
		);
    }

    public function set_admin_subpages()
	{
		if ( OptionUtils::get_option_or_default( SD_OPTION['debug'], false) !== false ){
			$this->subpages = array(
				// array(
				// 	'parent_slug' => 'seminardesk_plugin', 
				// 	'page_title' => 'Show all events in one list', 
				// 	'menu_title' => 'Event List', 
				// 	'capability' => 'manage_options', 
				// 	'menu_slug' => 'seminardesk_event_list', 
				// 	'callback' => array( $this->callbacks, 'adminEventList' ),
				// 	'position' => 2,
				// ),
			);

			foreach ( SD_TXN as $txn => $value ) {
				$this->subpages[] = array(
					'parent_slug' 	=> 'seminardesk_plugin', 
					'page_title' 	=> $value['title'], 
					'menu_title' 	=> $value['title'], 
					'capability' 	=> 'manage_options', 
					'menu_slug' 	=> 'edit-tags.php?taxonomy=' . $txn, 
					'callback' 		=> null,
					'position' 		=> $value['menu_position'],
				);
			}
		}
    }
    
    public function set_settings()
	{
		// TODO: optimize database entries - serialized array to speed up database and not blow it up 

		$args = array(
			array(
				'option_group'	=> 'seminardesk_plugin_settings',
				'option_name' 	=> SD_OPTION['slugs'],
				// 'callback' 	=> array( $this->callbacks_mngr, 'slugSanitize' ),
			),
			array(
				'option_group' 	=> 'seminardesk_plugin_settings',
				'option_name' 	=> SD_OPTION['debug'],
			'callback' 			=> array( $this->callbacks_mngr, 'checkboxSanitize' )
			),

		);

		$this->settings->set_settings( $args );
	}

	public function set_sections()
	{
		$args = array(
			array(
				'id' 		=> 'sd_admin_slugs',
			'title' 		=> __('Slugs', 'seminardesk'),
				'callback'	=> array( $this->callbacks_mngr, 'adminSectionSlugs' ),
				'page' 		=> 'seminardesk_plugin'
			),
			array(
				'id' 		=> 'sd_admin_debug',
				'title' 	=> __('Developing', 'seminardesk'),
				'callback' 	=> array( $this->callbacks_mngr, 'adminSectionDebug' ),
				'page' 		=> 'seminardesk_plugin'
			),
		);

		$this->settings->set_sections( $args );
	}

	public function set_fields()
	{
		$args = array(
			array(
				'id' 		=> SD_OPTION['debug'],
				'title' 	=> __('Debug:', 'seminardesk'),
				'callback' 	=> array( $this->callbacks_mngr, 'checkboxField' ),
				'page' 		=> 'seminardesk_plugin',
				'section' 	=> 'sd_admin_debug',
				'args' 		=> array(
					'option' 	=> SD_OPTION['debug'],
					'class' 	=> 'ui-toggle'
				)
			)
		);

		$types = array_merge(SD_CPT, SD_TXN, SD_TXN_TERM);
		foreach ( $types as $type ){
			$args[] = array(
				'id' 		=> $type['slug_option_key'],
				'title' 	=> __( $type['title'] . ':', 'seminardesk'),
				'callback' 	=> array( $this->callbacks_mngr, 'textField' ),
				'page' 		=> 'seminardesk_plugin',
				'section' 	=> 'sd_admin_slugs',
				'args' 		=> array(
					'option'		=> SD_OPTION['slugs'],
					'key' 			=> $type['slug_option_key'],
					'class' 		=> 'regular-text',
					'placeholder' 	=> $type['slug_default'],
				)
			);
		}

		$this->settings->set_fields( $args );
	}
}