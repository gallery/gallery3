<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * [Object Relational Mapping][ref-orm] (ORM) is a method of abstracting database
 * access to standard PHP calls. All table rows are represented as model objects,
 * with object properties representing row data. ORM in Kohana generally follows
 * the [Active Record][ref-act] pattern.
 *
 * [ref-orm]: http://wikipedia.org/wiki/Object-relational_mapping
 * [ref-act]: http://wikipedia.org/wiki/Active_record
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class ORM_Core {

	// Current relationships
	protected $has_one                 = array();
	protected $belongs_to              = array();
	protected $has_many                = array();
	protected $has_and_belongs_to_many = array();
	protected $has_many_through        = array();

	// Relationships that should always be joined
	protected $load_with = array();

	// Current object
	protected $object  = array();
	protected $changed = array();
	protected $related = array();
	protected $_valid  = FALSE;
	protected $_loaded  = FALSE;
	protected $_saved   = FALSE;
	protected $sorting;
	protected $rules = array();

	// Related objects
	protected $object_relations = array();
	protected $changed_relations = array();

	// Model table information
	protected $object_name;
	protected $object_plural;
	protected $table_name;
	protected $table_columns;
	protected $ignored_columns;

	// Auto-update columns for creation and updates
	protected $updated_column = NULL;
	protected $created_column = NULL;

	// Table primary key and value
	protected $primary_key = 'id';
	protected $primary_val = 'name';

	// Array of foreign key name overloads
	protected $foreign_key = array();

	// Model configuration
	protected $table_names_plural = TRUE;
	protected $reload_on_wakeup   = TRUE;

	// Database configuration
	protected $db = 'default';
	protected $db_applied = array();
	protected $db_builder;

	// With calls already applied
	protected $with_applied = array();

	// Stores column information for ORM models
	protected static $column_cache = array();

	/**
	 * Creates and returns a new model.
	 *
	 * @chainable
	 * @param   string  model name
	 * @param   mixed   parameter for find()
	 * @return  ORM
	 */
	public static function factory($model, $id = NULL)
	{
		// Set class name
		$model = ucfirst($model).'_Model';

		return new $model($id);
	}

	/**
	 * Prepares the model database connection and loads the object.
	 *
	 * @param   mixed  parameter for find or object to load
	 * @return  void
	 */
	public function __construct($id = NULL)
	{
		// Set the object name and plural name
		$this->object_name   = strtolower(substr(get_class($this), 0, -6));
		$this->object_plural = inflector::plural($this->object_name);

		if ( ! isset($this->sorting))
		{
			// Default sorting
			$this->sorting = array($this->primary_key => 'asc');
		}

		// Initialize database
		$this->_initialize();

		// Clear the object
		$this->clear();

		if (is_object($id))
		{
			// Load an object
			$this->_load_values((array) $id);
		}
		elseif ( ! empty($id))
		{
			// Set the object's primary key, but don't load it until needed
			$this->object[$this->primary_key] = $id;

			// Object is considered saved until something is set
			$this->_saved = TRUE;
		}
	}

	/**
	 * Prepares the model database connection, determines the table name,
	 * and loads column information.
	 *
	 * @return  void
	 */
	public function _initialize()
	{
		if ( ! is_object($this->db))
		{
			// Get database instance
			$this->db = Database::instance($this->db);
		}

		if (empty($this->table_name))
		{
			// Table name is the same as the object name
			$this->table_name = $this->object_name;

			if ($this->table_names_plural === TRUE)
			{
				// Make the table name plural
				$this->table_name = inflector::plural($this->table_name);
			}
		}

		if (is_array($this->ignored_columns))
		{
			// Make the ignored columns mirrored = mirrored
			$this->ignored_columns = array_combine($this->ignored_columns, $this->ignored_columns);
		}

		// Load column information
		$this->reload_columns();

		// Initialize the builder
		$this->db_builder = db::build();
	}

	/**
	 * Allows serialization of only the object data and state, to prevent
	 * "stale" objects being unserialized, which also requires less memory.
	 *
	 * @return  array
	 */
	public function __sleep()
	{
		// Store only information about the object
		return array('object_name', 'object', 'changed', '_loaded', '_saved', 'sorting');
	}

	/**
	 * Prepares the database connection and reloads the object.
	 *
	 * @return  void
	 */
	public function __wakeup()
	{
		// Initialize database
		$this->_initialize();

		if ($this->reload_on_wakeup === TRUE)
		{
			// Reload the object
			$this->reload();
		}
	}

	/**
	 * Handles pass-through to database methods. Calls to query methods
	 * (query, get, insert, update) are not allowed. Query builder methods
	 * are chainable.
	 *
	 * @param   string  method name
	 * @param   array   method arguments
	 * @return  mixed
	 */
	public function __call($method, array $args)
	{
		if (method_exists($this->db_builder, $method))
		{
			if (in_array($method, array('execute', 'insert', 'update', 'delete')))
				throw new Kohana_Exception('Query methods cannot be used through ORM');

			// Method has been applied to the database
			$this->db_applied[$method] = $method;

			// Number of arguments passed
			$num_args = count($args);

			if ($method === 'select' AND $num_args > 3)
			{
				// Call select() manually to avoid call_user_func_array
				$this->db_builder->select($args);
			}
			else
			{
				// We use switch here to manually call the database methods. This is
				// done for speed: call_user_func_array can take over 300% longer to
				// make calls. Most database methods are 4 arguments or less, so this
				// avoids almost any calls to call_user_func_array.

				switch ($num_args)
				{
					case 0:
						if (in_array($method, array('open', 'and_open', 'or_open', 'close', 'cache')))
						{
							// Should return ORM, not Database
							$this->db_builder->$method();
						}
						else
						{
							// Support for things like reset_select, reset_write, list_tables
							return $this->db_builder->$method();
						}
					break;
					case 1:
						$this->db_builder->$method($args[0]);
					break;
					case 2:
						$this->db_builder->$method($args[0], $args[1]);
					break;
					case 3:
						$this->db_builder->$method($args[0], $args[1], $args[2]);
					break;
					case 4:
						$this->db_builder->$method($args[0], $args[1], $args[2], $args[3]);
					break;
					default:
						// Here comes the snail...
						call_user_func_array(array($this->db, $method), $args);
					break;
				}
			}

			return $this;
		}
		else
		{
			throw new Kohana_Exception('Invalid method :method called in :class',
				array(':method' => $method, ':class' => get_class($this)));
		}
	}

	/**
	 * Handles retrieval of all model values, relationships, and metadata.
	 *
	 * @param   string  column name
	 * @return  mixed
	 */
	public function __get($column)
	{
		if (array_key_exists($column, $this->object))
		{
			if( ! $this->loaded() AND ! $this->empty_primary_key())
			{
				// Column asked for but the object hasn't been loaded yet, so do it now
				// Ignore loading of any columns that have been changed
				$this->find($this->object[$this->primary_key], TRUE);
			}

			return $this->object[$column];
		}
		elseif (isset($this->related[$column]))
		{
			return $this->related[$column];
		}
		elseif ($column === 'primary_key_value')
		{
			if( ! $this->loaded() AND ! $this->empty_primary_key() AND $this->unique_key($this->object[$this->primary_key]) !== $this->primary_key)
			{
				// Load if object hasn't been loaded and the key given isn't the primary_key
				// that we need (i.e. passing an email address to ORM::factory rather than the id)
				$this->find($this->object[$this->primary_key], TRUE);
			}

			return $this->object[$this->primary_key];
		}
		elseif ($model = $this->related_object($column))
		{
			// This handles the has_one and belongs_to relationships

			if (in_array($model->object_name, $this->belongs_to))
			{
				if ( ! $this->loaded() AND ! $this->empty_primary_key())
				{
					// Load this object first so we know what id to look for in the foreign table
					$this->find($this->object[$this->primary_key], TRUE);
				}

				// Foreign key lies in this table (this model belongs_to target model)
				$where = array($model->foreign_key(TRUE), '=', $this->object[$this->foreign_key($column)]);
			}
			else
			{
				// Foreign key lies in the target table (this model has_one target model)
				$where = array($this->foreign_key($column, $model->table_name), '=', $this->primary_key_value);
			}

			// one<>alias:one relationship
			return $this->related[$column] = $model->find($where);
		}
		elseif (isset($this->has_many_through[$column]))
		{
			// Load the "middle" model
			$through = ORM::factory(inflector::singular($this->has_many_through[$column]));

			// Load the "end" model
			$model = ORM::factory(inflector::singular($column));

			// Join ON target model's primary key set to 'through' model's foreign key
			// User-defined foreign keys must be defined in the 'through' model
			$join_table = $through->table_name;
			$join_col1  = $through->foreign_key($model->object_name, $join_table);
			$join_col2  = $model->foreign_key(TRUE);

			// one<>alias:many relationship
			return $model
				->join($join_table, $join_col1, $join_col2)
				->where($through->foreign_key($this->object_name, $join_table), '=', $this->primary_key_value);
		}
		elseif (isset($this->has_many[$column]))
		{
			// one<>many aliased relationship
			$model_name = $this->has_many[$column];

			$model = ORM::factory(inflector::singular($model_name));

			return $model->where($this->foreign_key($column, $model->table_name), '=', $this->primary_key_value);
		}
		elseif (in_array($column, $this->has_many))
		{
			// one<>many relationship
			$model = ORM::factory(inflector::singular($column));
			return $model->where($this->foreign_key($column, $model->table_name), '=', $this->primary_key_value);
		}
		elseif (in_array($column, $this->has_and_belongs_to_many))
		{
			// Load the remote model, always singular
			$model = ORM::factory(inflector::singular($column));

			if ($this->has($model, TRUE))
			{
				// many<>many relationship
				return $model->where($model->foreign_key(TRUE), 'IN', $this->changed_relations[$column]);
			}
			else
			{
				// empty many<>many relationship
				return $model->where($model->foreign_key(TRUE), 'IS', NULL);
			}
		}
		elseif (isset($this->ignored_columns[$column]))
		{
			return NULL;
		}
		elseif (in_array($column, array
			(
				'object_name', 'object_plural','_valid', // Object
				'primary_key', 'primary_val', 'table_name', 'table_columns', // Table
				'has_one', 'belongs_to', 'has_many', 'has_many_through', 'has_and_belongs_to_many', 'load_with' // Relationships
			)))
		{
			// Model meta information
			return $this->$column;
		}
		else
		{
			throw new Kohana_Exception('The :property property does not exist in the :class class',
				array(':property' => $column, ':class' => get_class($this)));
		}
	}

	/**
	 * Tells you if the Model has been loaded or not
	 *
	 * @return bool
	 */
	public function loaded() {
		if ( ! $this->_loaded AND ! $this->empty_primary_key())
		{
			// If returning the loaded member and no load has been attempted, do it now
			$this->find($this->object[$this->primary_key], TRUE);
		}
		return $this->_loaded;
	}

	/**
	 * Tells you if the model was saved successfully or not
	 *
	 * @return bool
	 */
	public function saved() {
		return $this->_saved;
	}

	/**
	 * Handles setting of all model values, and tracks changes between values.
	 *
	 * @param   string  column name
	 * @param   mixed   column value
	 * @return  void
	 */
	public function __set($column, $value)
	{
		if (isset($this->ignored_columns[$column]))
		{
			return NULL;
		}
		elseif (isset($this->object[$column]) OR array_key_exists($column, $this->object))
		{
			if (isset($this->table_columns[$column]))
			{
				// Data has changed
				$this->changed[$column] = $column;

				// Object is no longer saved or valid
				$this->_saved = $this->_valid = FALSE;
			}

			$this->object[$column] = $value;
		}
		elseif (in_array($column, $this->has_and_belongs_to_many) AND is_array($value))
		{
			// Load relations
			$model = ORM::factory(inflector::singular($column));

			if ( ! isset($this->object_relations[$column]))
			{
				// Load relations
				$this->has($model);
			}

			// Change the relationships
			$this->changed_relations[$column] = $value;

			if (isset($this->related[$column]))
			{
				// Force a reload of the relationships
				unset($this->related[$column]);
			}
		}
		else
		{
			throw new Kohana_Exception('The :property: property does not exist in the :class: class',
				array(':property:' => $column, ':class:' => get_class($this)));
		}
	}

	/**
	 * Chainable set method
	 *
	 * @param   string  name of field or array of key => val
	 * @param   mixed   value
	 * @return  ORM
	 */
	public function set($name, $value = NULL)
	{
		if (is_array($name))
		{
			foreach ($name as $key => $value)
			{
				$this->__set($key, $value);
			}
		}
		else
		{
			$this->__set($name, $value);
		}

		return $this;
	}

	/**
	 * Checks if object data is set.
	 *
	 * @param   string  column name
	 * @return  boolean
	 */
	public function __isset($column)
	{
		return (isset($this->object[$column]) OR isset($this->related[$column]));
	}

	/**
	 * Unsets object data.
	 *
	 * @param   string  column name
	 * @return  void
	 */
	public function __unset($column)
	{
		unset($this->object[$column], $this->changed[$column], $this->related[$column]);
	}

	/**
	 * Returns the values of this object as an array.
	 *
	 * @return  array
	 */
	public function as_array()
	{
		$object = array();

		foreach ($this->object as $key => $val)
		{
			// Reconstruct the array (calls __get)
			$object[$key] = $this->$key;
		}

		foreach ($this->with_applied as $model => $enabled)
		{
			// Generate arrays for relationships
			if ($enabled)
			{
				$object[$model] = $this->$model->as_array();
			}
		}

		return $object;
	}

	/**
	 * Binds another one-to-one object to this model.  One-to-one objects
	 * can be nested using 'object1:object2' syntax
	 *
	 * @param   string  target model to bind to
	 * @return  void
	 */
	public function with($target_path)
	{
		if (isset($this->with_applied[$target_path]))
		{
			// Don't join anything already joined
			return $this;
		}

		// Split object parts
		$objects = explode(':', $target_path);
		$target	 = $this;
		foreach ($objects as $object)
		{
			// Go down the line of objects to find the given target
			$parent = $target;
			$target = $parent->related_object($object);

			if ( ! $target)
			{
				// Can't find related object
				return $this;
			}
		}

		$target_name = $object;

		// Pop-off top object to get the parent object (user:photo:tag becomes user:photo - the parent table prefix)
		array_pop($objects);
		$parent_path = implode(':', $objects);

		if (empty($parent_path))
		{
			// Use this table name itself for the parent object
			$parent_path = $this->table_name;
		}
		else
		{
			if( ! isset($this->with_applied[$parent_path]))
			{
				// If the parent object hasn't been joined yet, do it first (otherwise LEFT JOINs fail)
				$this->with($parent_path);
			}
		}

		// Add to with_applied to prevent duplicate joins
		$this->with_applied[$target_path] = TRUE;

		$select = array();

		// Use the keys of the empty object to determine the columns
		foreach (array_keys($target->object) as $column)
		{
			// Add the prefix so that load_result can determine the relationship
			$select[$target_path.':'.$column] = $target_path.'.'.$column;
		}

		// Select all of the prefixed keys in the object
		$this->db_builder->select($select);

		if (in_array($target->object_name, $parent->belongs_to))
		{
			// Parent belongs_to target, use target's primary key as join column
			$join_col1 = $target->foreign_key(TRUE, $target_path);
			$join_col2 = $parent->foreign_key($target_name, $parent_path);
		}
		else
		{
			// Parent has_one target, use parent's primary key as join column
			$join_col2 = $parent->foreign_key(TRUE, $parent_path);
			$join_col1 = $parent->foreign_key($target_name, $target_path);
		}

		// This trick allows for models to use different table prefixes (sharing the same database)
		$join_table = array($this->db->quote_table($target_path) => $target->db->quote_table($target->table_name));

		// Join the related object into the result
		// Use Database_Expression to disable prefixing
		$this->db_builder->join(new Database_Expression($join_table), $join_col1, $join_col2, 'LEFT');

		return $this;
	}

	/**
	 * Finds and loads a single database row into the object.
	 *
	 * @chainable
	 * @param   mixed  primary key or an array of clauses
	 * @param   bool   ignore loading of columns that have been modified
	 * @return  ORM
	 */
	public function find($id = NULL, $ignore_changed = FALSE)
	{
		if ($id !== NULL)
		{
			if (is_array($id))
			{
				// Search for all clauses
				$this->db_builder->where(array($id));
			}
			else
			{
				// Search for a specific column
				$this->db_builder->where($this->table_name.'.'.$this->unique_key($id), '=', $id);
			}
		}

		return $this->load_result(FALSE, $ignore_changed);
	}

	/**
	 * Finds multiple database rows and returns an iterator of the rows found.
	 *
	 * @chainable
	 * @param   integer  SQL limit
	 * @param   integer  SQL offset
	 * @return  ORM_Iterator
	 */
	public function find_all($limit = NULL, $offset = NULL)
	{
		if ($limit !== NULL AND ! isset($this->db_applied['limit']))
		{
			// Set limit
			$this->limit($limit);
		}

		if ($offset !== NULL AND ! isset($this->db_applied['offset']))
		{
			// Set offset
			$this->offset($offset);
		}

		return $this->load_result(TRUE);
	}

	/**
	 * Creates a key/value array from all of the objects available. Uses find_all
	 * to find the objects.
	 *
	 * @param   string  key column
	 * @param   string  value column
	 * @return  array
	 */
	public function select_list($key = NULL, $val = NULL)
	{
		if ($key === NULL)
		{
			$key = $this->primary_key;
		}

		if ($val === NULL)
		{
			$val = $this->primary_val;
		}

		// Return a select list from the results
		return $this->select($this->table_name.'.'.$key, $this->table_name.'.'.$val)->find_all()->select_list($key, $val);
	}

	/**
	 * Validates the current object. This method requires that rules are set to be useful, if called with out
	 * any rules set, or if a Validation object isn't passed, nothing happens.
	 *
	 * @param   object   Validation array
	 * @param   boolean  Save on validate
	 * @return  ORM
	 * @chainable
	 */
	public function validate(Validation $array = NULL)
	{
		if ($array === NULL)
			$array = new Validation($this->object);

		if (count($this->rules) > 0)
		{
			foreach ($this->rules as $field => $parameters)
			{
				foreach ($parameters as $type => $value) {
					switch ($type) {
						case 'pre_filter':
							$array->pre_filter($value,$field);
							break;
						case 'rules':
							$rules = array_merge(array($field),$value);
							call_user_func_array(array($array,'add_rules'), $rules);
							break;
						case 'callbacks':
							$callbacks = array_merge(array($field),$value);
							call_user_func_array(array($array,'add_callbacks'), $callbacks);
							break;
					}
				}
			}
		}
		// Validate the array
		if (($this->_valid = $array->validate()) === FALSE)
		{
			ORM_Validation_Exception::handle_validation($this->table_name, $array);
		}

		// Fields may have been modified by filters
		$this->object = array_merge($this->object, $array->getArrayCopy());

		// Return validation status
		return $this;
	}

	/**
	 * Saves the current object.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function save()
	{
		if ( ! empty($this->changed))
		{
			// Require model validation before saving
			if ( ! $this->_valid)
			{
				$this->validate();
			}

			$data = array();
			foreach ($this->changed as $column)
			{
				// Compile changed data
				$data[$column] = $this->object[$column];
			}

			if ( ! $this->empty_primary_key() AND ! isset($this->changed[$this->primary_key]))
			{
				// Primary key isn't empty and hasn't been changed so do an update

				if (is_array($this->updated_column))
				{
					// Fill the updated column
					$column = $this->updated_column['column'];
					$format = $this->updated_column['format'];

					$data[$column] = $this->object[$column] = ($format === TRUE) ? time() : date($format);
				}

				$query = db::update($this->table_name)
					->set($data)
					->where($this->primary_key, '=', $this->primary_key_value)
					->execute($this->db);

				// Object has been saved
				$this->_saved = TRUE;
			}
			else
			{
				if (is_array($this->created_column))
				{
					// Fill the created column
					$column = $this->created_column['column'];
					$format = $this->created_column['format'];

					$data[$column] = $this->object[$column] = ($format === TRUE) ? time() : date($format);
				}

				$result = db::insert($this->table_name)
					->columns(array_keys($data))
					->values(array_values($data))
					->execute($this->db);

				if ($result->count() > 0)
				{
					if (empty($this->object[$this->primary_key]))
					{
						// Load the insert id as the primary key
						$this->object[$this->primary_key] = $result->insert_id();
					}

					// Object is now loaded and saved
					$this->_loaded = $this->_saved = TRUE;
				}
			}

			if ($this->saved() === TRUE)
			{
				// All changes have been saved
				$this->changed = array();
			}
		}

		if ($this->saved() === TRUE AND ! empty($this->changed_relations))
		{
			foreach ($this->changed_relations as $column => $values)
			{
				// All values that were added
				$added = array_diff($values, $this->object_relations[$column]);

				// All values that were saved
				$removed = array_diff($this->object_relations[$column], $values);

				if (empty($added) AND empty($removed))
				{
					// No need to bother
					continue;
				}

				// Clear related columns
				unset($this->related[$column]);

				// Load the model
				$model = ORM::factory(inflector::singular($column));

				if (($join_table = array_search($column, $this->has_and_belongs_to_many)) === FALSE)
					continue;

				if (is_int($join_table))
				{
					// No "through" table, load the default JOIN table
					$join_table = $model->join_table($this->table_name);
				}

				// Foreign keys for the join table
				$object_fk  = $this->foreign_key($join_table);
				$related_fk = $model->foreign_key($join_table);

				if ( ! empty($added))
				{
					foreach ($added as $id)
					{
						// Insert the new relationship
						db::insert($join_table)
							->columns($object_fk, $related_fk)
							->values($this->primary_key_value, $id)
							->execute($this->db);
					}
				}

				if ( ! empty($removed))
				{
					db::delete($join_table)
						->where($object_fk, '=', $this->primary_key_value)
						->where($related_fk, 'IN', $removed)
						->execute($this->db);
				}

				// Clear all relations for this column
				unset($this->object_relations[$column], $this->changed_relations[$column]);
			}
		}

		if ($this->saved() === TRUE)
		{
			// Always force revalidation after saving
			$this->_valid = FALSE;

			// Clear the per-request database cache
			$this->db->clear_cache(NULL, Database::PER_REQUEST);
		}

		return $this;
	}

	/**
	 * Deletes the current object from the database. This does NOT destroy
	 * relationships that have been created with other objects.
	 *
	 * @chainable
	 * @param   mixed  id to delete
	 * @return  ORM
	 */
	public function delete($id = NULL)
	{
		if ($id === NULL)
		{
			// Use the the primary key value
			$id = $this->primary_key_value;
		}

		// Delete this object
		db::delete($this->table_name)
			->where($this->primary_key, '=', $id)
			->execute($this->db);

		// Clear the per-request database cache
		$this->db->clear_cache(NULL, Database::PER_REQUEST);

		return $this->clear();
	}

	/**
	 * Delete all objects in the associated table. This does NOT destroy
	 * relationships that have been created with other objects.
	 *
	 * @chainable
	 * @param   array  ids to delete
	 * @return  ORM
	 */
	public function delete_all($ids = NULL)
	{
		if (is_array($ids))
		{
			// Delete only given ids
			db::delete($this->table_name)
				->where($this->primary_key, 'IN', $ids)
				->execute($this->db);
		}
		elseif ($ids === NULL)
		{
			// Delete all records
			db::delete($this->table_name)
				->execute($this->db);
		}
		else
		{
			// Do nothing - safeguard
			return $this;
		}

		// Clear the per-request database cache
		$this->db->clear_cache(NULL, Database::PER_REQUEST);

		return $this->clear();
	}

	/**
	 * Unloads the current object and clears the status.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function clear()
	{
		// Create an array with all the columns set to NULL
		$columns = array_keys($this->table_columns);
		$values  = array_combine($columns, array_fill(0, count($columns), NULL));

		// Replace the current object with an empty one
		$this->_load_values($values);

		return $this;
	}

	/**
	 * Reloads the current object from the database.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function reload()
	{
                if ($this->_loaded) {
                        return $this->find($this->object[$this->primary_key]);
                } else {
                        return $this->clear();
                }
	}

	/**
	 * Reload column definitions.
	 *
	 * @chainable
	 * @param   boolean  force reloading
	 * @return  ORM
	 */
	public function reload_columns($force = FALSE)
	{
		if ($force === TRUE OR empty($this->table_columns))
		{
			if (isset(ORM::$column_cache[$this->object_name]))
			{
				// Use cached column information
				$this->table_columns = ORM::$column_cache[$this->object_name];
			}
			else
			{
				// Load table columns
				ORM::$column_cache[$this->object_name] = $this->table_columns = $this->list_fields();
			}
		}

		return $this;
	}

	/**
	 * Tests if this object has a relationship to a different model.
	 *
	 * @param   object   related ORM model
	 * @param   boolean  check for any relations to given model
	 * @return  boolean
	 */
	public function has(ORM $model, $any = FALSE)
	{
		// Determine plural or singular relation name
		$related = ($model->table_names_plural === TRUE) ? $model->object_plural : $model->object_name;

		if (($join_table = array_search($related, $this->has_and_belongs_to_many)) === FALSE)
			return FALSE;

		if (is_int($join_table))
		{
			// No "through" table, load the default JOIN table
			$join_table = $model->join_table($this->table_name);
		}

		if ( ! isset($this->object_relations[$related]))
		{
			// Load the object relationships
			$this->changed_relations[$related] = $this->object_relations[$related] = $this->load_relations($join_table, $model);
		}

		if( ! $model->loaded() AND ! $model->empty_primary_key())
		{
			// Load the related model if it hasn't already been
			$model->find($model->object[$model->primary_key]);
		}

		if ( ! $model->empty_primary_key())
		{
			// Check if a specific object exists
			return in_array($model->primary_key_value, $this->changed_relations[$related]);
		}
		elseif ($any)
		{
			// Check if any relations to given model exist
			return ! empty($this->changed_relations[$related]);
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Adds a new relationship to between this model and another.
	 *
	 * @param   object   related ORM model
	 * @return  boolean
	 */
	public function add(ORM $model)
	{
		if ($this->has($model))
			return TRUE;

		// Get the faked column name
		$column = $model->object_plural;

		// Add the new relation to the update
		$this->changed_relations[$column][] = $model->primary_key_value;

		if (isset($this->related[$column]))
		{
			// Force a reload of the relationships
			unset($this->related[$column]);
		}

		return TRUE;
	}

	/**
	 * Adds a new relationship to between this model and another.
	 *
	 * @param   object   related ORM model
	 * @return  boolean
	 */
	public function remove(ORM $model)
	{
		if ( ! $this->has($model))
			return FALSE;

		// Get the faked column name
		$column = $model->object_plural;

		if (($key = array_search($model->primary_key_value, $this->changed_relations[$column])) === FALSE)
			return FALSE;

		// Remove the relationship
		unset($this->changed_relations[$column][$key]);

		if (isset($this->related[$column]))
		{
			// Force a reload of the relationships
			unset($this->related[$column]);
		}

		return TRUE;
	}

	/**
	 * Count the number of records in the table.
	 *
	 * @return  integer
	 */
	public function count_all()
	{
		// Return the total number of records in a table
		return $this->db_builder->count_records($this->table_name);
	}

	/**
	 * Proxy method to Database list_fields.
	 *
	 * @return  array
	 */
	public function list_fields()
	{
		// Proxy to database
		return $this->db->list_fields($this->table_name);
	}

	/**
	 * Proxy method to Database clear_cache.
	 *
	 * @chainable
	 * @param   string  SQL query to clear
	 * @param   integer  Type of cache to clear, Database::CROSS_REQUEST or Database::PER_REQUEST
	 * @return  ORM
	 */
	public function clear_cache($sql = NULL, $type = NULL)
	{
		// Proxy to database
		$this->db->clear_cache($sql, $type);

		ORM::$column_cache = array();

		return $this;
	}

	/**
	 * Returns the unique key for a specific value. This method is expected
	 * to be overloaded in models if the model has other unique columns.
	 *
	 * @param   mixed   unique value
	 * @return  string
	 */
	public function unique_key($id)
	{
		return $this->primary_key;
	}

	/**
	 * Determines the name of a foreign key for a specific table.
	 *
	 * @param   string  related table name
	 * @param   string  prefix table name (used for JOINs)
	 * @return  string
	 */
	public function foreign_key($table = NULL, $prefix_table = NULL)
	{
		if ($table === TRUE)
		{
			if (is_string($prefix_table))
			{
				// Use prefix table name and this table's PK
				return $prefix_table.'.'.$this->primary_key;
			}
			else
			{
				// Return the name of this table's PK
				return $this->table_name.'.'.$this->primary_key;
			}
		}

		if (is_string($prefix_table))
		{
			// Add a period for prefix_table.column support
			$prefix_table .= '.';
		}

		if (isset($this->foreign_key[$table]))
		{
			// Use the defined foreign key name, no magic here!
			$foreign_key = $this->foreign_key[$table];
		}
		else
		{
			if ( ! is_string($table) OR ! array_key_exists($table.'_'.$this->primary_key, $this->object))
			{
				// Use this table
				$table = $this->table_name;

				if (strpos($table, '.') !== FALSE)
				{
					// Hack around support for PostgreSQL schemas
					list ($schema, $table) = explode('.', $table, 2);
				}

				if ($this->table_names_plural === TRUE)
				{
					// Make the key name singular
					$table = inflector::singular($table);
				}
			}

			$foreign_key = $table.'_'.$this->primary_key;
		}

		return $prefix_table.$foreign_key;
	}

	/**
	 * This uses alphabetical comparison to choose the name of the table.
	 *
	 * Example: The joining table of users and roles would be roles_users,
	 * because "r" comes before "u". Joining products and categories would
	 * result in categories_products, because "c" comes before "p".
	 *
	 * Example: zoo > zebra > robber > ocean > angel > aardvark
	 *
	 * @param   string  table name
	 * @return  string
	 */
	public function join_table($table)
	{
		if ($this->table_name > $table)
		{
			$table = $table.'_'.$this->table_name;
		}
		else
		{
			$table = $this->table_name.'_'.$table;
		}

		return $table;
	}

	/**
	 * Returns an ORM model for the given object name;
	 *
	 * @param   string  object name
	 * @return  ORM
	 */
	protected function related_object($object)
	{
		if (isset($this->has_one[$object]))
		{
			$object = ORM::factory($this->has_one[$object]);
		}
		elseif (isset($this->belongs_to[$object]))
		{
			$object = ORM::factory($this->belongs_to[$object]);
		}
		elseif (in_array($object, $this->has_one) OR in_array($object, $this->belongs_to))
		{
			$object = ORM::factory($object);
		}
		else
		{
			return FALSE;
		}

		return $object;
	}

	/**
	 * Loads an array of values into into the current object.
	 *
	 * @chainable
	 * @param   array  values to load
	 * @return  ORM
	 */
	public function load_values(array $values)
	{
		// Related objects
		$related = array();

		foreach ($values as $column => $value)
		{
			if (strpos($column, ':') === FALSE)
			{
				if ( ! isset($this->changed[$column]))
				{
					if (isset($this->table_columns[$column]))
					{
						//Update the column, respects __get()
						$this->$column = $value;
					}
				}
			}
			else
			{
				list ($prefix, $column) = explode(':', $column, 2);

				$related[$prefix][$column] = $value;
			}
		}

		if ( ! empty($related))
		{
			foreach ($related as $object => $values)
			{
				// Load the related objects with the values in the result
				$this->related[$object] = $this->related_object($object)->load_values($values);
			}
		}

		return $this;
	}

	/**
	 * Loads an array of values into into the current object.  Only used internally
	 *
	 * @chainable
	 * @param   array  values to load
	 * @param   bool   ignore loading of columns that have been modified
	 * @return  ORM
	 */
	public function _load_values(array $values, $ignore_changed = FALSE)
	{
		if (array_key_exists($this->primary_key, $values))
		{
			if ( ! $ignore_changed)
			{
				// Replace the object and reset the object status
				$this->object = $this->changed = $this->related = array();
			}

			// Set the loaded and saved object status based on the primary key
			$this->_loaded = $this->_saved = ($values[$this->primary_key] !== NULL);
		}

		// Related objects
		$related = array();

		foreach ($values as $column => $value)
		{
			if (strpos($column, ':') === FALSE)
			{
				if ( ! $ignore_changed OR ! isset($this->changed[$column]))
				{
					$this->object[$column] = $value;
				}
			}
			else
			{
				list ($prefix, $column) = explode(':', $column, 2);

				$related[$prefix][$column] = $value;
			}
		}

		if ( ! empty($related))
		{
			foreach ($related as $object => $values)
			{
				// Load the related objects with the values in the result
				$this->related[$object] = $this->related_object($object)->_load_values($values);
			}
		}

		return $this;
	}

	/**
	 * Loads a database result, either as a new object for this model, or as
	 * an iterator for multiple rows.
	 *
	 * @chainable
	 * @param   boolean       return an iterator or load a single row
	 * @param   boolean       ignore loading of columns that have been modified
	 * @return  ORM           for single rows
	 * @return  ORM_Iterator  for multiple rows
	 */
	protected function load_result($array = FALSE, $ignore_changed = FALSE)
	{
		$this->db_builder->from($this->table_name);

		if ($array === FALSE)
		{
			// Only fetch 1 record
			$this->db_builder->limit(1);
		}

		if ( ! isset($this->db_applied['select']))
		{
			// Select all columns by default
			$this->db_builder->select($this->table_name.'.*');
		}

		if ( ! empty($this->load_with))
		{
			foreach ($this->load_with as $alias => $object)
			{
				// Join each object into the results
				if (is_string($alias))
				{
					// Use alias
					$this->with($alias);
				}
				else
				{
					// Use object
					$this->with($object);
				}
			}
		}

		if ( ! isset($this->db_applied['order_by']) AND ! empty($this->sorting))
		{
			$sorting = array();
			foreach ($this->sorting as $column => $direction)
			{
				if (strpos($column, '.') === FALSE)
				{
					// Keeps sorting working properly when using JOINs on
					// tables with columns of the same name
					$column = $this->table_name.'.'.$column;
				}

				$sorting[$column] = $direction;
			}

			// Apply the user-defined sorting
			$this->db_builder->order_by($sorting);
		}

		// Load the result
		$result = $this->db_builder->execute($this->db);
                $this->db_applied = array();

		if ($array === TRUE)
		{
			// Return an iterated result
			return new ORM_Iterator($this, $result);
		}

		if ($result->count() === 1)
		{
			// Load object values
			$this->_load_values($result->as_array()->current(), $ignore_changed);
		}
		else
		{
			// Clear the object, nothing was found
			$this->clear();
		}

		return $this;
	}

	/**
	 * Return an array of all the primary keys of the related table.
	 *
	 * @param   string  table name
	 * @param   object  ORM model to find relations of
	 * @return  array
	 */
	protected function load_relations($table, ORM $model)
	{
		$result = db::select(array('id' => $model->foreign_key($table)))
			->from($table)
			->where($this->foreign_key($table, $table), '=', $this->primary_key_value)
			->execute($this->db)
			->as_object();

		$relations = array();
		foreach ($result as $row)
		{
			$relations[] = $row->id;
		}

		return $relations;
	}

	/**
	 * Returns whether or not primary key is empty
	 *
	 * @return bool
	 */
	protected function empty_primary_key()
	{
		return (empty($this->object[$this->primary_key]) AND $this->object[$this->primary_key] !== '0');
	}

} // End ORM
