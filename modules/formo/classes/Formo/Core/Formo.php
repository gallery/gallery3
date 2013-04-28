<?php defined('SYSPATH') or die('No direct script access.');

class Formo_Core_Formo extends Formo_Innards {

	/**
	 * Helper method to return a form field
	 * 
	 * @access public
	 * @static
	 * @param array $array (default: NULL)
	 * @return Formo obj
	 */
	public static function form( array $array = NULL)
	{
		if (empty($array['alias']))
		{
			if ($array === NULL)
			{
				$array = array();
			}

			// Set the default alias
			$array += array('alias' => 'formo');
		}

		if (empty($array['driver']))
		{
			$array['driver'] = 'form';
		}

		return new Formo($array);
	}

	/**
	 * Simple factory method
	 * 
	 * @access public
	 * @static
	 * @param array $array (default: NULL)
	 * @return Formo obj
	 */
	public static function factory( array $array = NULL)
	{
		return new Formo($array);
	}

	/**
	 * Set up new field
	 * 
	 * @access public
	 * @param array $array (default: NULL)
	 * @return Formo obj
	 */
	public function __construct( array $array = NULL)
	{
		if (Kohana::$profiling === TRUE)
		{
			// Start a new benchmark
			$benchmark = Profiler::start('Formo', 'create objects');
		}

		$array = $this->_resolve_construct_aliases($array);

		foreach ($array as $key => $value)
		{
			$this->set($key, $value);
		}

		if (isset($benchmark))
		{
			// Stop benchmarking
			Profiler::stop($benchmark);
		}
	}

	/**
	 * Return a field
	 * Retuns NULL if the field doesn't exist
	 * 
	 * @access public
	 * @param mixed $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->find($key, TRUE);
	}

	/**
	 * Convert just the input to html
	 * 
	 * @access public
	 * @return string
	 */
	public function __invoke()
	{
		if ($this->get('render') === false)
		{
			return NULL;
		}

		$str = $this->open();
		$str.= $this->html();
		$str.= $this->render_opts();

		foreach ($this->_fields as $field)
		{
			if ($field->get('render') === TRUE)
			{
				$str.= $field->render();
			}
		}
		
		$str.= $this->close();

		return $str;
	}

	/**
	 * Determine whether a field exists
	 * 
	 * @access public
	 * @param mixed $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		return (bool) $this->find($key, TRUE);
	}

	/**
	 * Add a field to another field. Only works with Formo objects
	 * 
	 * @access public
	 * @param mixed $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		if ( ! $value instanceof Formo)
		{
			throw new Kohana_Exception('New fields must be instances of Formo, :param given instead', array(':param' => print_r($value, 1)));
		}

		$value->set('alias', $key);

		$this->add($value);
	}

	/**
	 * Render the Formo object
	 * 
	 * @access public
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Add a field or subform to a field or form
	 * 
	 * @access public
	 * @param mixed $alias
	 * @param mixed $driver (default: NULL)
	 * @param mixed $value (default: NULL)
	 * @param array $opts (default: NULL)
	 * @return void
	 */
	public function add($alias, $driver = NULL, $value = NULL, array $opts = NULL)
	{
		$args = $alias;

		if ( ! is_array($args))
		{
			// Treat args the same as a plain array
			$args = func_get_args();
		}

		if (Arr::get($args, 0) instanceof Formo)
		{
			$form = $args[0];

			// Allow a straight Formo object to be added
			$this->_fields[] = $form;

			if ( ! empty($args[1]))
			{
				$form->set($args[1]);
			}

			if ($form->get('driver') === 'form')
			{
				// Convert form driver to group driver since form should only be used once
				$form->set('driver', 'group');
			}

			// Always set the parent object
			$form->set('parent', $this);

			return $this;
		}

		$args['parent'] = $this;

		// Create the field object
		$field = Formo::factory($args);
		$this->_fields[] = $field;

		// Run the 'added' method in field's driver
		$field->driver('added');

		return $this;
	}

