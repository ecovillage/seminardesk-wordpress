<div class="wrap">
    <h1>SeminarDesk Plugin</h1>
    <?php settings_errors(); ?>

    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-1">Settings</a></li>
        <li><a href="#tab-2">Update</a></li>
        <li><a href="#tab-3">About</a></li>
    </ul>

    <div class="tab-content">

        <div id="tab-1" class="tab-pane active">
            <form method="post" action="options.php">
                <?php
                settings_fields('seminardesk_plugin_settings');
                do_settings_sections('seminardesk_plugin');
                submit_button(__('Save Settings', 'seminardesk'));
                ?>
            </form>
        </div>

        <div id="tab-2" class="tab-pane">
            <h3>Manual Update</h3>
            <p class="regular-text">Automatic Update of this Plugin from the WordPress Dashboard is not available yet. Plugin updates are provided by SeminarDesk in a .zip format and need to be manually installed via SFTP or FTP.</p>
        </div>

        <div id="tab-3" class="tab-pane">
            <h3>Contacts</h3>
            <p class='regular-text'></p>
        </div>

    </div>
</div>