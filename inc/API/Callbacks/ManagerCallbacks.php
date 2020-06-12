<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Api\Callbacks;

class ManagerCallbacks
{
	public function textField( $args )
	{
		$value = esc_attr( get_option( $args['name'] ) );
		echo '<input type="text" class="' . $args['class'] . '" name="' . $args['name'] . '" value="' . $value . '" placeholder="' . $args['placeholder'] . '">';
	}

	public function checkboxField( $args )
	{
		$checkbox = get_option( $args['name'] );
		echo '<input type="checkbox" name="' . $args['name'] . '" value="1" class="' . $args['class'] . '" ' . ($checkbox ? 'checked' : '') . '>';
	}

	public function slugSanitize( $input )
	{
		return sanitize_text_field($input);
	}

	public function flushRewriteRules( $value_old, $value_new )
	{			
		$test = get_option('sd_slug_cpt_events');
		flush_rewrite_rules();
	}

    public function checkboxSanitize( $input )
	{
		// return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
		return ( isset($input) ? true : false );
	}

	public function adminSectionSlugs()
	{
		_e('Customize the slugs of this plugin. Use with caution, might have unintended effects on a deployed WordPress instance.', 'seminardesk');
	}

	public function adminSectionDebug()
	{
		_e('Manage the settings for development.', 'seminardesk');
	
	}
}