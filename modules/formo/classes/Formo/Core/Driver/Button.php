<?php defined('SYSPATH') or die('No direct script access.');

class Formo_Core_Driver_Button extends Formo_Driver {

	public static function get_attr( array $array)
	{
		$field = $array['field'];

		return array
		(
			'name' => $field->name(),
			'value' => $field->val(),
		);
	}

	public static function get_label( array $array)
	{
		return NULL;
	}

	public static function get_tag()
	{
		return 'button';
	}

}