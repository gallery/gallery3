<?php defined('SYSPATH') or die('No direct script access.');

class Formo_Core_Driver_Datalist extends Formo_Driver {

	public static function close( array $array)
	{
		return '</datalist>';
	}

	public static function get_attr( array $array)
	{
		$field = $array['field'];

		return array
		(
			'name' => $field->name(),
			'type' => 'text',
			'list' => $field->attr('id').'_list',
			'value' => $field->val(),
		);
	}

	public static function get_opts( array $array)
	{
		$field = $array['field'];

		$opts_array = array();

		if ($field->get('blank') === TRUE)
		{
			$opts_array[] = '<option></option>';
		}

		foreach ($field->get('opts', array()) as $key => $value)
		{
			$selected = ($value == $field->val())
				? ' selected="selected"'
				: NULL;

			$opts_array[] = '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
		}

		return $opts_array;
	}

	public static function get_opts_template( array $array)
	{
		return 'opts/select_template';
	}

	public static function get_tag()
	{
		return 'input';
	}

	public static function open( array $array)
	{
		$str = $array['str'];
		$field = $array['field'];

		return $str.= '><datalist id="'.$field->attr('id').'_list">';
	}

}