<?php defined('SYSPATH') or die('No direct script access.');
/**
 * MySQL database result.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Database_Mysqli_Result_Core extends Database_Result {

	protected $internal_row = 0;

	public function __construct($result, $sql, $link, $return_objects)
	{
		if (is_object($result))
		{
			// True to return objects, false for arrays
			$this->return_objects = $return_objects;

			$this->total_rows = $result->num_rows;
		}
		elseif (is_bool($result))
		{
			if ($result == FALSE)
			{
				throw new Database_Exception('#:errno: :error [ :query ]',
					array(':error' => $link->error,
					':query' => $sql,
					':errno' => $link->errno));
			}
			else
			{
				// It's a DELETE, INSERT, REPLACE, or UPDATE query
				$this->insert_id = $link->insert_id;
				$this->total_rows = $link->affected_rows;
			}
		}

		// Store the result locally
		$this->result = $result;

		$this->sql = $sql;
	}

	public function __destruct()
	{
		if (is_object($this->result))
		{
			$this->result->free();
		}
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

		// Return a nested array of all results
		if (RUNS_MYSQLND)
			return $this->result->fetch_all(MYSQLI_ASSOC);

		$array = array();

		if ($this->total_rows > 0)
		{
			// Seek to the beginning of the result
			$this->result->data_seek(0);

			while ($row = $this->result->fetch_assoc())
			{
				// Add each row to the array
				$array[] = $row;
			}
			$this->internal_row = $this->total_rows;
		}

		return $array;
	}

	public function as_object($class = NULL, $return = FALSE)
	{
		// Return objects of type $class (or stdClass if none given)
		$this->return_objects = ($class !== NULL) ? $class : TRUE;

		if ( ! $return )
		{
			// Return this result object
			return $this;
		}

		// Return a nested array of all results
		$array = array();

		if ($this->total_rows > 0)
		{
			// Seek to the beginning of the result
			$this->result->data_seek(0);

			if (is_string($this->return_objects))
			{
				while ($row = $this->result->fetch_object($this->return_objects))
				{
					// Add each row to the array
					$array[] = $row;
				}
			}
			else
			{
				while ($row = $this->result->fetch_object())
				{
					// Add each row to the array
					$array[] = $row;
				}
			}

			$this->internal_row = $this->total_rows;
		}

		return $array;
	}

	/**
	 * SeekableIterator: seek
	 */
	public function seek($offset)
	{
		if ($this->offsetExists($offset) AND $this->result->data_seek($offset))
		{
			// Set the current row to the offset
			$this->current_row = $offset;

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Iterator: current
	 */
	public function current()
	{
		if ($this->current_row !== $this->internal_row AND ! $this->seek($this->current_row))
			return NULL;

		++$this->internal_row;

		if ($this->return_objects)
		{
			if (is_string($this->return_objects))
			{
				return $this->result->fetch_object($this->return_objects);
			}
			else
			{
				return $this->result->fetch_object();
			}
		}
		else
		{
			// Return an array of the row
			return $this->result->fetch_assoc();
		}
	}

} // End Database_MySQLi_Result