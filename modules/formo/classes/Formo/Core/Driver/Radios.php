<?php defined('SYSPATH') or die('No direct script access.');

class Formo_Core_Driver_Radios extends Formo_Driver {

	public static function get_label( array $array)
	{
		return;
	}

	public static function get_opts( array $array)
	{
		$field = $array['field'];

		$opts_array = array();
		foreach ($field->get('opts', array()) as $key => $value)
		{
			$opts_array[] = '<input type="radio" name="'.$field->name().'" value="'.$key.'" />';
		}

		return $opts_array;
	}

	public static function get_opts_template( array $array)
	{
		return 'opts/radios_template';
	}

	public static function get_title( array $array)
	{
		$field = $array['field'];

		$label = $field->get('label');

		return ($label !== Formo::NOTSET)
			? $label
			: $field->alias();
	}

}