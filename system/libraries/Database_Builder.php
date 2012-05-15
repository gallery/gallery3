<?php defined('SYSPATH') or die('No direct script access.');
/**
 * The Database Query Builder provides methods for creating database agnostic queries and
 * data manipulation.
 *
 * ##### A basic select query
 *
 *     $builder = new Database_Builder;
 *     $kohana = $builder
 *                 ->select()
 *                 ->where('name', '=', 'Kohana')
 *                 ->from('frameworks')
 *                 ->execute();
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Database_Builder_Core {

	// Valid ORDER BY directions
	protected $order_directions = array('ASC', 'DESC', 'RAND()');

	// Database object
	protected $db;

	// Builder members
	protected $select   = array();
	protected $from     = array();
	protected $join     = array();
	protected $where    = array();
	protected $group_by = array();
	protected $having   = array();
	protected $order_by = array();
	protected $limit    = NULL;
	protected $offset   = NULL;
	protected $set      = array();
	protected $columns  = array();
	protected $values   = array();
	protected $type;
	protected $distinct = FALSE;
	protected $reset    = TRUE;

	// TTL for caching (using Cache library)
	protected $ttl      = FALSE;

	public function __construct($db = 'default')
	{
		$this->db = $db;
	}

	/**
	 * Compiles the builder object into a SQL query. Useful for debugging
	 *
	 * ##### Example
	 *
	 *     echo $builder->select()->from('products');
	 *     // Output: SELECT * FROM `products`
	 *
	 * @return  string Compiled query
	 */
	public function __toString()
	{
		return $this->compile();
	}

	/**
	 * Creates a `SELECT` query with support for column aliases, database functions,
	 * subqueries or a [Database_Expression]
	 *
	 * ##### Examples
	 *
	 *     // Simple select
	 *     echo $builder->select()->from('products');
	 *
	 *     // Select with database function
	 *     echo $builder->select(array('records_found' => 'COUNT("*")'))->from('products');
	 *
	 *     // Select with sub query
	 *     echo $builder->select(array('field', 'test' => db::select('test')->from('table')))->from('products');
	 *
	 * @chainable
	 * @param   string|array    column name or array(alias => column)
	 * @return  Database_Builder
	 */
	public function select($columns = NULL)
	{
		$this->type = Database::SELECT;

		if ($columns === NULL)
		{
			$columns = array('*');
		}
		elseif ( ! is_array($columns))
		{
			$columns = func_get_args();
		}

		$this->select = array_merge($this->select, $columns);

		return $this;
	}

	/**
	 * Creates a `DISTINCT SELECT` query. For more information see see [Database_Builder::select].
	 *
	 * @chainable
	 * @param   string|array    column name or array(alias => column)
	 * @return  Database_Builder
	 */
	public function select_distinct($columns = NULL)
	{
		$this->select($columns);
		$this->distinct = TRUE;
		return $this;
	}

	/**
	 * Add tables to the FROM portion of the builder
	 *
	 * ##### Example
	 *
	 *     $builder->select()->from('products')
	 *             ->from(array('other' => 'other_table'));
	 *     // Output: SELECT * FROM `products`, `other_table` AS `other`
	 *
	 * @chainable
	 * @param   string|array    table name or array(alias => table)
	 * @return  Database_Builder
	 */
	public function from($tables)
	{
		if ( ! is_array($tables))
		{
			$tables = func_get_args();
		}

		$this->from = array_merge($this->from, $tables);

		return $this;
	}

	/**
	 * Add conditions to the `WHERE` clause. Alias for [Database_Builder::and_where].
	 *
	 * @chainable
	 * @param  mixed   Column name or array of columns => vals
	 * @param  string  Operation to perform
	 * @param  mixed   Value
	 * @return Database_Builder
	 */
	public function where($columns, $op = '=', $value = NULL)
	{
		return $this->and_where($columns, $op, $value);
	}

	/**
	 * Add conditions to the `WHERE` clause separating multiple conditions with `AND`.
	 * This function supports all `WHERE` operators including `LIKE` and `IN`. It can
	 * also be used with a [Database_Expression] or subquery.
	 *
	 * ##### Examples
	 *
	 *     // Basic where condition
	 *     $builder->where('field', '=', 'value');
	 *
	 *     // Multiple conditions with an array (you can also chain where() function calls)
	 *     $builder->where(array(array('field', '=', 'value'), array(...)));
	 *
	 *     // With a database expression
	 *     $builder->where('field', '=', db::expr('field + 1'));
	 *     // or a function
	 *     $builder->where('field', '=', db::expr('UNIX_TIMESTAMP()'));
	 *
	 *     // With a subquery
	 *     $builder->where('field', 'IN', db::select('id')->from('table'));
	 *
	 * [!!] You must manually escape all data you pass into a database expression!
	 *
	 * @chainable
	 * @param  mixed   Column name or array of triplets
	 * @param  string  Operation to perform
	 * @param  mixed   Value
	 * @return Database_Builder
	 */
	public function and_where($columns, $op = '=', $value = NULL)
	{
		if (is_array($columns))
		{
			foreach ($columns as $column)
			{
				$this->where[] = array('AND' => $column);
			}
		}
		else
		{
			$this->where[] = array('AND' => array($columns, $op, $value));
		}
		return $this;
	}

	/**
	 * Add conditions to the `WHERE` clause separating multiple conditions with `OR`.
	 * For more information about building a `WHERE` clause see [Database_Builder::and_where]
	 *
	 * @chainable
	 * @param  mixed   Column name or array of triplets
	 * @param  string  Operation to perform
	 * @param  mixed   Value
	 * @return Database_Builder
	 */
	public function or_where($columns, $op = '=', $value = NULL)
	{
		if (is_array($columns))
		{
			foreach ($columns as $column)
			{
				$this->where[] = array('OR' => $column);
			}
		}
		else
		{
			$this->where[] = array('OR' => array($columns, $op, $value));
		}
		return $this;
	}

	/**
	 * Join tables to the builder
	 *
	 * ##### Example
	 *
	 *     // Basic join
	 *     db::select()->from('products')
	 *                 ->join('reviews', 'reviews.product_id', 'products.id');
	 *
	 *     // Advanced joins
	 *     echo db::select()->from('products')
	 *                      ->join('reviews', 'field', db::expr('advanced condition here'), 'RIGHT');
	 *
	 * @chainable
	 * @param  mixed   Table name
	 * @param  mixed   Key, or an array of key => value pair, for join condition (can be a Database_Expression)
	 * @param  mixed   Value if $keys is not an array or Database_Expression
	 * @param  string  Join type (LEFT, RIGHT, INNER, etc.)
	 * @return Database_Builder
	 */
	public function join($table, $keys, $value = NULL, $type = NULL)
	{
		if (is_string($keys))
		{
			$keys = array($keys => $value);
		}

		if ($type !== NULL)
		{
			$type = strtoupper($type);
		}

		$this->join[] = array($table, $keys, $type);

		return $this;
	}

	/**
	 * This function is an alias for [Database_Builder::join]
	 * with the join type set to `LEFT`.
	 *
	 * @chainable
	 * @param  mixed   Table name
	 * @param  mixed   Key, or an array of key => value pair, for join condition (can be a Database_Expression)
	 * @param  mixed   Value if $keys is not an array or Database_Expression
	 * @return Database_Builder
	 */
	public function left_join($table, $keys, $value = NULL)
	{
		return $this->join($table, $keys, $value, 'LEFT');
	}

	/**
	 * This function is an alias for [Database_Builder::join]
	 * with the join type set to `RIGHT`.
	 *
	 * @chainable
	 * @param  mixed   Table name
	 * @param  mixed   Key, or an array of key => value pair, for join condition (can be a Database_Expression)
	 * @param  mixed   Value if $keys is not an array or Database_Expression
	 * @return Database_Builder
	 */
	public function right_join($table, $keys, $value = NULL)
	{
		return $this->join($table, $keys, $value, 'RIGHT');
	}

	/**
	 * This function is an alias for [Database_Builder::join]
	 * with the join type set to `INNER`.
	 *
	 * @chainable
	 * @param  mixed   Table name
	 * @param  mixed   Key, or an array of key => value pair, for join condition (can be a Database_Expression)
	 * @param  mixed   Value if $keys is not an array or Database_Expression
	 * @return Database_Builder
	 */
	public function inner_join($table, $keys, $value = NULL)
	{
		return $this->join($table, $keys, $value, 'INNER');
	}

	/**
	 * This function is an alias for [Database_Builder::join]
	 * with the join type set to `OUTER`.
	 *
	 * @chainable
	 * @param  mixed   Table name
	 * @param  mixed   Key, or an array of key => value pair, for join condition (can be a Database_Expression)
	 * @param  mixed   Value if $keys is not an array or Database_Expression
	 * @return Database_Builder
	 */
	public function outer_join($table, $keys, $value = NULL)
	{
		return $this->join($table, $keys, $value, 'OUTER');
	}

	/**
	 * This function is an alias for [Database_Builder::join]
	 * with the join type set to `FULL`.
	 *
	 * @chainable
	 * @param  mixed   Table name
	 * @param  mixed   Key, or an array of key => value pair, for join condition (can be a Database_Expression)
	 * @param  mixed   Value if $keys is not an array or Database_Expression
	 * @return Database_Builder
	 */
	public function full_join($table, $keys, $value = NULL)
	{
		return $this->join($table, $keys, $value, 'FULL');
	}

	/**
	 * This function is an alias for [Database_Builder::join]
	 * with the join type set to `LEFT INNER`.
	 *
	 * @chainable
	 * @param  mixed   Table name
	 * @param  mixed   Key, or an array of key => value pair, for join condition (can be a Database_Expression)
	 * @param  mixed   Value if $keys is not an array or Database_Expression
	 * @return Database_Builder
	 */
	public function left_inner_join($table, $keys, $value = NULL)
	{
		return $this->join($table, $keys, $value, 'LEFT INNER');
	}

	/**
	 * This function is an alias for [Database_Builder::join]
	 * with the join type set to `RIGHT INNER`.
	 *
	 * @chainable
	 * @param  mixed   Table name
	 * @param  mixed   Key, or an array of key => value pair, for join condition (can be a Database_Expression)
	 * @param  mixed   Value if $keys is not an array or Database_Expression
	 * @return Database_Builder
	 */
	public function right_inner_join($table, $keys, $value = NULL)
	{
		return $this->join($table, $keys, $value, 'RIGHT INNER');
	}

	/**
	 * Add fields to the GROUP BY portion
	 *
	 * ##### Example
	 *
	 *     db::select()->from('products')
	 *                 ->group_by(array('name', 'cat_id'));
	 *     // Output: SELECT * FROM `products` GROUP BY `name`, `cat_id`
	 *
	 * @chainable
	 * @param  mixed  Field names or an array of fields
	 * @return Database_Builder
	 */
	public function group_by($columns)
	{
		if ( ! is_array($columns))
		{
			$columns = func_get_args();
		}

		$this->group_by = array_merge($this->group_by, $columns);

		return $this;
	}

	/**
	 * Add conditions to the HAVING clause (AND)
	 *
	 * @chainable
	 * @param  mixed   Column name or array of columns => vals
	 * @param  string  Operation to perform
	 * @param  mixed   Value
	 * @return Database_Builder
	 */
	public function having($columns, $op = '=', $value = NULL)
	{
		return $this->and_having($columns, $op, $value);
	}

	/**
	 * Add conditions to the HAVING clause (AND)
	 *
	 * @chainable
	 * @param  mixed   Column name or array of triplets
	 * @param  string  Operation to perform
	 * @param  mixed   Value
	 * @return Database_Builder
	 */
	public function and_having($columns, $op = '=', $value = NULL)
	{
		if (is_array($columns))
		{
			foreach ($columns as $column)
			{
				$this->having[] = array('AND' => $column);
			}
		}
		else
		{
			$this->having[] = array('AND' => array($columns, $op, $value));
		}
		return $this;
	}

	/**
	 * Add conditions to the HAVING clause (OR)
	 *
	 * @chainable
	 * @param  mixed   Column name or array of triplets
	 * @param  string  Operation to perform
	 * @param  mixed   Value
	 * @return Database_Builder
	 */
	public function or_having($columns, $op = '=', $value = NULL)
	{
		if (is_array($columns))
		{
			foreach ($columns as $column)
			{
				$this->having[] = array('OR' => $column);
			}
		}
		else
		{
			$this->having[] = array('OR' => array($columns, $op, $value));
		}
		return $this;
	}

	/**
	 * Add fields to the ORDER BY portion
	 *
	 * @chainable
	 * @param  mixed   Field names or an array of fields (field => direction)
	 * @param  string  Direction or NULL for ascending
	 * @return Database_Builder
	 */
	public function order_by($columns, $direction = NULL)
	{
		if (is_array($columns))
		{
			foreach ($columns as $column => $direction)
			{
				if (is_string($column))
				{
					$this->order_by[] = array($column => $direction);
				}
				else
				{
					// $direction is the column name when the array key is numeric
					$this->order_by[] = array($direction => NULL);
				}
			}
		}
		else
		{
			$this->order_by[] = array($columns => $direction);
		}
		return $this;
	}

	/**
	 * Limit rows returned
	 *
	 * @chainable
	 * @param  int  Number of rows
	 * @return Database_Builder
	 */
	public function limit($number)
	{
		$this->limit = (int) $number;

		return $this;
	}

	/**
	 * Offset into result set
	 *
	 * @chainable
	 * @param  int  Offset
	 * @return Database_Builder
	 */
	public function offset($number)
	{
		$this->offset = (int) $number;

		return $this;
	}

	/**
	 * Alias for [Database_Builder::and_open]
	 *
	 * @chainable
	 * @param  string  Clause (WHERE OR HAVING)
	 * @return Database_Builder
	 */
	public function open($clause = 'WHERE')
	{
		return $this->and_open($clause);
	}

	/**
	 * Open new **ANDs** parenthesis set
	 *
	 * @chainable
	 * @param  string  Clause (WHERE OR HAVING)
	 * @return Database_Builder
	 */
	public function and_open($clause = 'WHERE')
	{
		if ($clause === 'WHERE')
		{
			$this->where[] = array('AND' => '(');
		}
		else
		{
			$this->having[] = array('AND' => '(');
		}

		return $this;
	}

	/**
	 * Open new **OR** parenthesis set
	 *
	 * @chainable
	 * @param  string  Clause (WHERE OR HAVING)
	 * @return Database_Builder
	 */
	public function or_open($clause = 'WHERE')
	{
		if ($clause === 'WHERE')
		{
			$this->where[] = array('OR' => '(');
		}
		else
		{
			$this->having[] = array('OR' => '(');
		}

		return $this;
	}

	/**
	 * Close close parenthesis set
	 *
	 * @chainable
	 * @param  string  Clause (WHERE OR HAVING)
	 * @return Database_Builder
	 */
	public function close($clause = 'WHERE')
	{
		if ($clause === 'WHERE')
		{
			$this->where[] = array(')');
		}
		else
		{
			$this->having[] = array(')');
		}

		return $this;
	}

	/**
	 * Set values for UPDATE
	 *
	 * @chainable
	 * @param  mixed   Column name or array of columns => vals
	 * @param  mixed   Value (can be a Database_Expression)
	 * @return Database_Builder
	 */
	public function set($keys, $value = NULL)
	{
		if (is_string($keys))
		{
			$keys = array($keys => $value);
		}

		$this->set = array_merge($keys, $this->set);

		return $this;
	}

	/**
	 * Columns used for INSERT queries
	 *
	 * @chainable
	 * @param  array  Columns
	 * @return Database_Builder
	 */
	public function columns($columns)
	{
		if ( ! is_array($columns))
		{
			$columns = func_get_args();
		}

		$this->columns = $columns;

		return $this;
	}

	/**
	 * Values used for INSERT queries
	 *
	 * @chainable
	 * @param  array  Values
	 * @return Database_Builder
	 */
	public function values($values)
	{
		if ( ! is_array($values))
		{
			$values = func_get_args();
		}

		$this->values[] = $values;

		return $this;
	}

	/**
	 * Set caching for the query
	 *
	 * @chainable
	 * @param  mixed  Time-to-live (FALSE to disable, NULL for Cache default, seconds otherwise)
	 * @return Database_Builder
	 */
	public function cache($ttl = NULL)
	{
		$this->ttl = $ttl;

		return $this;
	}

	/**
	 * Resets the database builder after execution. By default after you `execute()` a query
	 * the database builder will reset to its default state. You can use `reset(FALSE)`
	 * to stop this from happening. This is useful for pagination when you might want to
	 * apply a limit to the previous query.
	 *
	 * ##### Example
	 *
	 *     $db = new Database_Builder;
	 *     $all_results = $db->select()
	 *                        ->where('id', '=', 3)
	 *                        ->from('products')
	 *                        ->reset(FALSE)
	 *                        ->execute();
	 *
	 *     // Run the query again with a limit of 10
	 *     $ten_results = $db->limit(10)
	 *                       ->execute();
	 * @chainable
	 * @param   bool reset builder
	 * @return  Database_Builder
	 */
	public function reset($reset = TRUE)
	{
		$this->reset = (bool) $reset;
		return $this;
	}

	/**
	 * Compiles the given clause's conditions
	 *
	 * @param  array  Clause conditions
	 * @return string
	 */
	protected function compile_conditions($groups)
	{
		$last_condition = NULL;

		$sql = '';
		foreach ($groups as $group)
		{
			// Process groups of conditions
			foreach ($group as $logic => $condition)
			{
				if ($condition === '(')
				{
					if ( ! empty($sql) AND $last_condition !== '(')
					{
						// Include logic operator
						$sql .= ' '.$logic.' ';
					}

					$sql .= '(';
				}
				elseif ($condition === ')')
				{
					$sql .= ')';
				}
				else
				{
					list($columns, $op, $value) = $condition;

					// Stores each individual condition
					$vals = array();

					if ($columns instanceof Database_Expression)
					{
						// Add directly to condition list
						$vals[] = (string) $columns;
					}
					else
					{
						$op = strtoupper($op);

						if ( ! is_array($columns))
						{
							$columns = array($columns => $value);
						}

						foreach ($columns as $column => $value)
						{
							if ($value instanceof Database_Builder)
							{
								// Using a subquery
								$value->db = $this->db;
								$value = '('.(string) $value.')';
							}
							elseif (is_array($value))
							{
								if ($op === 'BETWEEN' OR $op === 'NOT BETWEEN')
								{
									// Falls between two values
									$value = $this->db->quote($value[0]).' AND '.$this->db->quote($value[1]);
								}
								else
								{
									// Return as list
									$value = array_map(array($this->db, 'quote'), $value);
									$value = '('.implode(', ', $value).')';
								}
							}
							else
							{
								$value = $this->db->quote($value);
							}

							if ( ! empty($column))
							{
								// Ignore blank columns
								$column = $this->db->quote_column($column);
							}

							// Add to condition list
							$vals[] = $column.' '.$op.' '.$value;
						}
					}

					if ( ! empty($sql) AND $last_condition !== '(')
					{
						// Add the logic operator
						$sql .= ' '.$logic.' ';
					}

					// Join the condition list items together by the given logic operator
					$sql .= implode(' '.$logic.' ', $vals);
				}

				$last_condition = $condition;
			}
		}

		return $sql;
	}

	/**
	 * Compiles the columns portion of the query for INSERT
	 *
	 * @return string
	 */
	protected function compile_columns()
	{
		return '('.implode(', ', array_map(array($this->db, 'quote_column'), $this->columns)).')';
	}

	/**
	 * Compiles the VALUES portion of the query for INSERT
	 *
	 * @return string
	 */
	protected function compile_values()
	{
		$values = array();
		foreach ($this->values as $group)
		{
			// Each set of values to be inserted
			$values[] = '('.implode(', ', array_map(array($this->db, 'quote'), $group)).')';
		}

		return implode(', ', $values);
	}

	/**
	 * Create an UPDATE query
	 *
	 * @chainable
	 * @param  string  Table name
	 * @param  array   Array of Keys => Values
	 * @param  array   WHERE conditions
	 * @return Database_Builder
	 */
	public function update($table = NULL, $set = NULL, $where = NULL)
	{
		$this->type = Database::UPDATE;

		if (is_array($set))
		{
			$this->set($set);
		}

		if ($where !== NULL)
		{
			$this->where($where);
		}

		if ($table !== NULL)
		{
			$this->from($table);
		}

		return $this;
	}

	/**
	 * Create an INSERT query.  Use 'columns' and 'values' methods for multi-row inserts
	 *
	 * @chainable
	 * @param  string  Table name
	 * @param  array   Array of Keys => Values
	 * @return Database_Builder
	 */
	public function insert($table = NULL, $set = NULL)
	{
		$this->type = Database::INSERT;

		if (is_array($set))
		{
			$this->columns(array_keys($set));
			$this->values(array_values($set));
		}

		if ($table !== NULL)
		{
			$this->from($table);
		}

		return $this;
	}

	/**
	 * Create a DELETE query
	 *
	 * @chainable
	 * @param  string  Table name
	 * @param  array   WHERE conditions
	 * @return Database_Builder
	 */
	public function delete($table, $where = NULL)
	{
		$this->type = Database::DELETE;

		if ($where !== NULL)
		{
			$this->where($where);
		}

		if ($table !== NULL)
		{
			$this->from($table);
		}

		return $this;
	}

	/**
	 * Count records for a given table
	 *
	 * @param  string  Table name
	 * @param  array   WHERE conditions
	 * @return int
	 */
	public function count_records($table = FALSE, $where = NULL)
	{
		if (count($this->from) < 1)
		{
			if ($table === FALSE)
				throw new Database_Exception('Database count_records requires a table');

			$this->from($table);
		}

		if ($where !== NULL)
		{
			$this->where($where);
		}

		// Grab the count AS records_found
		$result = $this->select(array('records_found' => 'COUNT("*")'))->execute();

		return $result->get('records_found');
	}

	/**
	 * Executes the built query
	 *
	 * @param  mixed  Database name or object
	 * @return Database_Result
	 */
	public function execute($db = NULL)
	{
		if ($db !== NULL)
		{
			$this->db = $db;
		}

		if ( ! is_object($this->db))
		{
			// Get the database instance
			$this->db = Database::instance($this->db);
		}

		$query = $this->compile();

		if ($this->reset)
		{
			// Reset the query after executing
			$this->_reset();
		}

		if ($this->ttl !== FALSE AND $this->type === Database::SELECT)
		{
			// Return result from cache (only allowed with SELECT)
			return $this->db->query_cache($query, $this->ttl);
		}
		else
		{
			// Load the result (no caching)
			return $this->db->query($query);
		}
	}

	/**
	 * Compiles the builder object into a SQL query
	 *
	 * @return  string Compiled query
	 */
	protected function compile()
	{
		if ( ! is_object($this->db))
		{
			// Use default database for compiling to string if none is given
			$this->db = Database::instance($this->db);
		}

		if ($this->type === Database::SELECT)
		{
			// SELECT columns FROM table
			$sql = $this->distinct ? 'SELECT DISTINCT ' : 'SELECT ';
			$sql .= $this->compile_select();

			if ( ! empty($this->from))
			{
				$sql .= "\nFROM ".$this->compile_from();
			}
		}
		elseif ($this->type === Database::UPDATE)
		{
			$sql = 'UPDATE '.$this->compile_from()."\n".'SET '.$this->compile_set();
		}
		elseif ($this->type === Database::INSERT)
		{
			$sql = 'INSERT INTO '.$this->compile_from()."\n".$this->compile_columns()."\nVALUES ".$this->compile_values();
		}
		elseif ($this->type === Database::DELETE)
		{
			$sql = 'DELETE FROM '.$this->compile_from();
		}

		if ( ! empty($this->join))
		{
			$sql .= $this->compile_join();
		}

		if ( ! empty($this->where))
		{
			$sql .= "\n".'WHERE '.$this->compile_conditions($this->where);
		}

		if ( ! empty($this->group_by))
		{
			$sql .= "\n".'GROUP BY '.$this->compile_group_by();
		}

		if ( ! empty($this->having))
		{
			$sql .= "\n".'HAVING '.$this->compile_conditions($this->having);
		}

		if ( ! empty($this->order_by))
		{
			$sql .= "\nORDER BY ".$this->compile_order_by();
		}

		if (is_int($this->limit))
		{
			$sql .= "\nLIMIT ".$this->limit;
		}

		if (is_int($this->offset))
		{
			$sql .= "\nOFFSET ".$this->offset;
		}

		return $sql;
	}

	/**
	 * Compiles the SELECT portion of the query
	 *
	 * @return string
	 */
	protected function compile_select()
	{
		$vals = array();

		foreach ($this->select as $alias => $name)
		{
			if ($name instanceof Database_Builder)
			{
				// Using a subquery
				$name->db = $this->db;
				$vals[] = '('.(string) $name.') AS '.$this->db->quote_column($alias);
			}
			elseif (is_string($alias))
			{
				$vals[] = $this->db->quote_column($name, $alias);
			}
			else
			{
				$vals[] = $this->db->quote_column($name);
			}
		}

		return implode(', ', $vals);
	}

	/**
	 * Compiles the FROM portion of the query
	 *
	 * @return string
	 */
	protected function compile_from()
	{
		$vals = array();

		foreach ($this->from as $alias => $name)
		{
			if (is_string($alias))
			{
				// Using AS format so escape both
				$vals[] = $this->db->quote_table($name, $alias);
			}
			else
			{
				// Just using the table name itself
				$vals[] = $this->db->quote_table($name);
			}
		}

		return implode(', ', $vals);
	}

	/**
	 * Compiles the JOIN portion of the query
	 *
	 * @return string
	 */
	protected function compile_join()
	{
		$sql = '';
		foreach ($this->join as $join)
		{
			list($table, $keys, $type) = $join;

			if ($type !== NULL)
			{
				// Join type
				$sql .= ' '.$type;
			}

			$sql .= ' JOIN '.$this->db->quote_table($table);

			$condition = '';
			if ($keys instanceof Database_Expression)
			{
				$condition = (string) $keys;
			}
			elseif (is_array($keys))
			{
				// ON condition is an array of matches
				foreach ($keys as $key => $value)
				{
					if ( ! empty($condition))
					{
						$condition .= ' AND ';
					}

					$condition .= $this->db->quote_column($key).' = '.$this->db->quote_column($value);
				}
			}

			if ( ! empty($condition))
			{
				// Add ON condition
				$sql .= ' ON ('.$condition.')';
			}
		}

		return $sql;
	}

	/**
	 * Compiles the GROUP BY portion of the query
	 *
	 * @return string
	 */
	protected function compile_group_by()
	{
		$vals = array();

		foreach ($this->group_by as $column)
		{
			// Escape the column
			$vals[] = $this->db->quote_column($column);
		}

		return implode(', ', $vals);
	}

	/**
	 * Compiles the ORDER BY portion of the query
	 *
	 * @return string
	 */
	protected function compile_order_by()
	{
		$ordering = array();

		foreach ($this->order_by as $column => $order_by)
		{
			list($column, $direction) = each($order_by);

			$column = $this->db->quote_column($column);

			if ($direction !== NULL)
			{
				$direction = ' '.$direction;
			}

			$ordering[] = $column.$direction;
		}

		return implode(', ', $ordering);
	}

	/**
	 * Compiles the SET portion of the query for UPDATE
	 *
	 * @return string
	 */
	protected function compile_set()
	{
		$vals = array();

		foreach ($this->set as $key => $value)
		{
			// Using an UPDATE so Key = Val
			$vals[] = $this->db->quote_column($key).' = '.$this->db->quote($value);
		}

		return implode(', ', $vals);
	}

	/**
	 * Resets all query components
	 */
	protected function _reset()
	{
		$this->select   = array();
		$this->from     = array();
		$this->join     = array();
		$this->where    = array();
		$this->group_by = array();
		$this->having   = array();
		$this->order_by = array();
		$this->limit    = NULL;
		$this->offset   = NULL;
		$this->set      = array();
		$this->values   = array();
		$this->type     = NULL;
		$this->distinct = FALSE;
		$this->reset    = TRUE;
		$this->ttl      = FALSE;
	}

} // End Database_Builder
