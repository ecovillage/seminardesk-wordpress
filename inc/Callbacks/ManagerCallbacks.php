<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Callbacks;

use Inc\Controllers\CptController;
use Inc\Controllers\TaxonomyController;
use Inc\Utils\OptionUtils;

class ManagerCallbacks
{
	public function textField( $args )
	{
		$name = $args['option']  . '[' . $args['key'] . ']';
		$value = OptionUtils::get_option_or_default( $args['option'], '',  $args['key']);
		echo '<input type="text" class="' . $args['class'] . '" name="' . $name . '" value="' . $value . '" placeholder="' . $args['placeholder'] . '">';
	}

	public function checkboxField( $args )
	{
		$checkbox = get_option( $args['option'] );
		echo '<input type="checkbox" name="' . $args['option'] . '" value="1" class="' . $args['class'] . '" ' . ($checkbox ? 'checked' : '') . '>';
	}

	public function slugSanitize( $input )
	{
		return sanitize_text_field($input);
	}

	/**
	 * create CPTs and TXNs with new slug and rewrite rules 
	 * 
	 * @param mixed $value_old 
	 * @param mixed $value_new 
	 * @return void 
	 */
	public function rewrite_slugs( $value_old, $value_new )
	{			
        $cpt_ctrl = new CptController();
		$cpt_ctrl->create_cpts();
		$txn_ctrl = new TaxonomyController();
		$txn_ctrl->create_taxonomies();
		$txn_ctrl->update_terms_slug();

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