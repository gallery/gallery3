<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Cached database result.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Database_Cache_Result_Core extends Database_Result {

	/**
	 * Result data (array of rows)
	 * @var array
	 */
	protected $data;

	public function __construct($data, $sql, $return_objects)
	{
		$this->data           = $data;
		$this->sql            = $sql;
		$this->total_rows     = count($data);
		$this->return_objects = $return_objects;
	}

	public function __destruct()
	{
		// Not used
	}

	public function as_array($return = FALSE)
	{
		// Return arrays rather than objects
		$this->return_objects = FALSE;

		if ( ! $return )
		{
			// Return this result object
			return $this;
		}

		// Return the entire array of rows
		return $this->data;
	}

	public function as_object($class = NULL, $return = FALSE)
	{
		if ($class !== NULL)
			throw new Database_Exception('Database cache results do not support object casting');

		// Return objects of type $class (or stdClass if none given)
		$this->return_objects = TRUE;

		return $this;
	}

	public function seek($offset)
	{
		if ( ! $this->offsetExists($offset))
			return FALSE;

		$this->current_row = $offset;

		return TRUE;
	}

	public function current()
	{
		if ($this->return_objects)
		{
			// Return a new object with the current row of data
			return (object) $this->data[$this->current_row];
		}
		else
		{
			// Return an array of the row
			return $this->data[$this->current_row];
		}
	}

} // End Database_Cache_Result