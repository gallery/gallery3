<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Unit_Test library.
 *
 * $Id: Unit_Test.php 4367 2009-05-27 21:23:57Z samsoir $
 *
 * @package    Unit_Test
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Unit_Test_Core {

	// The path(s) to recursively scan for tests
	protected $paths = array();

	// The results of all tests from every test class
	protected $results = array();

	// Statistics for every test class
	protected $stats = array();

        public static $lang = array(
                'class'                => 'Class',
                'method'               => 'Method',
                'invalid_test_path'    => 'Failed to open test path: %s.',
                'duplicate_test_class' => 'Duplicate test class named %s found in %s.',
                'test_class_not_found' => 'No test class by the name of %s found in %s.',
                'test_class_extends'   => '%s must extend Unit_Test_Case.',
                'no_tests_found'       => 'No tests found',
                'score'                => 'Score',
                'total'                => 'Total',
                'passed'               => 'Passed',
                'failed'               => 'Failed',
                'error'                => 'Error',
                'errors'               => 'Errors',
                'line'                 => 'line',
                'assert_true'          => 'assert_true: Expected true, but was given (%s) %s.',
                'assert_true_strict'   => 'assert_true_strict: Expected (boolean) true, but was given (%s) %s.',
                'assert_false'         => 'assert_false: Expected false, but was given (%s) %s.',
                'assert_false_strict'  => 'assert_false_strict: Expected (boolean) false, but was given (%s) %s.',
                'assert_equal'         => 'assert_equal: Expected (%s) %s, but was given (%s) %s.',
                'assert_not_equal'     => 'assert_not_equal: Expected not (%s) %s, but was given (%s) %s.',
                'assert_same'          => 'assert_same: Expected (%s) %s, but was given (%s) %s.',
                'assert_not_same'      => 'assert_not_same: Expected not (%s) %s, but was given (%s) %s.',
                'assert_boolean'       => 'assert_boolean: Expected a boolean, but was given (%s) %s.',
                'assert_not_boolean'   => 'assert_not_boolean: Expected not a boolean, but was given (%s) %s.',
                'assert_integer'       => 'assert_integer: Expected an integer, but was given (%s) %s.',
                'assert_not_integer'   => 'assert_not_integer: Expected not an integer, but was given (%s) %s.',
                'assert_float'         => 'assert_float: Expected a float, but was given (%s) %s.',
                'assert_not_float'     => 'assert_not_float: Expected not a float, but was given (%s) %s.',
                'assert_array'         => 'assert_array: Expected an array, but was given (%s) %s.',
                'assert_array_key'     => 'assert_array_key: Expected a valid key, but was given (%s) %s.',
                'assert_in_array'      => 'assert_in_array: Expected a valid value, but was given (%s) %s.',	
                'assert_not_array'     => 'assert_not_array: Expected not an array, but was given (%s) %s.',
                'assert_object'        => 'assert_object: Expected an object, but was given (%s) %s.',
                'assert_not_object'    => 'assert_not_object: Expected not an object, but was given (%s) %s.',
                'assert_null'          => 'assert_null: Expected null, but was given (%s) %s.',
                'assert_not_null'      => 'assert_not_null: Expected not null, but was given (%s) %s.',
                'assert_empty'         => 'assert_empty: Expected an empty value, but was given (%s) %s.',
                'assert_not_empty'     => 'assert_not_empty: Expected not an empty value, but was given (%s) %s.',
                'assert_pattern'       => 'assert_pattern: Expected %s to match %s.',
                'assert_not_pattern'   => 'assert_not_pattern: Expected %s to not match %s.'
                      );
	/**
	 * Sets the test path(s), runs the tests inside and stores the results.
	 *
	 * @param   array      test path(s)
	 * @param   string     filter (regular expression)
	 * @return  void
	 */
	public function __construct($extra_paths=array(), $filter=null)
	{
		// Merge possible default test path(s) from config with the rest
		$paths = array_merge($extra_paths, Kohana::config('unit_test.paths', FALSE, FALSE));

		// Normalize all test paths
		foreach ($paths as $path)
		{
			$path = str_replace('\\', '/', realpath((string) $path));
		}

		// Take out duplicate test paths after normalization
		$this->paths = array_unique($paths);

		// Loop over each given test path
		foreach ($this->paths as $path)
		{
			// Validate test path
			if ( ! is_dir($path))
				throw new Kohana_Exception('unit_test.invalid_test_path', $path);

			// Recursively iterate over each file in the test path
			foreach
			(
				new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::KEY_AS_PATHNAME))
				as $path => $file
			)
			{
				// Normalize path
				$path = str_replace('\\', '/', $path);

				// Skip files without "_Test" suffix
				if ( ! $file->isFile() OR substr($path, -9) !== '_Test'.EXT)
					continue;

				// The class name should be the same as the file name
				$class = substr($path, strrpos($path, '/') + 1, -(strlen(EXT)));

				// Skip hidden files
				if ($class[0] === '.')
					continue;

				// Check for duplicate test class name
				if (class_exists($class, FALSE))
					throw new Kohana_Exception('unit_test.duplicate_test_class', $class, $path);

				// Include the test class
				include_once $path;

				// Check whether the test class has been found and loaded
				if ( ! class_exists($class, FALSE))
					throw new Kohana_Exception('unit_test.test_class_not_found', $class, $path);

				// Reverse-engineer Test class
				$reflector = new ReflectionClass($class);

				// Test classes must extend Unit_Test_Case
				if ( ! $reflector->isSubclassOf(new ReflectionClass('Unit_Test_Case')))
					throw new Kohana_Exception('unit_test.test_class_extends', $class);

				// Skip disabled Tests
				if ($reflector->getConstant('DISABLED') === TRUE)
					continue;

				// Initialize setup and teardown method triggers
				$setup = $teardown = FALSE;

				// Look for valid setup and teardown methods
				foreach (array('setup', 'teardown') as $method_name)
				{
					if ($reflector->hasMethod($method_name))
					{
						$method = new ReflectionMethod($class, $method_name);
						$$method_name = ($method->isPublic() AND ! $method->isStatic() AND $method->getNumberOfRequiredParameters() === 0);
					}
				}

				// Initialize test class results and stats
				$this->results[$class] = array();
				$this->stats[$class] = array
				(
					'passed' => 0,
					'failed' => 0,
					'errors' => 0,
					'total' => 0,
					'score'  => 0,
				);

				// Loop through all the class methods
				foreach ($reflector->getMethods() as $method)
				{
					// Skip invalid test methods
					if ( ! $method->isPublic() OR $method->isStatic() OR $method->getNumberOfRequiredParameters() !== 0)
						continue;

					if ($filter && !preg_match("/$filter/i", "$class::{$method->getName()}")) {
						continue;
					}

					// Test methods should be suffixed with "_test"
					if (substr($method_name = $method->getName(), -5) !== '_test')
						continue;

					// Instantiate Test class
					$object = new $class;

					try
					{
						// Run setup method
						if ($setup === TRUE)
						{
							$object->setup();
						}

                                                $e = null;
						try {
							// Enforce a time limit
							set_time_limit(30);

							// Run the actual test
							$object->$method_name();
						} catch (Exception $e) { }

						// Run teardown method
						if ($teardown === TRUE)
						{
							$object->teardown();
						}

                                                if ($e) {
							throw $e;
                                                }


						$this->stats[$class]['total']++;

						// Test passed
						$this->results[$class][$method_name] = TRUE;
						$this->stats[$class]['passed']++;

					}
					catch (Kohana_Unit_Test_Exception $e)
					{
						$this->stats[$class]['total']++;
						// Test failed
						$this->results[$class][$method_name] = $e;
						$this->stats[$class]['failed']++;
					}
					catch (Exception $e)
					{
						$this->stats[$class]['total']++;

						// Test error
						$this->results[$class][$method_name] = $e;
						$this->stats[$class]['errors']++;
					}

					// Calculate score
					$this->stats[$class]['score'] = $this->stats[$class]['passed'] * 100 / $this->stats[$class]['total'];

					// Cleanup
					unset($object);
				}

				if ($filter && $this->stats[$class]['total'] == 0) {
					unset($this->results[$class]);
					unset($this->stats[$class]);
				}
			}
		}
	}

	/**
	 * Generates nice test results.
	 *
	 * @param   boolean  hide passed tests from the report
	 * @return  string   rendered test results html
	 */
	public function report($hide_passed = NULL)
	{
		// No tests found
		if (empty($this->results))
			return sprintf(self::$lang['no_tests_found']);

		// Hide passed tests from the report?
		$hide_passed = (bool) (($hide_passed !== NULL) ? $hide_passed : Kohana::config('unit_test.hide_passed', FALSE, FALSE));
		
		
		if (PHP_SAPI == 'cli')
		{
			$report = View::factory('kohana_unit_test_cli');
		}
		else
		{
			$report = View::factory('kohana_unit_test');
		}
		// Render unit_test report
		return $report->set('results', $this->results)
					  ->set('stats', $this->stats)
					  ->set('hide_passed', $hide_passed)
					  ->render();
	}

	/**
	 * Magically convert this object to a string.
	 *
	 * @return  string  test report
	 */
	public function __toString()
	{
		return $this->report();
	}

	/**
	 * Magically gets a Unit_Test property.
	 *
	 * @param   string  property name
	 * @return  mixed   variable value if the property is found
	 * @return  void    if the property is not found
	 */
	public function __get($key)
	{
		if (isset($this->$key))
			return $this->$key;
	}

} // End Unit_Test_Core


