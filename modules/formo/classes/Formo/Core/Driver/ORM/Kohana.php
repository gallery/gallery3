<?php defined('SYSPATH') or die('No direct script access.');

class Formo_Core_Driver_ORM_Kohana {

	protected static $_relationship_types = array('has_many', 'belongs_to', 'has_one');
	protected static $_table_columns = array();

	public static function load( array $array)
	{
		$model = $array['model'];
		$field = $array['field'];
		$std = new stdClass;

		static::_build_relationships($model, $std);

		foreach ($model->as_array() as $alias => $value)
		{
			// The bool that tracks whether the field is relational
			$relational_field = FALSE;
			// Create the array
			$options = array('alias' => $alias);
			// The default is the value from the table
			$options['val'] = $model->$alias;
			// If the field is a belongs_to field, do some extra processing
			static::_process_belongs_to($alias, $model, $std, $options);
			static::_process_has_one($alias, $model, $std, $options);
			// Process enum fields
			static::_process_enum($alias, $model, $options);
			//$foreign_key = $this->_process_belongs_to($alias, $options);
			// Add meta data for the field

			if (empty($options['driver']))
			{
				// Default to the default driver
				$options['driver'] = 'input';
			}

			$field
				->add($options);
		}

		static::_process_has_many($alias, $model, $std, $field);

		$rules = static::_get_base_rules($model);
		$rules = Arr::merge($rules, $model->rules());

		if ($rules)
		{
			$field->add_rules_fields($rules);
		}

		if ($filters = $model->filters())
		{
			foreach ($filters as $alias => $_filters)
			{
				$field->merge($alias, array('filters' => $_filters));
			}
		}

		if (method_exists($model, 'formo'))
		{
			unset($array['model']);
			$model->formo($field, $array);
		}
	}

	public static function select_list($result, $key, $value)
	{
		$array = array();
		foreach ($result as $row)
		{
			$array[$row->$key] = $row->$value;
		}

		return $array;
	}

	protected static function _build_relationships( Kohana_ORM $model, stdClass $std)
	{
		// Pull out relationship data
		foreach (static::$_relationship_types as $type)
		{
			$std->{$type} = array
			(
				'definitions' => array(),
				'foreign_keys' => array(),
			);

			$std->{$type}['definitions'] = $model->$type();

			foreach ($std->{$type}['definitions'] as $key => $values)
			{
				$value = (isset($values['far_key']))
					? $values['far_key']
					: $values['foreign_key'];

				$std->{$type}['foreign_keys'][$value] = $key;
			}
		}
	}

	protected static function _get_base_rules($model)
	{
		$info = $model->list_columns();

		$rules = array();
		foreach ($info as $alias => $data)
		{
			if ($data['is_nullable'] !== TRUE)
			{
				$rules[$alias][] = array('not_empty');
			}

			if ($data['type'] === 'int')
			{
				$rules[$alias][] = array('digit', array(':value', true));
				$rules[$alias][] = array('range', array(':value', Arr::get($data, 'min', 0), Arr::get($data, 'max', 1)));
			}
			elseif ($data['type'] === 'varchar')
			{
				$rules[$alias][] = array('maxlength', array(':value', Arr::get($data, 'character_maximum_length')));
			}
		}

		return $rules;
	}

	protected static function _process_belongs_to($alias, Kohana_ORM $model, stdClass $std, array & $options)
	{
		if ( ! isset($std->belongs_to['foreign_keys'][$alias]))
		{
			// No need to process non-belongs-to fields here
			return NULL;
		}

		$field_alias = $std->belongs_to['foreign_keys'][$alias];

		if (Arr::get($std->belongs_to['definitions'][$field_alias], 'formo') === true)
		{
			$options['driver'] = 'select';
			$opts = ORM::factory($std->belongs_to['definitions'][$field_alias]['model'])->find_all();
			$options['opts'] = static::select_list($opts, 'id', 'name');
		}
	}

	protected static function _process_has_many($alias, Kohana_ORM $model, stdClass $std, Formo $form)
	{
		if (empty($std->has_many))
		{
			// No need to process non-has-many fields here
			return NULL;
		}

		foreach (Arr::get($std->has_many, 'definitions', array()) as $key => $values)
		{
			if (Arr::get($values, 'formo') === true)
			{
				$rs_all = ORM::factory($values['model'])
					->find_all();

				$rs_in = ORM::factory($values['model'])
					->where($values['foreign_key'], '=', $model->pk())
					->find_all();

				$opts = static::select_list($rs_all, 'id', 'name');
				$val = static::select_list($rs_in, 'id', 'id');

				$form->add($key, 'checkboxes', $val, array('opts' => $opts));
			}
			else
			{
				$form->add($key, 'checkboxes', null, array('render' => false));
			}
		}
	}

	protected static function _process_has_one($alias, Kohana_ORM $model, stdClass $std, array & $options)
	{
		if ( ! isset($std->has_one['foreign_keys'][$alias]))
		{
			// No need to process non-belongs-to fields here
			return NULL;
		}

		$field_alias = $std->has_one['foreign_keys'][$alias];

		if (Arr::get($std->has_one['definitions'][$field_alias], 'formo') === true)
		{
			$options['driver'] = 'select';
			$opts = ORM::factory($std->has_one['definitions'][$field_alias]['model'])->find_all();
			$options['opts'] = static::select_list($opts, 'id', 'name');
		}
	}


	public static function _process_enum($alias, Kohana_ORM $model, & $options)
	{
		$column = Arr::get(static::_table_columns($model), $alias, array());

		if (Arr::get($column, 'data_type') != 'enum')
		{
			return;
		}

		$opts = Arr::get($column, 'options', array());

		$options['driver'] = 'select';
		$options['opts'] = array_combine($opts, $opts);

		if (empty($options['val']))
		{
			$options['val'] = Arr::geT($column, 'column_default');
		}
	}

	public static function _table_columns($model)
	{
		if (empty(static::$_table_columns))
		{
			static::$_table_columns = $model->table_columns();
		}

		return static::$_table_columns;
	}

}