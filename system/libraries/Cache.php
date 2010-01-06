<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Provides a driver-based interface for finding, creating, and deleting cached
 * resources. Caches are identified by a unique string. Tagging of caches is
 * also supported, and caches can be found and deleted by id or tag.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Cache_Core {

	protected static $instances = array();

	// Configuration
	protected $config;

	// Driver object
	protected $driver;

	/**
	 * Returns a singleton instance of Cache.
	 *
	 * @param   string  configuration
	 * @return  Cache_Core
	 */
	public static function & instance($config = FALSE)
	{
		if ( ! isset(Cache::$instances[$config]))
		{
			// Create a new instance
			Cache::$instances[$config] = new Cache($config);
		}

		return Cache::$instances[$config];
	}

	/**
	 * Loads the configured driver and validates it.
	 *
	 * @param   array|string  custom configuration or config group name
	 * @return  void
	 */
	public function __construct($config = FALSE)
	{
		if (is_string($config))
		{
			$name = $config;

			// Test the config group name
			if (($config = Kohana::config('cache.'.$config)) === NULL)
				throw new Cache_Exception('The :group: group is not defined in your configuration.', array(':group:' => $name));
		}

		if (is_array($config))
		{
			// Append the default configuration options
			$config += Kohana::config('cache.default');
		}
		else
		{
			// Load the default group
			$config = Kohana::config('cache.default');
		}

		// Cache the config in the object
		$this->config = $config;

		// Set driver name
		$driver = 'Cache_'.ucfirst($this->config['driver']).'_Driver';

		// Load the driver
		if ( ! Kohana::auto_load($driver))
			throw new Cache_Exception('The :driver: driver for the :class: library could not be found',
									   array(':driver:' => $this->config['driver'], ':class:' => get_class($this)));

		// Initialize the driver
		$this->driver = new $driver($this->config['params']);

		// Validate the driver
		if ( ! ($this->driver instanceof Cache_Driver))
			throw new Cache_Exception('The :driver: driver for the :library: library must implement the :interface: interface',
									   array(':driver:' => $this->config['driver'], ':library:' => get_class($this), ':interface:' => 'Cache_Driver'));

		Kohana_Log::add('debug', 'Cache Library initialized');
	}

	/**
	 * Set cache items
	 */
	public function set($key, $value = NULL, $tags = NULL, $lifetime = NULL)
	{
		if ($lifetime === NULL)
		{
			$lifetime = $this->config['lifetime'];
		}

		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		if ($this->config['prefix'] !== NULL)
		{
			$key = $this->add_prefix($key);

			if ($tags !== NULL)
			{
				$tags = $this->add_prefix($tags, FALSE);
			}
		}

		return $this->driver->set($key, $tags, $lifetime);
	}

	/**
	 * Get a cache items by key
	 */
	public function get($keys)
	{
		$single = FALSE;

		if ( ! is_array($keys))
		{
			$keys = array($keys);
			$single = TRUE;
		}

		if ($this->config['prefix'] !== NULL)
		{
			$keys = $this->add_prefix($keys, FALSE);

			if ( ! $single)
			{
			    return $this->strip_prefix($this->driver->get($keys, $single));
			}

		}

		return $this->driver->get($keys, $single);
	}

	/**
	 * Get cache items by tags
	 */
	public function get_tag($tags)
	{
		if ( ! is_array($tags))
		{
			$tags = array($tags);
		}

		if ($this->config['prefix'] !== NULL)
		{
		    $tags = $this->add_prefix($tags, FALSE);
		    return $this->strip_prefix($this->driver->get_tag($tags));
		}
		else
		{
		    return $this->driver->get_tag($tags);
		}
	}

	/**
	 * Delete cache item by key
	 */
	public function delete($keys)
	{
		if ( ! is_array($keys))
		{
			$keys = array($keys);
		}

		if ($this->config['prefix'] !== NULL)
		{
			$keys = $this->add_prefix($keys, FALSE);
		}

		return $this->driver->delete($keys);
	}

	/**
	 * Delete cache items by tag
	 */
	public function delete_tag($tags)
	{
		if ( ! is_array($tags))
		{
			$tags = array($tags);
		}

		if ($this->config['prefix'] !== NULL)
		{
			$tags = $this->add_prefix($tags, FALSE);
		}

		return $this->driver->delete_tag($tags);
	}

	/**
	 * Empty the cache
	 */
	public function delete_all()
	{
		return $this->driver->delete_all();
	}

	/**
	 * Add a prefix to keys or tags
	 */
	protected function add_prefix($array, $to_key = TRUE)
	{
		$out = array();

		foreach($array as $key => $value)
		{
			if ($to_key)
			{
				$out[$this->config['prefix'].$key] = $value;
			}
			else
			{
				$out[$key] = $this->config['prefix'].$value;
			}
		}

		return $out;
	}

	/**
	 * Strip a prefix to keys or tags
	 */
	protected function strip_prefix($array)
	{
		$out = array();

		$start = strlen($this->config['prefix']);

		foreach($array as $key => $value)
		{
			$out[substr($key, $start)] = $value;
		}

		return $out;
	}

} // End Cache Library