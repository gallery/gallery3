<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provides a driver-based interface for setting and getting
 * configuration options for the Kohana environment
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Config_Core implements ArrayAccess {

	/**
	 * The default Kohana_Config driver
	 * to use for system setup
	 *
	 * @var     string
	 * @static
	 */
	public static $default_driver = 'array';

	/**
	 * Kohana_Config instance
	 *
	 * @var     array
	 * @static
	 */
	protected static $instance;

	/**
	 * Returns a new instance of the Kohana_Config library
	 * based on the singleton pattern
	 *
	 * @param   string       driver
	 * @return  Kohana_Config
	 * @access  public
	 * @static
	 */
	public static function & instance()
	{
		// If the driver has not been initialised, intialise it
		if ( empty(Kohana_Config::$instance))
		{
			//call a 1 time non singleton of Kohana_Config to get a list of drivers
			$config = new Kohana_Config(array(
				'config_drivers'=>array(
				), 'internal_cache'=>FALSE
			));
			$core_config = $config->get('core');
			Kohana_Config::$instance = new Kohana_Config($core_config);
		}

		// Return the Kohana_Config driver requested
		return Kohana_Config::$instance;
	}

	/**
	 * The drivers for this object
	 *
	 * @var     Kohana_Config_Driver
	 */
	protected $drivers;

	/**
	 * Kohana_Config constructor to load the supplied driver.
	 * Enforces the singleton pattern.
	 *
	 * @param   string       driver
	 * @access  protected
	 */
	protected function __construct(array $core_config)
	{
		$drivers = $core_config['config_drivers'];

		//remove array if it's found in config
		if (in_array('array', $drivers))
			unset($drivers[array_search('array', $drivers)]);

		//add array at the very end
		$this->drivers = $drivers = array_merge($drivers, array(
			'array'
		));

		foreach ($this->drivers as & $driver)
		{
			// Create the driver name
			$driver = 'Config_'.ucfirst($driver).'_Driver';

			// Ensure the driver loads correctly
			if (!Kohana::auto_load($driver))
				throw new Kohana_Exception('The :driver: driver for the :library: library could not be found.', array(
					':driver:' => $driver, ':library:' => get_class($this)
				));

			// Load the new driver
			$driver = new $driver($core_config);

			// Ensure the new driver is valid
			if (!$driver instanceof Config_Driver)
				throw new Kohana_Exception('The :driver: driver for the :library: library must implement the :interface: interface', array(
					':driver:' => $driver, ':library:' => get_class($this), ':interface:' => 'Config_Driver'
				));
		}
	}

	/**
	 * Gets a value from the configuration driver
	 *
	 * @param   string       key
	 * @param   bool         slash
	 * @param   bool         required
	 * @return  mixed
	 * @access  public
	 */
	public function get($key, $slash = FALSE, $required = FALSE)
	{
		foreach ($this->drivers as $driver)
		{
			try
			{
				return $driver->get($key, $slash, $required);
			}
			catch (Kohana_Config_Exception $e)
			{
				//if it's the last driver in the list and it threw an exception, re throw it
				if ($driver === $this->drivers[(count($this->drivers) - 1)])
					throw $e;
			}
		}
	}

	/**
	 * Sets a value to the configuration drivers
	 *
	 * @param   string       key
	 * @param   mixed        value
	 * @return  bool
	 * @access  public
	 */
	public function set($key, $value)
	{
		foreach ($this->drivers as $driver)
		{
			try
			{
				$driver->set($key, $value);
			}
			catch (Kohana_Config_Exception $e)
			{
				//if it's the last driver in the list and it threw an exception, re throw it
				if ($driver === $this->drivers[(count($this->drivers) - 1)])
					throw $e;
			}
		}
		return TRUE;
	}

	/**
	 * Clears a group from configuration
	 *
	 * @param   string       group
	 * @return  bool
	 * @access  public
	 */
	public function clear($group)
	{
		foreach ($this->drivers as $driver)
		{
			try
			{
				$driver->clear($group);
			}
			catch (Kohana_Config_Exception $e)
			{
				//if it's the last driver in the list and it threw an exception, re throw it
				if ($driver === $this->drivers[(count($this->drivers) - 1)])
					throw $e;
			}
		}
		return TRUE;
	}

	/**
	 * Loads a configuration group
	 *
	 * @param   string       group
	 * @param   bool         required
	 * @return  array
	 * @access  public
	 */
	public function load($group, $required = FALSE)
	{
		foreach ($this->drivers as $driver)
		{
			try
			{
				return $driver->load($group, $required);
			}
			catch (Kohana_Config_Exception $e)
			{
				//if it's the last driver in the list and it threw an exception, re throw it
				if ($driver === $this->drivers[(count($this->drivers) - 1)])
					throw $e;
			}
		}
	}

	/**
	 * Returns true or false if any config has been loaded(either manually or from cache)
	 *
	 * @return boolean
	 */
	public function loaded()
	{
		return $this->drivers[(count($this->drivers) - 1)]->loaded;
	}

	/**
	 * The following allows access using
	 * array syntax.
	 *
	 * @example  $config['core.site_domain']
	 */

	 /**
	 * Allows access to configuration settings
	 * using the ArrayAccess interface
	 *
	 * @param   string       key
	 * @return  mixed
	 * @access  public
	 */
	public function offsetGet($key)
	{
		foreach ($this->drivers as $driver)
		{
			try
			{
				return $driver->get($key);
			}
			catch (Kohana_Config_Exception $e)
			{
				//if it's the last driver in the list and it threw an exception, re throw it
				if ($driver === $this->drivers[(count($this->drivers) - 1)])
					throw $e;
			}
		}
	}

	/**
	 * Allows access to configuration settings
	 * using the ArrayAccess interface
	 *
	 * @param   string       key
	 * @param   mixed        value
	 * @return  bool
	 * @access  public
	 */
	public function offsetSet($key, $value)
	{
		foreach ($this->drivers as $driver)
		{
			try
			{
				$driver->set($key, $value);
			}
			catch (Kohana_Config_Exception $e)
			{
				//if it's the last driver in the list and it threw an exception, re throw it
				if ($driver === $this->drivers[(count($this->drivers) - 1)])
					throw $e;
			}
		}
		return TRUE;
	}

	/**
	 * Allows access to configuration settings
	 * using the ArrayAccess interface
	 *
	 * @param   string       key
	 * @return  bool
	 * @access  public
	 */
	public function offsetExists($key)
	{
		foreach ($this->drivers as $driver)
		{
			try
			{
				return $driver->setting_exists($key);
			}
			catch (Kohana_Config_Exception $e)
			{
				//if it's the last driver in the list and it threw an exception, re throw it
				if ($driver === $this->drivers[(count($this->drivers) - 1)])
					throw $e;
			}
		}
	}

	/**
	 * Allows access to configuration settings
	 * using the ArrayAccess interface
	 *
	 * @param   string       key
	 * @return  bool
	 * @access  public
	 */
	public function offsetUnset($key)
	{
		foreach ($this->drivers as $driver)
		{
			try
			{
				return $driver->set($key, NULL);
			}
			catch (Kohana_Config_Exception $e)
			{
				//if it's the last driver in the list and it threw an exception, re throw it
				if ($driver === $this->drivers[(count($this->drivers) - 1)])
					throw $e;
			}
		}
		return TRUE;
	}
} // End KohanaConfig

class Kohana_Config_Exception extends Kohana_Exception {}
