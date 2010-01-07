<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Form helper class.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class form_Core {

	/**
	 * Generates an opening HTML form tag.
	 *
	 * @param   string  form action attribute
	 * @param   array   extra attributes
	 * @param   array   hidden fields to be created immediately after the form tag
	 * @param   string  non-default protocol, eg: https
	 * @return  string
	 */
	public static function open($action = NULL, $attr = array(), $hidden = NULL, $protocol = NULL)
	{
		// Make sure that the method is always set
		empty($attr['method']) and $attr['method'] = 'post';

		if ($attr['method'] !== 'post' AND $attr['method'] !== 'get')
		{
			// If the method is invalid, use post
			$attr['method'] = 'post';
		}

		if ($action === NULL)
		{
			// Use the current URL as the default action
			$action = url::site(Router::$complete_uri, $protocol);
		}
		elseif (strpos($action, '://') === FALSE)
		{
			// Make the action URI into a URL
			$action = url::site($action, $protocol);
		}

		// Set action
		$attr['action'] = $action;

		// Form opening tag
		$form = '<form'.form::attributes($attr).'>'."\n";

		// Add hidden fields immediate after opening tag
		empty($hidden) or $form .= form::hidden($hidden);

		return $form;
	}

	/**
	 * Generates an opening HTML form tag that can be used for uploading files.
	 *
	 * @param   string  form action attribute
	 * @param   array   extra attributes
	 * @param   array   hidden fields to be created immediately after the form tag
	 * @return  string
	 */
	public static function open_multipart($action = NULL, $attr = array(), $hidden = array())
	{
		// Set multi-part form type
		$attr['enctype'] = 'multipart/form-data';

		return form::open($action, $attr, $hidden);
	}

	/**
	 * Creates a HTML form hidden input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function hidden($data, $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'hidden';

		return form::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form input tag. Defaults to a text type.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function input($data, $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		// Type and value are required attributes
		$data += array
		(
			'type'  => 'text',
			'value' => $value
		);

		return '<input'.form::attributes($data).' '.$extra.' />';
	}

	/**
	 * Creates a HTML form password input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function password($data, $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'password';

		return form::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form upload input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function upload($data, $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'file';

		return form::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form textarea tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @param   boolean       encode existing entities
	 * @return  string
	 */
	public static function textarea($data, $value = '', $extra = '', $double_encode = TRUE)
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		if ( ! isset($data['rows']))
		{
			$data['rows'] = '';
		}

		if ( ! isset($data['cols']))
		{
			$data['cols'] = '';
		}

		// Use the value from $data if possible, or use $value
		$value = isset($data['value']) ? $data['value'] : $value;

		// Value is not part of the attributes
		unset($data['value']);

		return '<textarea'.form::attributes($data, 'textarea').' '.$extra.'>'.htmlspecialchars($value, ENT_QUOTES, Kohana::CHARSET, $double_encode).'</textarea>';
	}

	/**
	 * Creates an HTML form select tag, or "dropdown menu".
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   array         select options, when using a name
	 * @param   string|array  option key(s) that should be selected by default
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function dropdown($data, $options = NULL, $selected = NULL, $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}
		else
		{
			if (isset($data['options']))
			{
				// Use data options
				$options = $data['options'];
			}

			if (isset($data['selected']))
			{
				// Use data selected
				$selected = $data['selected'];
			}
		}

		if (is_array($selected))
		{
			// Multi-select box
			$data['multiple'] = 'multiple';
		}
		else
		{
			// Single selection (but converted to an array)
			$selected = array($selected);
		}

		$input = '<select'.form::attributes($data, 'select').' '.$extra.'>'."\n";
		foreach ((array) $options as $key => $val)
		{
			// Key should always be a string
			$key = (string) $key;

			if (is_array($val))
			{
				$input .= '<optgroup label="'.$key.'">'."\n";
				foreach ($val as $inner_key => $inner_val)
				{
					// Inner key should always be a string
					$inner_key = (string) $inner_key;

					$sel = in_array($inner_key, $selected) ? ' selected="selected"' : '';
					$input .= '<option value="'.$inner_key.'"'.$sel.'>'.htmlspecialchars($inner_val, ENT_QUOTES, Kohana::CHARSET, FALSE).'</option>'."\n";
				}
				$input .= '</optgroup>'."\n";
			}
			else
			{
				$sel = in_array($key, $selected) ? ' selected="selected"' : '';
				$input .= '<option value="'.$key.'"'.$sel.'>'.htmlspecialchars($val, ENT_QUOTES, Kohana::CHARSET, FALSE).'</option>'."\n";
			}
		}
		$input .= '</select>';

		return $input;
	}

	/**
	 * Creates an HTML form checkbox input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   boolean       make the checkbox checked by default
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function checkbox($data, $value = '', $checked = FALSE, $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'checkbox';

		if ($checked == TRUE OR (isset($data['checked']) AND $data['checked'] == TRUE))
		{
			$data['checked'] = 'checked';
		}
		else
		{
			unset($data['checked']);
		}

		return form::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form radio input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   boolean       make the radio selected by default
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function radio($data = '', $value = '', $checked = FALSE, $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'radio';

		if ($checked == TRUE OR (isset($data['checked']) AND $data['checked'] == TRUE))
		{
			$data['checked'] = 'checked';
		}
		else
		{
			unset($data['checked']);
		}

		return form::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form submit input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function submit($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		if (empty($data['name']))
		{
			// Remove the name if it is empty
			unset($data['name']);
		}

		$data['type'] = 'submit';

		return form::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form button input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function button($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		if (empty($data['name']))
		{
			// Remove the name if it is empty
			unset($data['name']);
		}

		if (isset($data['value']) AND empty($value))
		{
			$value = arr::remove('value', $data);
		}

		return '<button'.form::attributes($data, 'button').' '.$extra.'>'.$value.'</button>';
	}

	/**
	 * Creates an HTML form label tag.
	 *
	 * @param   string|array  label "for" name or an array of HTML attributes
	 * @param   string        label text or HTML
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function label($data = '', $text = NULL, $extra = '')
	{
		if ( ! is_array($data))
		{
			if (is_string($data))
			{
				// Specify the input this label is for
				$data = array('for' => $data);
			}
			else
			{
				// No input specified
				$data = array();
			}
		}

		if ($text === NULL AND isset($data['for']))
		{
			// Make the text the human-readable input name
			$text = ucwords(inflector::humanize($data['for']));
		}

		return '<label'.form::attributes($data).' '.$extra.'>'.$text.'</label>';
	}

	/**
	 * Sorts a key/value array of HTML attributes, putting form attributes first,
	 * and returns an attribute string.
	 *
	 * @param   array   HTML attributes array
	 * @return  string
	 */
	public static function attributes($attr, $type = NULL)
	{
		if (empty($attr))
			return '';

		$order = array
		(
			'action',
			'method',
			'type',
			'id',
			'name',
			'value',
			'src',
			'size',
			'maxlength',
			'rows',
			'cols',
			'accept',
			'tabindex',
			'accesskey',
			'align',
			'alt',
			'title',
			'class',
			'style',
			'selected',
			'checked',
			'readonly',
			'disabled'
		);

		$sorted = array();
		foreach ($order as $key)
		{
			if (isset($attr[$key]))
			{
				// Move the attribute to the sorted array
				$sorted[$key] = $attr[$key];

				// Remove the attribute from unsorted array
				unset($attr[$key]);
			}
		}

		// Combine the sorted and unsorted attributes and create an HTML string
		return html::attributes(array_merge($sorted, $attr));
	}

} // End form
