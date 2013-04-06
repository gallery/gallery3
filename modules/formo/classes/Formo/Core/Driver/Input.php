<?php defined('SYSPATH') or die('No direct script access.');

class Formo_Core_Driver_Input extends Formo_Driver {

	public static function get_tag()
	{
		return 'input';
	}

	public static function get_attr( array $array)
	{
		$field = $array['field'];

		$type = static::_get_type($field);

		$val = ($type == 'password')
			? NULL
			: $field->val();

		return array
		(
			'type' => $type,
			'value' => $val,
			'name' => $field->name(),
		);
	}

	public static function get_label( array $array)
	{
		$field = $array['field'];

		if (in_array($field->attr('type'), array('submit', 'hidden')))
		{
			return NULL;
		}
		else
		{
			return parent::get_label($array);
		}
	}

	public static function pre_validate( array $array)
	{
		$field = $array['field'];

		if ($rules = $field->config('input_rules.'.static::_get_type($field)))
		{
			$field->add_rules($rules);
		}
	}

	protected static function _get_type($field)
	{
		return ($type = $field->attr('type'))
			? $type
			: 'text';
	}

}