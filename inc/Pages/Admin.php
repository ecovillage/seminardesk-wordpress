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

		add_action( 'update_option_sd_slug_cpt_events', array( $this->callbacks_mngr, 'flushRewriteCpt' ), 10, 2 );
		add_action( 'update_option_sd_slug_cpt_facilitators', array( $this->callbacks_mngr, 'flushRewriteCpt' ), 10, 2 );
		add_action( 'update_option_sd_slug_cpt_dates', array( $this->callbacks_mngr, 'flushRewriteCpt' ), 10, 2 );
		add_action( 'update_option_sd_slug_txn_dates', array( $this->callbacks_mngr, 'flushRewriteTaxonomies' ), 10, 2 );
		add_action( 'update_option_sd_slug_txn_dates_upcoming', array( $this->callbacks_mngr, 'flushRewriteTaxonomies' ), 10, 2 );
		add_action( 'update_option_sd_slug_txn_dates_past', array( $this->callbacks_mngr, 'flushRewriteTaxonomies' ), 10, 2 );
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
				'page_title' => 'Taxonomy for Event Dates', 
				'menu_title' => 'Date Taxonomy', 
				'capability' => 'manage_options', 
				'menu_slug' => 'edit-tags.php?taxonomy=dates', 
				'callback' => NULL,
				'position' => 4,
			),
		);
    }
    
    public function setSettings()
	{
		// TODO: optimize database entries - serialized array to speed up database and not blow it up 
		$args = array(
			array(
				'option_group' => 'seminardesk_plugin_settings',
				'option_name' => 'sd_slug_cpt_events',
				// 'callback' => array( $this->callbacks_mngr, 'slugSanitize' ),
			),
			array(
				'option_group' => 'seminardesk_plugin_settings',
				'option_name' => 'sd_slug_cpt_dates',
			),
			array(
				'option_group' => 'seminardesk_plugin_settings',
				'option_name' => 'sd_slug_cpt_facilitators',
			),
			array(
				'option_group' => 'seminardesk_plugin_settings',
				'option_name' => 'sd_slug_cpt_dates',
			),
			array(
				'option_group' => 'seminardesk_plugin_settings',
				'option_name' => 'sd_slug_txn_dates',
			),
			array(
				'option_group' => 'seminardesk_plugin_settings',
				'option_name' => 'sd_slug_txn_dates_upcoming',
			),
			array(
				'option_group' => 'seminardesk_plugin_settings',
				'option_name' => 'sd_slug_txn_dates_past',
			),
			array(
				'option_group' => 'seminardesk_plugin_settings',
				'option_name' => 'sd_debug',
				'callback' => array( $this->callbacks_mngr, 'checkboxSanitize' )
			),
		);

		$this->settings->setSettings( $args );
	}

	public function setSections()
	{
		$args = array(
			array(
				'id' => 'sd_admin_slugs',
				'title' => __('Slugs', 'seminardesk'),
				'callback' => array( $this->callbacks_mngr, 'adminSectionSlugs' ),
				'page' => 'seminardesk_plugin'
			),
			array(
				'id' => 'sd_admin_debug',
				'title' => __('Developing', 'seminardesk'),
				'callback' => array( $this->callbacks_mngr, 'adminSectionDebug' ),
				'page' => 'seminardesk_plugin'
			),
		);

		$this->settings->setSections( $args );
	}

	public function setFields()
	{
		$args = array(
			array(
				'id' 		=> 'sd_slug_cpt_events',
				'title' 	=> __('CPT Events:', 'seminardesk'),
				'callback' 	=> array( $this->callbacks_mngr, 'textField' ),
				'page' 		=> 'seminardesk_plugin',
				'section' 	=> 'sd_admin_slugs',
				'args' 		=> array(
					'name' 			=> 'sd_slug_cpt_events',
					'class' 		=> 'regular-text',
					'placeholder' 	=> SD_SLUG_CPT_EVENTS,
				)
			),
			array(
				'id' 		=> 'sd_slug_cpt_dates',
				'title' 	=> __('CPT Dates:', 'seminardesk'),
				'callback' 	=> array( $this->callbacks_mngr, 'textField' ),
				'page' 		=> 'seminardesk_plugin',
				'section' 	=> 'sd_admin_slugs',
				'args' 		=> array(
					'name' 			=> 'sd_slug_cpt_dates',
					'class' 		=> 'regular-text',
					'placeholder' 	=> SD_SLUG_CPT_DATES,
				)
			),
			array(
				'id' 		=> 'sd_slug_cpt_facilitators',
				'title' 	=> __('CPT Facilitators:', 'seminardesk'),
				'callback' 	=> array( $this->callbacks_mngr, 'textField' ),
				'page' 		=> 'seminardesk_plugin',
				'section' 	=> 'sd_admin_slugs',
				'args' 		=> array(
					'name' 			=> 'sd_slug_cpt_facilitators',
					'class' 		=> 'regular-text',
					'placeholder' 	=> SD_SLUG_CPT_FACILITATORS,
				)
			),
			array(
				'id' 		=> 'sd_slug_txn_dates',
				'title' 	=> __('Taxonomy Dates:', 'seminardesk'),
				'callback' 	=> array( $this->callbacks_mngr, 'textField' ),
				'page' 		=> 'seminardesk_plugin',
				'section' 	=> 'sd_admin_slugs',
				'args' 		=> array(
					'name' 			=> 'sd_slug_txn_dates',
					'class' 		=> 'regular-text',
					'placeholder' 	=> SD_SLUG_TXN_DATES,
				)
			),
			array(
				'id' 		=> 'sd_slug_txn_dates_upcoming',
				'title' 	=> __('Taxonomy Dates Upcoming:', 'seminardesk'),
				'callback' 	=> array( $this->callbacks_mngr, 'textField' ),
				'page' 		=> 'seminardesk_plugin',
				'section' 	=> 'sd_admin_slugs',
				'args' 		=> array(
					'name' 			=> 'sd_slug_txn_dates_upcoming',
					'class' 		=> 'regular-text',
					'placeholder' 	=> SD_SLUG_TXN_DATES_UPCOMING,
				)
			),
			array(
				'id' 		=> 'sd_slug_txn_dates_past',
				'title' 	=> __('Taxonomy Dates Past:', 'seminardesk'),
				'callback' 	=> array( $this->callbacks_mngr, 'textField' ),
				'page' 		=> 'seminardesk_plugin',
				'section' 	=> 'sd_admin_slugs',
				'args' 		=> array(
					'name' 			=> 'sd_slug_txn_dates_past',
					'class' 		=> 'regular-text',
					'placeholder' 	=> SD_SLUG_TXN_DATES_PAST,
				)
			),
			array(
				'id' => 'sd_debug',
				'title' => __('Debug:', 'seminardesk'),
				'callback' => array( $this->callbacks_mngr, 'checkboxField' ),
				'page' => 'seminardesk_plugin',
				'section' => 'sd_admin_debug',
				'args' => array(
					'name' => 'sd_debug',
					'class' => 'ui-toggle'
				)
			)
		);

		$this->settings->setFields( $args );
	}
}