	/**
	 * Add a class tag to the field
	 * 
	 * @access public
	 * @param mixed $class
	 * @return Formo obj
	 */
	public function add_class($class)
	{
		// Break current classes into an array
		$all_classes = explode(' ', \Arr::get($this->_attr, 'class'));

		// Use an array for new classes too
		$classes = ( ! is_array($class))
			? explode(' ', $class)
			: $class;

		foreach ($classes as $_class)
		{
			if ( ! in_array($_class, $all_classes))
			{
				// Add the new class if it isn't already there
				$all_classes[] = $_class;
			}
		}


		$this->_attr = \Arr::merge($this->_attr, array('class' => implode(' ', $all_classes)));

		return $this;
	}

	/**
	 * Add a single rule
	 * 
	 * @access public
	 * @param mixed $rule   (as array like Validation::rules() or string like Validation::rule())
	 * @param array $params (only used if $rule isn't an array, default: NULL)
	 * @return Formo obj
	 */
	public function add_rule($rule, $params = NULL)
	{
		if (is_array($rule))
		{
			$this->_add_rule($rule);
		}
		else
		{
			if (isset($params))
			{
				$this->_add_rule(array($rule, $params));
			}
			else
			{
				$this->_add_rule(array($rule));
			}
		}

		return $this;
	}

	/**
	 * Add multiple rules at a time
	 * 
	 * @access public
	 * @param array $array)
	 * @return void
	 */
	public function add_rules( array $array)
	{
		foreach ($array as $rule)
		{
			$this->add_rule($rule);
		}

		return $this;
	}

	/**
	 * Add rules for multiple fields
	 * 
	 * @access public
	 * @param array $array
	 * @return Formo obj
	 */
	public function add_rules_fields( array $array)
	{
		foreach ($array as $alias => $rules)
		{
			$field = $this->find($alias, TRUE);

			if ( ! $field)
			{
				continue;
			}

			$field->add_rules($rules);
		}

		return $this;
	}

	/**
	 * Helper method to return the field's alias
	 * 
	 * @access public
	 * @return string
	 */
	public function alias()
	{
		return $this->_alias;
	}

	/**
	 * Return the field or form as an array
	 * 
	 * @access public
	 * @param mixed $value (default: NULL)
	 * @return array
	 */
	public function as_array($value = NULL)
	{
		$array = array();
		foreach ($this->_fields as $field)
		{
			if ($value === NULL)
			{
				$array += array($field->alias() => $field);
			}
			else
			{
				$array += array($field->alias() => $field->get($value));
			}
		}

		return $array;
	}

	/**
	 * Set an html attribute or attributes
	 * 
	 * @access public
	 * @param mixed $get
	 * @param mixed $set (default: NULL)
	 * @return Formo obj
	 */
	public function attr($get, $set = NULL)
	{
		if (func_num_args() == 1)
		{
			if (is_array($get))
			{
				foreach ($get as $key => $value)
				{
					$this->attr($key, $value);
				}

				return $this;
			}
			else
			{
				return \Arr::get($this->_attr, $get);
			}
		}

		$this->_attr = \Arr::merge($this->_attr, array($get => $set));

		return $this;
	}

	/**
	 * Set attributes for a set of fields
	 * 
	 * @access public
	 * @param array $array
	 * @return void
	 */
	public function attr_fields( array $array)
	{
		foreach ($array as $alias => $values)
		{
			$field = $this->find($alias, TRUE);

			if ( ! $field)
			{
				continue;
			}

			$field->attr($values);
		}

		return $this;
	}

	/**
	 * Setup callback or callbacks
	 * 
	 * @access public
	 * @param mixed $type
	 * @param array $callbacks
	 * @return Formo obj
	 */
	public function callback($type, array $callbacks = NULL)
	{
		if (is_array($type))
		{
			foreach ($type as $alias => $callbacks)
			{
				$field = $this->find($alias, TRUE);
				$field->merge('callbacks', $callbacks);
			}
		}
		else
		{
			$this->merge('callbacks', array($type => $callbacks));
		}
	}

