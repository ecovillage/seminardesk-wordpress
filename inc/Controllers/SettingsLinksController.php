<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Controllers;

class SettingsLinksController
{
    /**
     * Register SettingsLinks via controller class
     *
     * @return void
     */
    public function register() {
        // add settings link to the plugin menu
        add_filter( "plugin_action_links_" . SD_ENV['base'], array( $this, 'create_settings_link'));
     }

    /**
     * Add custom settings link to plugin menu.
     *
     * @param array  $links
     * @return array $links list of links
     */
    public function create_settings_link( $links ) {
        $settings_link = '<a href="admin.php?page=seminardesk_plugin">Settings</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }
 }