=== SeminarDesk for WordPress ===
Requires at least: 5.2
Tested up to: 5.4.2
Requires PHP: 7.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connects SeminarDesk to WordPress.

== Description ==

This plugin allows you to connect [SeminarDesk](https://www.seminardesk.com) to your WordPress site in order to automatically create posts for events, dates and facilitators when those items are created or updated via SeminarDesk.

== Installation ==

The plugin can be installed via ZIP file, the most recent version is provided [here](https://www.seminardesk.com/wordpress-plugin).

If you have direct access to your hosting environment, you can also clone the [plugin's Git repository](https://bitbucket.org/seminardesk/seminardesk-wordpress/branch/master) and checkout the master branch.

== Setup ==

The plugin works by handling the Webhooks that are triggered by SeminarDesk.

You need to complete the following steps in order to create the connection:

At first, create a new WordPress user with "Author" role, you could name it `SeminarDesk`, for example.

Then add the following URL under "Administration > Webhooks", alongside the username and password of the user you created in step 1.

`https://your-wordpress-site.com/wp-json/seminardesk/v1/webhooks`

When adding the webhook URL, you should select "All events" so that all supported item types will be published to your WordPress site.

From now on, SeminarDesk will publish events to the WordPress plugin whenever a new item like event or facilitator is created, updated or deleted. To initially publish all items, you can select "Send all items" from the webhook's action menu.

== Changelog ==

= 1.0.1 =
* Renamed plugin file, added readme file and added Autoload generated sources and dependencies to make plugin installable via Git.

= 1.0.0 =
* Initial release.
