<?php 
/**
 * @package SeminardeskPlugin
 */
namespace Inc\Api\Callbacks;



class ManagerCallbacks
{
    public function seminardeskOptionsGroup( $input )
	{
		return $input;
	}
    
    public function seminardeskTextExample()
	{
		$value = esc_attr( get_option( 'text_example' ) );
		echo '<input type="text" class="regular-text" name="text_example" value="' . $value . '" placeholder="Write Something Here!">';
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