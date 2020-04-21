<div class="wrap">
	<h1>SeminarDesk Plugin</h1>
	<?php settings_errors(); ?>

	<form method="post" action="options.php">
		<?php 
			settings_fields( 'seminardesk_options_group' );
			do_settings_sections( 'seminardesk_plugin' );
			submit_button();
		?>
	</form>
</div>