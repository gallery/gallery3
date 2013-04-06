<?php defined('SYSPATH') or die('No direct script access.');

class Formo_Core_Driver_Form extends Formo_Driver {

	public static function get_attr( array $array)
	{
		$field = $array['field'];

		return array
		(
			'method' => ($method = $field->attr('method')) ? $method : 'post',
			'action' => ($action = $field->attr('action')) ? $action : Request::$current->url(),
		);
	}

	public static function get_tag()
	{
		return 'form';
	}

	public static function get_template( array $array)
	{
		$field = $array['field'];

		if ($template = $field->get('template'))
		{
			return $template;
		}

		return 'form_template';
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

	public static function load_config( array $array)
	{
		$field = $array['field'];
		$field->set('config', Kohana::$config->load('formo'));
	}
}