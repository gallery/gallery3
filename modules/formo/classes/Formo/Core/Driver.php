<?php defined('SYSPATH') or die('No direct script access.');

abstract class Formo_Core_Driver {

	/**
	 * Event that's run directly after a field is added
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return void
	 */
	public static function added( array $array)
	{
		return;
	}

	/**
	 * Return whether a field can post nothing (ie, checkboxes post nothing if not checked)
	 * 
	 * @access public
	 * @static
	 * @return boolean
	 */
	public static function can_be_empty()
	{
		return false;
	}

	/**
	 * Allow for any special closing tags that don't fit the norm (ie, datalist)
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return string
	 */
	public static function close( array $array)
	{
		$str = $array['str'];

		return $str;
	}

	/**
	 * Allow any pre-configured attributes for a field or form
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return array
	 */
	public static function get_attr( array $array)
	{
		return array();
	}

	/**
	 * Return a formatted label
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return string
	 */
	public static function get_label( array $array)
	{
		$field = $array['field'];

		$label = $field->get('label');

		return ($label !== Formo::NOTSET)
			? $label
			: $field->alias();
	}

	/**
	 * Configure and return any option fields (ie select, radios and checkboxes fields)
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return array
	 */
	public static function get_opts( array $array)
	{
		return array();
	}

	/**
	 * Retrieve a template that renders options for a field
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return string
	 */
	public static function get_opts_template( array $array)
	{
		return NULL;
	}

	/**
	 * Event run before validation. Allows pre-configured rules to be added
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return void
	 */
	public static function pre_validate( array $array)
	{
		return;
	}

	/**
	 * Return a field's tag name. Has to be set for every field.
	 * 
	 * @access public
	 * @static
	 * @return string
	 */
	public static function get_tag() {}

	/**
	 * Return a field's template used with $field->render()
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return void
	 */
	public static function get_template( array $array)
	{
		$field = $array['field'];

		if ($template = $field->get('template'))
		{
			return $template;
		}

		return 'field_template';
	}

	/**
	 * Get special title (used mostly for groups of fields like checkboxes and radios)
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return string
	 */
	public static function get_title( array $array)
	{
		return NULL;
	}

	/**
	 * Return a field's value
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return mixed
	 */
	public static function get_val( array $array)
	{
		return $array['val'];
	}

	/**
	 * Find values that will be added to the field's validation object
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return void
	 */
	public static function get_validation_values( array $array)
	{
		$field = $array['field'];

		return array($field->alias() => $field->val());
	}

	/**
	 * Return whether this is usually a parent containing multiple fields
	 * 
	 * @access public
	 * @static
	 * @return boolean
	 */
	public static function is_a_parent()
	{
		return FALSE;
	}

	/**
	 * Return field's open tag
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return string
	 */
	public static function open( array $array)
	{
		$str = $array['str'];

		return $str;
	}

	/**
	 * Driver actually sets a field's value from Formo::load()
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return void
	 */
	public static function load( array $array)
	{
		$val = $array['val'];
		$field = $array['field'];

		$field->val($val);
	}

	/**
	 * Return a field's 'name' attribute
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return name
	 */
	public static function name( array $array)
	{
		$field = $array['field'];
		$use_namespaces = $array['use_namespaces'];

		if ($use_namespaces !== TRUE)
		{
			return $field->alias();
		}

		if ($parent = $field->parent())
		{
			$name = $parent->alias().'['.$field->alias().']';
		}
		else
		{
			$name = $field->alias();
		}

		return $name;
	}

	/**
	 * Take a raw new value in and return what the field should interpret the value to be
	 * 
	 * @access public
	 * @static
	 * @param array $array
	 * @return mixed
	 */
	public static function new_val( array $array)
	{
		$new_val = $array['new_val'];

		return $new_val;
	}

}