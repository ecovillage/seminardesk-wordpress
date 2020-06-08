<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Api\Callbacks;



class ManagerCallbacks
{
	// execute when setting saved
    public function seminardeskOptionsGroup( $input )
	{
		return $input;
	}
    
    public function seminardeskTextExample()
	{
		$value = esc_attr( get_option( 'sd_txn_slug' ) );
		echo '<input type="text" class="regular-text" name="sd_txn_slug" value="' . $value . '" placeholder="schedule">';
	}

	public function seminardeskFirstName()
	{
		$value = esc_attr( get_option( 'first_name' ) );
		echo '<input type="text" class="regular-text" name="first_name" value="' . $value . '" placeholder="Write your First Name">';
	}

    public function checkboxSanitize( $input )
	{
		// return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
		return ( isset($input) ? true : false );
	}

	public function seminardeskAdminSection()
	{
		echo 'Manage the settings of this plugin in this section.';
	}

	public function checkboxField( $args )
	{
		$name = $args['label_for'];
		$classes = $args['class'];
		$checkbox = get_option( $name );
		echo '<input type="checkbox" name="' . $name . '" value="1" class="' . $classes . '" ' . ($checkbox ? 'checked' : '') . '>';
	}
}