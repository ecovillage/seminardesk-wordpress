<?php
/**
 * @package SeminardeskPlugin
 */

namespace Inc\Base;

class SettingsLinks
{
    /**
     * Register service SettingsLinks
     *
     * @return void
     */
    public function register() {
        // add settings link to the plugin menu
        add_filter( "plugin_action_links_" . SD_PLUGIN, array( $this, 'settings_link'));
     }

    /**
     * Add custom settings link to plugin menu.
     *
     * @param array  $links
     * @return array $links list of links
     */
    public function settings_link( $links ) {
        $settings_link = '<a href="admin.php?page=seminardesk_plugin">Settings</a>';
        array_push( $links, $settings_link );
        return $links;
    }
 }