	/**
	 * Return the field's closing tag
	 * 
	 * @access public
	 * @return string
	 */
	public function close()
	{
		if ($tag = $this->driver('get_tag'))
		{
			$has_singletag = in_array($tag, static::$_single_tags);
	
			// Let the config file determine whether to close the tags
			$str = ($has_singletag === TRUE)
				? '>'."\n"
				: '</'.$tag.'>'."\n";

			return $this->driver('close', array('str' => $str));
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * Run a method through a field's driver
	 * 
	 * @access public
	 * @param mixed $func
	 * @param array $args (default: NULL)
	 * @return mixed
	 */
	public function driver($func, array $args = NULL)
	{
		$class_name = 'Formo_Driver_'.ucfirst($this->_driver);

		$array = array('field' => $this);
		if ($args !== NULL)
		{
			$array = Arr::merge($args, $array);
		}

		return $class_name::$func($array);
	}

	/**
	 * Get or set a field error
	 * 
	 * @access public
	 * @param mixed $message (default: NULL)
	 * @param array $params (default: array())
	 * @return mixed
	 */
	public function error($message = NULL, array $params = array())
	{
		if ($message)
		{
			$this->_errors[$this->alias()] = array($message, $params);

			return $this;
		}
		else
		{
			return $this->_error_to_msg();
		}
	}

	/**
	 * Get field errors
	 * 
	 * @access public
	 * @param array & $array (default: NULL)
	 * @return array
	 */
	public function errors( array & $array = NULL)
	{
		if ($array === NULL)
		{
			$array = array();
			$is_first_field = TRUE;
		}
		else
		{
			$is_first_field = FALSE;
		}

		$error = $this->error();

		if ( ! empty($this->_fields))
		{
			if ($is_first_field === TRUE AND $error)
			{
				$array[':self'] = $error;
			}

			if ($is_first_field === FALSE)
			{
				$array[$this->alias()] = array();

				if ($error)
				{
					$array[$this->alias()][':self'] = $error;
				}
			}
		}
		elseif ($error)
		{
			$array[$this->alias()] = $error;
		}

		foreach ($this->_fields as $field)
		{
			if ($is_first_field === TRUE)
			{
				$field->errors($array);
			}
			else
			{
				$field->errors($array[$this->alias()]);
			}
		}

		if ( ! empty($this->_fields) AND $is_first_field === FALSE AND empty($array[$this->alias()]))
		{
			unset($array[$this->alias()]);
		}

		return $array;
	}

	/**
	 * Find a field by its alias
	 * Returns NULL if field can't be found
	 * 
	 * @access public
	 * @param mixed $alias
	 * @param mixed $not_recursive (default: FALSE)
	 * @return mixed
	 */
	public function find($alias, $not_recursive = FALSE)
	{
		if (is_array($alias))
		{
			$array = array();
			foreach ($alias as $_alias)
			{
				if ($field = $this->find($_alias))
				{
					$array += array($field);
				}
			}

			return $array;
		}

		if ($alias === ':self')
		{
			return $this;
		}
		
		foreach ($this->_fields as $field)
		{
			// First look directly at through all this object's fields
			if ($field->alias() == $alias)
			{
				return $field;
			}
		}

		if ($not_recursive === TRUE)
		{
			return NULL;
		}

		foreach ($this->_fields as $field)
		{
			// Next look deeper for the field
			if ($_field = $field->find($alias))
			{
				return $_field;
			}
		}

		// Return NULL if not a match
		return NULL;
	}

	/**
	 * Flatten fields in a form
	 * 
	 * @access public
	 * @param array &$array (default: NULL)
	 * @return Formo obj
	 */
	public function flatten( array &$array = NULL)
	{
		if ($array === NULL)
		{
			$array = array();
		}

		foreach ($this->_fields as $field)
		{
			if ($field->driver('is_a_parent'))
			{
				$field->flatten($array);
			}
			else
			{
				$array[] = $field;
			}
		}

		$this->_fields = $array;

		return $this;
	}

	/**
	 * Get a field attribute
	 * You can use Arr::path dot syntax to retrieve a value
	 * 
	 * @access public
	 * @param mixed $var
	 * @param mixed $default (default: NULL)
	 * @return mixed
	 */
	public function get($var, $default = NULL)
	{
		$parts = NULL;
		if (strpos($var, '.') !== FALSE)
		{
			$parts = explode('.', $var);
			$var = array_shift($parts);
		}

		$array_name = $this->_get_var_name($var);

		if ($array_name === 'val')
		{
			// Special case for value
			return $this->val();
		}

		if ($array_name === '_vars')
		{
			return Arr::get($this->_vars, $var, $default);
		}

		if ($parts)
		{
			return Arr::path($this->$array_name, implode('.', $parts), $default);
		}
		else
		{
			return (isset($this->$array_name))
				? $this->$array_name
				: $default;
		}
	}

	/**
	 * Get or set HTML
	 * 
	 * @access public
	 * @param mixed $str
	 * @return void
	 */
	public function html($str = NULL)
	{
		if (func_num_args() === 0)
		{
			return $this->_html;
		}

		$this->_html = $str;

		return $this;
	}

	/**
	 * Return a field's label
	 * 
	 * @access public
	 * @return void
	 */
	public function label()
	{
		return $this->_get_label();
	}

	/**
	 * Load an array of alias => value pairs into a form
	 * 
	 * @access public
	 * @param array $array (default: NULL)
	 * @return void
	 */
	public function load( array $array = NULL)
	{
		if ($array === NULL)
		{
			$post = Request::$current->post();
			$files = $this->_get_files_array();
			$array = Arr::merge($post, $files);
		}

		$this->set('input_array', $array);

		if ( ! $this->sent($array))
		{
			return $this;
		}

		if (Kohana::$profiling === TRUE)
		{
			// Start a new benchmark
			$benchmark = Profiler::start('Formo', __FUNCTION__);
		}
		
		if ($this->config('namespaces') === TRUE)
		{
			// First find all the fields that can be empty
			foreach ($array as $namespace => $values)
			{
				if ($namespace === $this->alias())
				{
					$this->_load($values);
				}
				elseif ($field = $this->find($namespace) AND $field->driver('is_a_parent'))
				{
					$field->_load($values);
				}
			}
		}
		else
		{
			$this->_load($array);
		}

		if (isset($benchmark))
		{
			// Stop benchmarking
			Profiler::stop($benchmark);
		}

		return $this;
	}

	/**
	 * Merge a field with an array value with a new array
	 * 
	 * @access public
	 * @param mixed $property
	 * @param array $array (default: NULL)
	 * @return Formo obj
	 */
	public function merge($property, array $array = NULL)
	{
		if (is_array($property))
		{
			foreach ($property as $_property => $_array)
			{
				$this->merge($_property, $_array);
			}
		}
		else
		{
			$this->_merge($property, $array);
		}

		return $this;
	}

	/**
	 * Merge properties for multiple fields
	 * 
	 * @access public
	 * @param array $array
	 * @return Formo obj
	 */
	public function merge_fields( array $array)
	{
		foreach ($array as $alias => $properties)
		{
			$field = $this->find($alias, TRUE);

			if ( ! $field)
			{
				continue;
			}

			$field->merge($properties);
		}

		return $this;
	}

	/**
	 * Return a field's HTML 'name' tag value
	 * 
	 * @access public
	 * @return string
	 */
	public function name()
	{
		$use_namespaces = $this->config('namespaces');

		return $this->driver('name', array('use_namespaces' => $use_namespaces));
	}

	/**
	 * Return a field's HTML opening tag
	 * 
	 * @access public
	 * @return string
	 */
	public function open()
	{
		if ($tag = $this->driver('get_tag'))
		{
			$has_singletag = in_array($tag, static::$_single_tags);
			
			$str = '<'.$tag.$this->_attr_to_str();
			$str.= ($has_singletag === TRUE)
				? NULL
				: '>';

			return $this->driver('open', array('str' => $str));
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * Reorder a field or multiple fields
	 * 
	 * @access public
	 * @param mixed $field
	 * @param mixed $new_order (default: NULL)
	 * @param mixed $relative_field (default: NULL)
	 * @return Formo obj
	 */
	public function order($field, $new_order = NULL, $relative_field = NULL)
	{
		if (is_array($field))
		{
			foreach($field as $alias => $values)
			{
				$this->order($alias, Arr::get($values, 0), Arr::get($values, 1));
			}
		}
		else
		{
			$this->_order($field, $new_order, $relative_field);
		}

		return $this;
	}

	/**
	 * Run a method through the ORM driver
	 * 
	 * @access public
	 * @param mixed $method
	 * @param array $vals (default: NULL)
	 * @return Formo obj
	 */
	public function orm($method, array $vals = NULL)
	{
		if ($vals === NULL)
		{
			$vals = array();
		}

		$vals = Arr::merge($vals, array('field' => $this));

		$driver = $this->config('orm_driver');

		$class_name = 'Formo_Driver_ORM_'.ucfirst($driver);

		$class_name::$method($vals);

		return $this;
	}

	/**
	 * Find a field's parent
	 * If TRUE passed, find the field's parent (return that field if it is the parent)
	 * 
	 * @access public
	 * @param mixed $group_or_form (default: FALSE)
	 * @return mixed
	 */
	public function parent($group_or_form = FALSE)
	{
		if ($group_or_form !== TRUE)
		{
			return $this->_parent;
		}

		$parent = ($this->driver('is_a_parent'))
			? $this
			: $this->parent()->parent(TRUE);

		return $parent;
	}

	/**
	 * Remove a field or fields from a form
	 * 
	 * @access public
	 * @param mixed $alias
	 * @return Formo obj
	 */
	public function remove($alias)
	{
		if (is_array($alias))
		{
			foreach ($alias as $_alias)
			{
				$this->remove($_alias);
			}
		}
		else
		{
			foreach ($this->_fields as $key => $field)
			{
				if ($field->alias() === $alias)
				{
					unset($this->_fields[$key]);
				}
			}
		}

		return $this;
	}

	/**
	 * Remove a class or classes
	 * 
	 * @access public
	 * @param mixed $class
	 * @return Formo obj
	 */
	public function remove_class($class)
	{
		if (is_array($class))
		{
			foreach ($class as $_class)
			{
				$this->remove_class(explode(' ', $class));
			}
		}
		elseif (strpos($class, ' ') !== FALSE)
		{
			$this->remove_class(explode(' ', $class));
		}
		else
		{
			$all_classes = explode(' ', \Arr::get($this->_attr, 'class'));
	
			if (($key = array_search($class, $all_classes)) !== FALSE)
			{
				unset($all_classes[$key]);
			}
		}

		return $this;
	}

	/**
	 * Remove a rule or rules from a field.
	 * 
	 * @access public
	 * @param mixed $alias
	 * @param mixed $rule (default: NULL)
	 * @return Formo obj
	 */
	public function remove_rule($rule)
	{
		$this->_remove_rule($rule);

		return $this;
	}

	/**
	 * Remove multiple rules at a time
	 * 
	 * @access public
	 * @param array $array
	 * @return void
	 */
	public function remove_rules( array $array)
	{
		foreach ($array as $rule_to_remove)
		{
			$this->remove_rule($rule_to_remove);
		}
	}

	/**
	 * Remove rules for multiple fields
	 * 
	 * @access public
	 * @param mixed $array
	 * @return void
	 */
	public function remove_rules_fields($array)
	{
		foreach ($array as $alias => $rules)
		{
			$field = $this->find($alias, TRUE);

			if ( ! $field)
			{
				continue;
			}

			$field->remove_rule($rules);
		}
	}

	/**
	 * Render a field from its view file
	 * 
	 * @access public
	 * @return string
	 */
	public function render($template = NULL)
	{
		if (Kohana::$profiling === TRUE)
		{
			// Start a new benchmark
			$benchmark = Profiler::start('Formo', __FUNCTION__);
		}

		if ($this->get('render') === FALSE)
		{
			return NULL;
		}

		if ($template == NULL)
		{
			$template = $this->driver('get_template');
			$template = $this->config('template_dir').$template;
		}

		$view = View::factory($template)
			->set('field', $this)
			->set('label', $this->label())
			->set('title', $this->title());

		$str = $view->render();

		if (isset($benchmark))
		{
			// Stop benchmarking
			Profiler::stop($benchmark);
		}

		return $str;
	}

	/**
	 * Render options (used for select, checkboxes, radios)
	 * 
	 * @access public
	 * @return string
	 */
	public function render_opts()
	{
		if ($template = $this->driver('get_opts_template'))
		{
			$template = $this->config('template_dir').$template;

			$view = View::factory($template)
				->set('field', $this)
				->set('opts', $this->get('opts', array()));

			return $view->render();
		}
	}

	/**
	 * Set a value for a field's attribute
	 * You can use Arr::set_path's dot-syntax to set an attribute
	 *
	 * @access public
	 * @param mixed $var
	 * @param mixed $val (default: NULL)
	 * @return void
	 */
	public function set($var, $val = NULL)
	{
		if (is_array($var))
		{
			foreach ($var as $key => $value)
			{
				$this->set($key, $value);
			}

			return $this;
		}

		if ($var === 'val')
		{
			// Special case for value
			$this->val($val);
			return $this;
		}

		if ($var === 'driver')
		{
			// Special case for driver
			$this->_set_driver($val);
			return $this;
		}

		if ($var == 'attr')
		{
			$this->attr($val);
			return $this;
		}

		if ($var == 'fields' AND is_array($val))
		{
			foreach ($val as $field)
			{
				$field['parent'] = $this;
				$new_field = Formo::factory($field);
				$this->_fields[] = $new_field;
			}

			return $this;
		}

		$parts = NULL;
		if (strpos($var, '.') !== FALSE)
		{
			$parts = explode('.', $var);
			$var = array_shift($parts);
		}

		$array_name = $this->_get_var_name($var);

		if ($parts)
		{
			Arr::set_path($this->$array_name, implode('.', $parts), $val);
		}
		elseif ($array_name === '_vars')
		{
			$this->_vars[$var] = $val;
		}
		else
		{
			// Set the value
			$this->$array_name = $val;
		}

		return $this;
	}

	/**
	 * Set variables for a set of fields
	 * 
	 * @access public
	 * @param array $array
	 * @return void
	 */
	public function set_fields( array $array)
	{
		foreach ($array as $alias => $vals)
		{
			$field = $this->find($alias, TRUE);

			if ( ! $field)
			{
				continue;
			}

			$field->set($vals);
		}

		return $this;
	}

	/**
	 * Determine whether a form or field has been sent
	 * 
	 * @access public
	 * @param array $input_array (default: NULL)
	 * @return boolean
	 */
	public function sent( array $input_array = NULL)
	{
		if ($input_array === NULL)
		{
			if ($arr = $this->get('input_array'))
			{
				$input_array = $arr;
			}
			else
			{
				$input_array = Request::$current->post();
			}
		}

		foreach ($input_array as $alias => $value)
		{
			if ($alias === $this->alias() OR $this->find($alias))
			{
				return TRUE;
			}
		}

		if ($parent = $this->parent())
		{
			return $this->parent()->sent();
		}

		return FALSE;
	}

	/**
	 * Create a subform from an array list of fields already in a form
	 * 
	 * @access public
	 * @param mixed $alias
	 * @param array $fields
	 * @param array $order (default: NULL)
	 * @param string $driver (default: 'group')
	 * @return Formo obj
	 */
	public function subform($alias, array $fields, array $order = NULL, $driver = 'group')
	{
		$subform = Formo::factory(array(
			'alias' => $alias,
			'driver' => $driver,
		));

		foreach ($fields as $field_alias)
		{
			$field = $this->find($field_alias, TRUE);
			$subform->add($field);
			$this->remove($field_alias);
		}

		$this->add($subform);

		if ($order !== NULL)
		{
			$this->order($alias, $order[0], Arr::get($order, 1));
		}

		return $this;
	}

	/**
	 * Return a field's title
	 * 
	 * @access public
	 * @return string
	 */
	public function title()
	{
		return $this->driver('get_title');
	}

	/**
	 * Use for casting to json or for sending form objects across an api
	 * 
	 * @access public
	 * @return void
	 */
	public function to_array()
	{
		if (Kohana::$profiling === TRUE)
		{
			// Start a new benchmark
			$benchmark = Profiler::start('Formo', __FUNCTION__);
		}

		$array = array
		(
			'alias' => $this->alias(),
			'driver' => $this->get('driver'),
			'val' => $this->val(),
			'opts' => $this->get('opts'),
			'attr' => $this->get('attr'),
			'rules' => $this->get('rules'),
			'html' => $this->html(),
			'render' => $this->get('render'),
			'fields' => array(),
		);

		// Traverse through fields if this field is considered a parent field
		if ($this->driver('is_a_parent'))
		{
			foreach ($this->get('fields') as $field)
			{
				// Attach the field as an array
				$array['fields'][] = $field->to_array();
			}
		}

		return $array;
	}

	public function val($new_val = NULL, $force_new = FALSE)
	{
		if (func_num_args() === 0)
		{
			return $this->_get_val();
		}
		else
		{
			$new_val = $this->driver('new_val', array('new_val' => $new_val));
			$this->_set_val($new_val, $force_new);

			return $this;
		}
	}

	/**
	 * Validate all fields and return whether form passed validation
	 * 
	 * @access public
	 * @return boolean
	 */
	public function validate()
	{
		$this->driver('pre_validate');

		if ( ! $this->sent())
		{
			// Return and don't run any callbacks
			return FALSE;
		}

		if ($this->get('render') === FALSE OR $this->get('ignore') === TRUE)
		{
			return;
		}

		if (Kohana::$profiling === TRUE)
		{
			// Start a new benchmark
			$benchmark = Profiler::start('Formo', __FUNCTION__);
		}

		$pass_validation = TRUE;

		foreach ($this->_fields as $field)
		{
			if ($field->validate() === FALSE)
			{
				$pass_validation = FALSE;
			}
		}

		if ($pass_validation === TRUE)
		{
			if (Arr::get($this->_errors, $this->alias()))
			{
				$pass_validation = TRUE;
			}
			else
			{
				$validation = $this->validation();
				$pass_validation = $validation->check();
				$this->_errors = $validation->errors();
			}
		}

		$this->_run_callbacks($pass_validation);

		if ($this->error())
		{
			$pass_validation = FALSE;
		}

		if (isset($benchmark))
		{
			// Stop benchmarking
			Profiler::stop($benchmark);
		}

		return $pass_validation;
	}

	/**
	 * Fetch a validation object from a field or form
	 * 
	 * @access public
	 * @param array $array (default: NULL)
	 * @return Validation obj
	 */
	public function validation( array $array = NULL)
	{
		$this->driver('pre_validate');

		if ($array !== NULL)
		{
			$validation = new Validation($array);
			$validation->rules($this->alias(), $this->_rules);

			foreach ($this->_fields as $field)
			{
				$field->driver('pre_validate');
				$validation->rules($field->alias(), $field->get('rules'));
			}
		}
		else
		{
			$values = $this->driver('get_validation_values');
	
			$validation = new Validation($values);	
			$this->_add_rules_to_validation($validation);
		}

		$parent = $this->parent(TRUE);
		$validation->bind(':formo', $this);
		$validation->bind(':form_val', $parent->val());
		$validation->bind(':form', $parent);

		return $validation;
	}

	/**
	 * Format error messages from a validation object according to Formo's
	 * formatting rules
	 * 
	 * @access public
	 * @param Kohana_Validation $validation
	 * @return mixed
	 */
	public function validation_errors( Kohana_Validation $validation)
	{
		$validation->check();
		$errors = $validation->errors();

		if ($this->driver('is_a_parent'))
		{
			$array = array();
			foreach ($this->_fields as $field)
			{
				if ($msg = $field->_error_to_msg($errors))
				{
					$array[$field->alias()] = $field->_error_to_msg($errors);
				}
			}

			return $array;
		}
		else
		{
			return $this->_error_to_msg($errors);
		}
	}

}
