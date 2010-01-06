<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Object Relational Mapping (ORM) result iterator.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class ORM_Iterator_Core implements Iterator, ArrayAccess, Countable {

	// Class attributes
	protected $class_name;
	protected $primary_key;
	protected $primary_val;

	// Database result object
	protected $result;

	public function __construct(ORM $model, Database_Result $result)
	{
		// Class attributes
		$this->class_name  = get_class($model);
		$this->primary_key = $model->primary_key;
		$this->primary_val = $model->primary_val;

		// Database result (make sure rows are returned as arrays)
		$this->result = $result;
	}

	/**
	 * Returns an array of the results as ORM objects or a nested array
	 *
	 * @param   bool    TRUE to return an array of ORM objects, FALSE for an array of arrays
	 * @param   string  key column to index on, NULL to ignore
	 * @return  array
	 */
	public function as_array($objects = TRUE, $key = NULL)
	{
		$array = array();

		// Import class name
		$class = $this->class_name;

		if ($objects)
		{
			// Generate an array of objects
			foreach ($this->result as $data)
			{
				if ($key === NULL)
				{
					// No indexing
					$array[] = new $class($data);
				}
				else
				{
					// Index on the given key
					$array[$data->$key] = new $class($data);
				}
			}
		}
		else
		{
			// Generate an array of arrays (and the subarrays may be nested in the case of relationships)
			// This could be done by creating a new ORM object and calling as_array on it, but this is much faster
			foreach ($this->result as $data)
			{
				// Have to do a bit of magic here to handle any relationships and generate a nested array for them
				$temp = array();

				foreach ($data as $key => $val)
				{
					$ptr = & $temp;

					foreach (explode(':', $key) as $subkey)
					{
						// Walk thru the relationships (separated in the key name by a ':')
						// 'user:email:address' will be array['user']['email']['address']
						$ptr = & $ptr[$subkey];
					}

					// Set the value
					$ptr = $val;
				}

				// Append the result
				$array[] = $temp;
			}
		}

		return $array;
	}

	/**
	 * Return an array of all of the primary keys for this object.
	 *
	 * @return  array
	 */
	public function primary_key_array()
	{
		$ids = array();
		foreach ($this->result as $row)
		{
			$ids[] = $row->{$this->primary_key};
		}
		return $ids;
	}

	/**
	 * Create a key/value array from the results.
	 *
	 * @param   string  key column
	 * @param   string  value column
	 * @return  array
	 */
	public function select_list($key = NULL, $val = NULL)
	{
		if ($key === NULL)
		{
			// Use the default key
			$key = $this->primary_key;
		}

		if ($val === NULL)
		{
			// Use the default value
			$val = $this->primary_val;
		}

		$array = array();
		foreach ($this->result as $row)
		{
			$array[$row->$key] = $row->$val;
		}
		return $array;
	}

	/**
	 * Return a range of offsets.
	 *
	 * @param   integer  start
	 * @param   integer  end
	 * @return  array
	 */
	public function range($start, $end)
	{
		// Array of objects
		$array = array();

		if ($this->result->offsetExists($start))
		{
			// Import the class name
			$class = $this->class_name;

			// Set the end offset
			$end = $this->result->offsetExists($end) ? $end : $this->count();

			for ($i = $start; $i < $end; $i++)
			{
				// Insert each object in the range
				$array[] = new $class($this->result->offsetGet($i));
			}
		}

		return $array;
	}

	/**
	 * Countable: count
	 */
	public function count()
	{
		return $this->result->count();
	}

	/**
	 * Iterator: current
	 */
	public function current()
	{
		if ($row = $this->result->current())
		{
			// Import class name
			$class = $this->class_name;

			$row = new $class($row);
		}

		return $row;
	}

	/**
	 * Iterator: key
	 */
	public function key()
	{
		return $this->result->key();
	}

	/**
	 * Iterator: next
	 */
	public function next()
	{
		return $this->result->next();
	}

	/**
	 * Iterator: rewind
	 */
	public function rewind()
	{
		$this->result->rewind();
	}

	/**
	 * Iterator: valid
	 */
	public function valid()
	{
		return $this->result->valid();
	}

	/**
	 * ArrayAccess: offsetExists
	 */
	public function offsetExists($offset)
	{
		return $this->result->offsetExists($offset);
	}

	/**
	 * ArrayAccess: offsetGet
	 */
	public function offsetGet($offset)
	{
		if ($this->result->offsetExists($offset))
		{
			// Import class name
			$class = $this->class_name;

			return new $class($this->result->offsetGet($offset));
		}
	}

	/**
	 * ArrayAccess: offsetSet
	 *
	 * @throws  Kohana_Database_Exception
	 */
	public function offsetSet($offset, $value)
	{
		throw new Kohana_Database_Exception('database.result_read_only');
	}

	/**
	 * ArrayAccess: offsetUnset
	 *
	 * @throws  Kohana_Database_Exception
	 */
	public function offsetUnset($offset)
	{
		throw new Kohana_Database_Exception('database.result_read_only');
	}

} // End ORM Iterator