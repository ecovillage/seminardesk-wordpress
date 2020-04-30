<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Pages;

use Inc\Api\SettingsApi;
use Inc\Api\Callbacks\AdminCallbacks;
use Inc\Api\Callbacks\ManagerCallbacks;

/**
 * Define admin pages and sub pages
 */
class Admin
{

    public $settings;
    
	public $callbacks;
	public $callbacks_mngr;

	public $pages = array();
	public $subpages = array();

    /**
     * Register service admin pages
     *
     * @return void
     */
    public function register() 
    {
        $this->settings = new SettingsApi(); 
		$this->callbacks = new AdminCallbacks();
		$this->callbacks_mngr = new ManagerCallbacks();
        
        $this->setAdminPages();
        $this->setAdminSubpages();
        
        $this->setSettings();
		$this->setSections();
        $this->setFields();
        
		$this->settings->addPages( $this->pages )->withSubPage( 'General' )->addSubPages( $this->subpages )->register();
		// $this->settings->addPages( $this->pages )->withSubPage( 'General' )->register();
    }

    /**
     * Add SeminarDesk to the admin pages.
     *
     * @return void
     */
    public function setAdminPages() {
        // add SeminarDesk to the Admin pages 
        $this->pages = array(
			array(
				'page_title' => 'SeminarDesk Plugin', 
				'menu_title' => 'SeminarDesk', 
				'capability' => 'manage_options', 
				'menu_slug' => 'seminardesk_plugin', 
				'callback' => array( $this->callbacks, 'adminGeneral' ), 
				'icon_url' => 'dashicons-calendar', 
				'position' => 110
			)
		);
    }

    public function setAdminSubpages()
	{
		$this->subpages = array(
			array(
				'parent_slug' => 'seminardesk_plugin', 
				'page_title' => 'Show all events in one list', 
				'menu_title' => 'Event List', 
				'capability' => 'manage_options', 
				'menu_slug' => 'seminardesk_event_list', 
				'callback' => array( $this->callbacks, 'adminEventList' ),
				'position' => 2,
			),
			array(
				'parent_slug' => 'seminardesk_plugin', 
				'page_title' => 'Custom Post Types', 
				'menu_title' => 'CPT', 
				'capability' => 'manage_options', 
				'menu_slug' => 'seminardesk_cpt', 
				'callback' => array( $this->callbacks, 'adminCpt' ),
				'position' => 11,
			),
			array(
				'parent_slug' => 'seminardesk_plugin', 
				'page_title' => 'Custom Taxonomies', 
				'menu_title' => 'Taxonomies', 
				'capability' => 'manage_options', 
				'menu_slug' => 'seminardesk_taxonomies', 
				'callback' => array( $this->callbacks, 'adminTaxonomy' ),
				'position' => 12,
			),
			array(
				'parent_slug' => 'seminardesk_plugin', 
				'page_title' => 'Custom Widgets', 
				'menu_title' => 'Widgets', 
				'capability' => 'manage_options', 
				'menu_slug' => 'seminardesk_widgets', 
				'callback' => array( $this->callbacks, 'adminWidget' ),
				'position' => 13,
			)
		);
    }
    
    public function setSettings()
	{
		// TODO: optimize database entries - serialized array to speed up database and not blow it up 
		$args = array(
			array(
				'option_group' => 'seminardesk_plugin_settings',
				'option_name' => 'text_example',
				'callback' => array( $this->callbacks_mngr, 'seminardeskOptionsGroup' )
			),
			array(
				'option_group' => 'seminardesk_plugin_settings',
				'option_name' => 'first_name'
			),
			array(
				'option_group' => 'seminardesk_plugin_settings',
				'option_name' => 'cpt_manager',
				'callback' => array( $this->callbacks_mngr, 'checkboxSanitize' )
			),
		);

		$this->settings->setSettings( $args );
	}

	public function setSections()
	{
		$args = array(
			array(
				'id' => 'seminardesk_admin_index',
				'title' => 'Settings',
				'callback' => array( $this->callbacks_mngr, 'seminardeskAdminSection' ),
				'page' => 'seminardesk_plugin'
			)
		);

		$this->settings->setSections( $args );
	}

	public function setFields()
	{
		$args = array(
			array(
				'id' => 'text_example',
				'title' => 'Text Example',
				'callback' => array( $this->callbacks_mngr, 'seminardeskTextExample' ),
				'page' => 'seminardesk_plugin',
				'section' => 'seminardesk_admin_index',
				'args' => array(
					'label_for' => 'text_example',
					'class' => 'example-class'
				)
			),
			array(
				'id' => 'first_name',
				'title' => 'First Name',
				'callback' => array( $this->callbacks_mngr, 'seminardeskFirstName' ),
				'page' => 'seminardesk_plugin',
				'section' => 'seminardesk_admin_index',
				'args' => array(
					'label_for' => 'first_name',
					'class' => 'example-class'
				)
			),
			array(
				'id' => 'cpt_manager',
				'title' => 'Activate CPT Manager',
				'callback' => array( $this->callbacks_mngr, 'checkboxField' ),
				'page' => 'seminardesk_plugin',
				'section' => 'seminardesk_admin_index',
				'args' => array(
					'label_for' => 'cpt_manager',
					'class' => 'ui-toggle'
				)
			)
		);

		$this->settings->setFields( $args );
	}
}