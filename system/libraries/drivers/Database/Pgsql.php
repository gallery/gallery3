<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * PostgreSQL 8.1+ Database Driver
 *
 * $Id: Pgsql.php 4344 2009-05-11 16:41:39Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Pgsql_Driver extends Database_Driver {

	// Database connection link
	protected $link;
	protected $db_config;

	/**
	 * Sets the config for the class.
	 *
	 * @param  array  database configuration
	 */
	public function __construct($config)
	{
		$this->db_config = $config;

		Kohana::log('debug', 'PgSQL Database Driver Initialized');
	}

	public function connect()
	{
		// Check if link already exists
		if (is_resource($this->link))
			return $this->link;

		// Import the connect variables
		extract($this->db_config['connection']);

		// Persistent connections enabled?
		$connect = ($this->db_config['persistent'] == TRUE) ? 'pg_pconnect' : 'pg_connect';

		// Build the connection info
		$port = isset($port) ? 'port=\''.$port.'\'' : '';
		$host = isset($host) ? 'host=\''.$host.'\' '.$port : ''; // if no host, connect with the socket

		$connection_string = $host.' dbname=\''.$database.'\' user=\''.$user.'\' password=\''.$pass.'\'';
		// Make the connection and select the database
		if ($this->link = $connect($connection_string))
		{
			if ($charset = $this->db_config['character_set'])
			{
				echo $this->set_charset($charset);
			}

			// Clear password after successful connect
			$this->db_config['connection']['pass'] = NULL;

			return $this->link;
		}

		return FALSE;
	}

	public function query($sql)
	{
		// Only cache if it's turned on, and only cache if it's not a write statement
		if ($this->db_config['cache'] AND ! preg_match('#\b(?:INSERT|UPDATE|SET)\b#i', $sql))
		{
			$hash = $this->query_hash($sql);

			if ( ! isset($this->query_cache[$hash]))
			{
				// Set the cached object
				$this->query_cache[$hash] = new Pgsql_Result(pg_query($this->link, $sql), $this->link, $this->db_config['object'], $sql);
			}
			else
			{
				// Rewind cached result
				$this->query_cache[$hash]->rewind();
			}

			return $this->query_cache[$hash];
		}

		// Suppress warning triggered when a database error occurs (e.g., a constraint violation)
		return new Pgsql_Result(@pg_query($this->link, $sql), $this->link, $this->db_config['object'], $sql);
	}

	public function set_charset($charset)
	{
		$this->query('SET client_encoding TO '.pg_escape_string($this->link, $charset));
	}

	public function escape_table($table)
	{
		if (!$this->db_config['escape'])
			return $table;

		return '"'.str_replace('.', '"."', $table).'"';
	}

	public function escape_column($column)
	{
		if (!$this->db_config['escape'])
			return $column;

		if ($column == '*')
			return $column;

		// This matches any functions we support to SELECT.
		if ( preg_match('/(avg|count|sum|max|min)\(\s*(.*)\s*\)(\s*as\s*(.+)?)?/i', $column, $matches))
		{
			if ( count($matches) == 3)
			{
				return $matches[1].'('.$this->escape_column($matches[2]).')';
			}
			else if ( count($matches) == 5)
			{
				return $matches[1].'('.$this->escape_column($matches[2]).') AS '.$this->escape_column($matches[2]);
			}
		}

		// This matches any modifiers we support to SELECT.
		if ( ! preg_match('/\b(?:all|distinct)\s/i', $column))
		{
			if (stripos($column, ' AS ') !== FALSE)
			{
				// Force 'AS' to uppercase
				$column = str_ireplace(' AS ', ' AS ', $column);

				// Runs escape_column on both sides of an AS statement
				$column = array_map(array($this, __FUNCTION__), explode(' AS ', $column));

				// Re-create the AS statement
				return implode(' AS ', $column);
			}

			return preg_replace('/[^.*]+/', '"$0"', $column);
		}

		$parts = explode(' ', $column);
		$column = '';

		for ($i = 0, $c = count($parts); $i < $c; $i++)
		{
			// The column is always last
			if ($i == ($c - 1))
			{
				$column .= preg_replace('/[^.*]+/', '"$0"', $parts[$i]);
			}
			else // otherwise, it's a modifier
			{
				$column .= $parts[$i].' ';
			}
		}
		return $column;
	}

	public function regex($field, $match, $type, $num_regexs)
	{
		$prefix = ($num_regexs == 0) ? '' : $type;

		return $prefix.' '.$this->escape_column($field).' ~* \''.$this->escape_str($match).'\'';
	}

	public function notregex($field, $match, $type, $num_regexs)
	{
		$prefix = $num_regexs == 0 ? '' : $type;

		return $prefix.' '.$this->escape_column($field).' !~* \''.$this->escape_str($match) . '\'';
	}

	public function limit($limit, $offset = 0)
	{
		return 'LIMIT '.$limit.' OFFSET '.$offset;
	}

	public function compile_select($database)
	{
		$sql = ($database['distinct'] == TRUE) ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= (count($database['select']) > 0) ? implode(', ', $database['select']) : '*';

		if (count($database['from']) > 0)
		{
			$sql .= "\nFROM ";
			$sql .= implode(', ', $database['from']);
		}

		if (count($database['join']) > 0)
		{
			foreach($database['join'] AS $join)
			{
				$sql .= "\n".$join['type'].'JOIN '.implode(', ', $join['tables']).' ON '.$join['conditions'];
			}
		}

		if (count($database['where']) > 0)
		{
			$sql .= "\nWHERE ";
		}

		$sql .= implode("\n", $database['where']);

		if (count($database['groupby']) > 0)
		{
			$sql .= "\nGROUP BY ";
			$sql .= implode(', ', $database['groupby']);
		}

		if (count($database['having']) > 0)
		{
			$sql .= "\nHAVING ";
			$sql .= implode("\n", $database['having']);
		}

		if (count($database['orderby']) > 0)
		{
			$sql .= "\nORDER BY ";
			$sql .= implode(', ', $database['orderby']);
		}

		if (is_numeric($database['limit']))
		{
			$sql .= "\n";
			$sql .= $this->limit($database['limit'], $database['offset']);
		}

		return $sql;
	}

	public function escape_str($str)
	{
		if (!$this->db_config['escape'])
			return $str;

		is_resource($this->link) or $this->connect();

		return pg_escape_string($this->link, $str);
	}

	public function list_tables()
	{
		$sql    = 'SELECT table_schema || \'.\' || table_name FROM information_schema.tables WHERE table_schema NOT IN (\'pg_catalog\', \'information_schema\')';
		$result = $this->query($sql)->result(FALSE, PGSQL_ASSOC);

		$retval = array();
		foreach ($result as $row)
		{
			$retval[] = current($row);
		}

		return $retval;
	}

	public function show_error()
	{
		return pg_last_error($this->link);
	}

	public function list_fields($table)
	{
		$result = NULL;

		foreach ($this->field_data($table) as $row)
		{
			// Make an associative array
			$result[$row->column_name] = $this->sql_type($row->data_type);

			if (!strncmp($row->column_default, 'nextval(', 8))
			{
				$result[$row->column_name]['sequenced'] = TRUE;
			}

			if ($row->is_nullable === 'YES')
			{
				$result[$row->column_name]['null'] = TRUE;
			}
		}

		if (!isset($result))
			throw new Kohana_Database_Exception('database.table_not_found', $table);

		return $result;
	}

	public function field_data($table)
	{
		// http://www.postgresql.org/docs/8.3/static/infoschema-columns.html
		$result = $this->query('
			SELECT column_name, column_default, is_nullable, data_type, udt_name,
				character_maximum_length, numeric_precision, numeric_precision_radix, numeric_scale
			FROM information_schema.columns
			WHERE table_name = \''. $this->escape_str($table) .'\'
			ORDER BY ordinal_position
		');

		return $result->result_array(TRUE);
	}

} // End Database_Pgsql_Driver Class

/**
 * PostgreSQL Result
 */
class Pgsql_Result extends Database_Result {

	// Data fetching types
	protected $fetch_type  = 'pgsql_fetch_object';
	protected $return_type = PGSQL_ASSOC;

	/**
	 * Sets up the result variables.
	 *
	 * @param  resource  query result
	 * @param  resource  database link
	 * @param  boolean   return objects or arrays
	 * @param  string    SQL query that was run
	 */
	public function __construct($result, $link, $object = TRUE, $sql)
	{
		$this->link = $link;
		$this->result = $result;

		// If the query is a resource, it was a SELECT, SHOW, DESCRIBE, EXPLAIN query
		if (is_resource($result))
		{
			// Its an DELETE, INSERT, REPLACE, or UPDATE query
			if (preg_match('/^(?:delete|insert|replace|update)\b/iD', trim($sql), $matches))
			{
				$this->insert_id  = (strtolower($matches[0]) == 'insert') ? $this->insert_id() : FALSE;
				$this->total_rows = pg_affected_rows($this->result);
			}
			else
			{
				$this->current_row = 0;
				$this->total_rows  = pg_num_rows($this->result);
				$this->fetch_type = ($object === TRUE) ? 'pg_fetch_object' : 'pg_fetch_array';
			}
		}
		else
		{
			throw new Kohana_Database_Exception('database.error', pg_last_error().' - '.$sql);
		}

		// Set result type
		$this->result($object);

		// Store the SQL
		$this->sql = $sql;
	}

	/**
	 * Magic __destruct function, frees the result.
	 */
	public function __destruct()
	{
		if (is_resource($this->result))
		{
			pg_free_result($this->result);
		}
	}

	public function result($object = TRUE, $type = PGSQL_ASSOC)
	{
		$this->fetch_type = ((bool) $object) ? 'pg_fetch_object' : 'pg_fetch_array';

		// This check has to be outside the previous statement, because we do not
		// know the state of fetch_type when $object = NULL
		// NOTE - The class set by $type must be defined before fetching the result,
		// autoloading is disabled to save a lot of stupid overhead.
		if ($this->fetch_type == 'pg_fetch_object')
		{
			$this->return_type = (is_string($type) AND Kohana::auto_load($type)) ? $type : 'stdClass';
		}
		else
		{
			$this->return_type = $type;
		}

		return $this;
	}

	public function as_array($object = NULL, $type = PGSQL_ASSOC)
	{
		return $this->result_array($object, $type);
	}

	public function result_array($object = NULL, $type = PGSQL_ASSOC)
	{
		$rows = array();

		if (is_string($object))
		{
			$fetch = $object;
		}
		elseif (is_bool($object))
		{
			if ($object === TRUE)
			{
				$fetch = 'pg_fetch_object';

				// NOTE - The class set by $type must be defined before fetching the result,
				// autoloading is disabled to save a lot of stupid overhead.
				$type = (is_string($type) AND Kohana::auto_load($type)) ? $type : 'stdClass';
			}
			else
			{
				$fetch = 'pg_fetch_array';
			}
		}
		else
		{
			// Use the default config values
			$fetch = $this->fetch_type;

			if ($fetch == 'pg_fetch_object')
			{
				$type = (is_string($type) AND Kohana::auto_load($type)) ? $type : 'stdClass';
			}
		}

		if ($this->total_rows)
		{
			pg_result_seek($this->result, 0);

			while ($row = $fetch($this->result, NULL, $type))
			{
				$rows[] = $row;
			}
		}

		return $rows;
	}

	public function insert_id()
	{
		if ($this->insert_id === NULL)
		{
			$query = 'SELECT LASTVAL() AS insert_id';

			// Disable error reporting for this, just to silence errors on
			// tables that have no serial column.
			$ER = error_reporting(0);

			$result = pg_query($this->link, $query);
			$insert_id = pg_fetch_array($result, NULL, PGSQL_ASSOC);

			$this->insert_id = $insert_id['insert_id'];

			// Reset error reporting
			error_reporting($ER);
		}

		return $this->insert_id;
	}

	public function seek($offset)
	{
		if ($this->offsetExists($offset) AND pg_result_seek($this->result, $offset))
		{
			// Set the current row to the offset
			$this->current_row = $offset;

			return TRUE;
		}

		return FALSE;
	}

	public function list_fields()
	{
		$field_names = array();

		$fields = pg_num_fields($this->result);
		for ($i = 0; $i < $fields; ++$i)
		{
			$field_names[] = pg_field_name($this->result, $i);
		}

		return $field_names;
	}

	/**
	 * ArrayAccess: offsetGet
	 */
	public function offsetGet($offset)
	{
		if ( ! $this->seek($offset))
			return FALSE;

		// Return the row by calling the defined fetching callback
		$fetch = $this->fetch_type;
		return $fetch($this->result, NULL, $this->return_type);
	}

} // End Pgsql_Result Class

/**
 * PostgreSQL Prepared Statement (experimental)
 */
class Kohana_Pgsql_Statement {

	protected $link = NULL;
	protected $stmt;

	public function __construct($sql, $link)
	{
		$this->link = $link;

		$this->stmt = $this->link->prepare($sql);

		return $this;
	}

	public function __destruct()
	{
		$this->stmt->close();
	}

	// Sets the bind parameters
	public function bind_params()
	{
		$argv = func_get_args();
		return $this;
	}

	// sets the statement values to the bound parameters
	public function set_vals()
	{
		return $this;
	}

	// Runs the statement
	public function execute()
	{
		return $this;
	}
}
