<?php defined('SYSPATH') or die('No direct script access.');

class Formo_Core_Driver_Checkbox extends Formo_Driver {

	public static function can_be_empty()
	{
		return TRUE;
	}

	public static function get_attr( array $array)
	{
		$field = $array['field'];

		$array = array
		(
			'type' => 'checkbox',
			'value' => 1,
			'name' => $field->name(),
		);

		if ($field->val() === TRUE)
		{
			$array += array('checked' => 'checked');
		}

		return $array;
	}

	public static function get_template( array $array)
	{
		$field = $array['field'];

		if ($template = $field->get('template'))
		{
			return $template;
		}

		return 'checkbox_template';
	}

	public static function get_tag()
	{
		return 'input';
	}

	public static function new_val( array $array)
	{
		$new_val = $array['new_val'];

		return (bool) $new_val;
	}

}