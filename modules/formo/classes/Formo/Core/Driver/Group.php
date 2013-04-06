<?php defined('SYSPATH') or die('No direct script access.');

class Formo_Core_Driver_Group extends Formo_Driver {

	public static function get_template( array $array)
	{
		$field = $array['field'];

		if ($template = $field->get('template'))
		{
			return $template;
		}

		return 'group_template';
	}

	public static function get_val( array $array)
	{
		$field = $array['field'];
		$val = $array['val'];

		$array = array();
		foreach ($field->as_array() as $alias => $field)
		{
			$array[$alias] = $field->val();
		}

		return $array;
	}

	public static function get_validation_values( array $array)
	{
		$field = $array['field'];

		return array($field->alias() => $field->val());
	}

	public static function is_a_parent()
	{
		return TRUE;
	}

}