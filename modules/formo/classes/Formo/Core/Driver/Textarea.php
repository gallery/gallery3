<?php defined('SYSPATH') or die('No direct script access.');

class Formo_Core_Driver_Textarea extends Formo_Driver {

	public static function get_attr( array $array)
	{
		$field = $array['field'];

		return array
		(
			'name' => $field->name(),
		);
	}

	public static function get_tag()
	{
		return 'textarea';
	}

	public static function open( array $array)
	{
		$str = $array['str'];
		$field = $array['field'];

		return $str.= $field->val();
	}

}