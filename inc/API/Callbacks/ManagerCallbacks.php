<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Api\Callbacks;

use Inc\CPT;
use Inc\Base\CptController;
use Inc\Base\TaxonomyController;

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

	/**
	 * create CPTs with new slug and rewrite rules 
	 * 
	 * @param mixed $value_old 
	 * @param mixed $value_new 
	 * @return void 
	 */
	public function flushRewriteCpt( $value_old, $value_new )
	{			
        $cpt_ctrl = new CptController(array(
            new CPT\CptEvents(),
            new CPT\CptDates(),
            new CPT\CptFacilitators(),
        ));
        $cpt_ctrl->create_cpts();
		flush_rewrite_rules();
	}

	/**
	 * create taxonomies with new slug and rewrite rules 
	 * 
	 * @param mixed $value_old 
	 * @param mixed $value_new 
	 * @return void 
	 */
	public function flushRewriteTaxonomies( $value_old, $value_new )
	{			
		$txn_ctrl = new TaxonomyController();
        $txn_ctrl->create_taxonomy_dates();
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