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
		// sd logo used as menu icon
		$sd_icon = 'data:image/svg+xml;base64,' . base64_encode(
			'<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
			width="300.000000pt" height="300.000000pt" viewBox="0 0 300.000000 300.000000"
			preserveAspectRatio="xMidYMid meet">
		
			<g transform="translate(-120.000000,450.000000) scale(0.200000,-0.200000)"
			fill="black" stroke="none">
			<path d="M1768 1768 c-3 -151 -3 -153 -20 -121 -22 40 -71 57 -163 57 -159 0
			-275 -114 -292 -286 l-6 -65 -23 24 c-28 28 -85 53 -176 77 -36 10 -75 26 -87
			37 -38 36 -21 79 31 79 22 0 33 -8 46 -30 l17 -30 103 0 c102 0 103 0 97 23
			-38 126 -164 193 -318 168 -142 -24 -207 -85 -207 -196 0 -57 11 -84 50 -122
			33 -32 66 -47 186 -82 80 -23 97 -37 92 -77 -6 -56 -113 -47 -140 10 -11 25
			-13 26 -115 26 l-103 0 6 -27 c34 -142 212 -216 399 -168 80 21 132 57 154
			106 l18 40 18 -28 c60 -95 120 -127 235 -127 99 0 146 17 175 67 l20 32 3 -42
			3 -43 105 0 104 0 0 425 0 425 -105 0 -104 0 -3 -152z m-68 -223 c48 -25 65
			-68 65 -165 -1 -129 -36 -180 -126 -180 -54 0 -88 22 -113 74 -21 45 -21 153
			1 207 28 70 106 99 173 64z"/>
			<path d="M2085 1276 c-55 -24 -81 -80 -64 -139 27 -97 160 -115 212 -28 34 57
			10 138 -48 165 -40 19 -60 20 -100 2z"/>
			</g>
			</svg>'
		);
		// add SeminarDesk to the Admin pages 
        $this->pages = array(
			array(
				'page_title' 	=> 'SeminarDesk Plugin', 
				'menu_title' 	=> 'SeminarDesk', 
				'capability' 	=> 'manage_options', 
				'menu_slug' 	=> 'seminardesk_plugin', 
				'callback'		=> array( $this->callbacks, 'adminGeneral' ), 
				'icon_url'		=> $sd_icon, // 'dashicons-calendar', 
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
				// 'callback' 	=> array( $this->callbacks_mngr, 'sanitize_slug' ),
			),
			array(
				'option_group' 	=> 'seminardesk_plugin_settings',
				'option_name' 	=> SD_OPTION['debug'],
			'callback' 			=> array( $this->callbacks_mngr, 'sanitize_checkbox' )
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
				'callback'	=> array( $this->callbacks_mngr, 'admin_section_slugs' ),
				'page' 		=> 'seminardesk_plugin'
			),
			array(
				'id' 		=> 'sd_admin_debug',
				'title' 	=> __('Developing', 'seminardesk'),
				'callback' 	=> array( $this->callbacks_mngr, 'admin_section_debug' ),
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
				'callback' 	=> array( $this->callbacks_mngr, 'checkbox_field' ),
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
				'callback' 	=> array( $this->callbacks_mngr, 'text_field' ),
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