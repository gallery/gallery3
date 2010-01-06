<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Model base class.
 *
 * $Id: Model.php 4729 2009-12-29 20:35:19Z isaiah $
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Model_Core {

	/**
	 * Creates and returns a new model.
	 *
	 * @param   string   model name
	 * @param   mixed    constructor arguments
	 * @param   boolean  construct the model with multiple arguments
	 * @return  Model
	 */
	public static function factory($name, $args = NULL, $multiple = FALSE)
	{
		// Model class name
		$class = ucfirst($name).'_Model';

		if ($args === NULL)
		{
			// Create a new model with no arguments
			return new $class;
		}

		if ($multiple !== TRUE)
		{
			// Create a model with a single argument
			return new $class($args);
		}

		$class = new ReflectionClass($class);

		// Create a model with multiple arguments
		return $class->newInstanceArgs($args);
	}

	// Database object
	protected $db = 'default';

	/**
	 * Loads the database instance, if the database is not already loaded.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		if ( ! is_object($this->db))
		{
			// Load the default database
			$this->db = Database::instance($this->db);
		}
	}

} // End Model