abstract class Unit_Test_Case {

	public function assert_true($value, $debug = NULL)
	{
		if ($value != TRUE)
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_true'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_true_strict($value, $debug = NULL)
	{
		if ($value !== TRUE)
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_true_strict'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_false($value, $debug = NULL)
	{
		if ($value != FALSE)
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_false'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_false_strict($value, $debug = NULL)
	{
		if ($value !== FALSE)
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_false_strict'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_equal($expected, $actual, $debug = NULL)
	{
		if ($expected != $actual)
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_equal'], gettype($expected), var_export($expected, TRUE), gettype($actual), var_export($actual, TRUE)), $debug);

		return $this;
	}

	public function assert_not_equal($expected, $actual, $debug = NULL)
	{
		if ($expected == $actual)
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_not_equal'], gettype($expected), var_export($expected, TRUE), gettype($actual), var_export($actual, TRUE)), $debug);

		return $this;
	}

	public function assert_same($expected, $actual, $debug = NULL)
	{
		if ($expected !== $actual)
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_same'], gettype($expected), var_export($expected, TRUE), gettype($actual), var_export($actual, TRUE)), $debug);

		return $this;
	}

	public function assert_not_same($expected, $actual, $debug = NULL)
	{
		if ($expected === $actual)
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_not_same'], gettype($expected), var_export($expected, TRUE), gettype($actual), var_export($actual, TRUE)), $debug);

		return $this;
	}

	public function assert_boolean($value, $debug = NULL)
	{
		if ( ! is_bool($value))
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_boolean'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_boolean($value, $debug = NULL)
	{
		if (is_bool($value))
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_not_boolean'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_integer($value, $debug = NULL)
	{
		if ( ! is_int($value))
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_integer'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_integer($value, $debug = NULL)
	{
		if (is_int($value))
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_not_integer'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_float($value, $debug = NULL)
	{
		if ( ! is_float($value))
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_float'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_float($value, $debug = NULL)
	{
		if (is_float($value))
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_not_float'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_array($value, $debug = NULL)
	{
		if ( ! is_array($value))
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_array'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_array_key($key, $array, $debug = NULL)
	{
		if ( ! array_key_exists($key, $array)) {
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_array_key'], gettype($key), var_export($key, TRUE)), $debug);
		}

		return $this;
	}

	public function assert_in_array($value, $array, $debug = NULL)
	{
		if ( ! in_array($value, $array)) {
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_in_array'], gettype($value), var_export($value, TRUE)), $debug);
		}

		return $this;
	}

	public function assert_not_array($value, $debug = NULL)
	{
		if (is_array($value))
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_not_array'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_object($value, $debug = NULL)
	{
		if ( ! is_object($value))
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_object'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_object($value, $debug = NULL)
	{
		if (is_object($value))
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_not_object'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_null($value, $debug = NULL)
	{
		if ($value !== NULL)
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_null'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_null($value, $debug = NULL)
	{
		if ($value === NULL)
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_not_null'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_empty($value, $debug = NULL)
	{
		if ( ! empty($value))
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_empty'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_empty($value, $debug = NULL)
	{
		if (empty($value))
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_empty'], gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_pattern($value, $regex, $debug = NULL)
	{
		if ( ! is_string($value) OR ! is_string($regex) OR ! preg_match($regex, $value))
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_pattern'], var_export($value, TRUE), var_export($regex, TRUE)), $debug);

		return $this;
	}

	public function assert_not_pattern($value, $regex, $debug = NULL)
	{
		if ( ! is_string($value) OR ! is_string($regex) OR preg_match($regex, $value))
			throw new Kohana_Unit_Test_Exception(sprintf(Unit_Test::$lang['assert_not_pattern'], var_export($value, TRUE), var_export($regex, TRUE)), $debug);

		return $this;
	}

} // End Unit_Test_Case


class Kohana_Unit_Test_Exception extends Exception {

	protected $debug = NULL;

	/**
	 * Sets exception message and debug info.
	 *
	 * @param   string  message
	 * @param   mixed   debug info
	 * @return  void
	 */
	public function __construct($message, $debug = NULL)
	{
		// Failure message
		parent::__construct((string) $message);

		// Extra user-defined debug info
		$this->debug = $debug;

		// Overwrite failure location
		$trace = $this->getTrace();
		$this->file = $trace[0]['file'];
		$this->line = $trace[0]['line'];
	}

	/**
	 * Returns the user-defined debug info
	 *
	 * @return  mixed  debug property
	 */
	public function getDebug()
	{
		return $this->debug;
	}

} // End Kohana_Unit_Test_Exception
