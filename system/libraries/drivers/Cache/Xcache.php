<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * XCache-based Cache driver.
 * 
 * $Id: Memcache.php 4605 2009-09-14 17:22:21Z kiall $
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 * @TODO       Check if XCache cleans its own keys.
 */
class Cache_Xcache_Driver extends Cache_Driver {
	protected $config;

	public function __construct($config)
	{
		if ( ! extension_loaded('xcache'))
			throw new Cache_Exception('The xcache PHP extension must be loaded to use this driver.');

		$this->config = $config;
	}

	public function set($items, $tags = NULL, $lifetime = NULL)
	{
		if ($tags !== NULL)
		{
			Kohana_Log::add('debug', __('Cache: XCache driver does not support tags'));
		}

		foreach ($items as $key => $value)
		{
			if (is_resource($value))
				throw new Cache_Exception('Caching of resources is impossible, because resources cannot be serialised.');

			if ( ! xcache_set($key, $value, $lifetime))
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	public function get($keys, $single = FALSE)
	{
		$items = array();

		foreach ($keys as $key)
		{
			if (xcache_isset($id))
			{
				$items[$key] = xcache_get($id);
			}
			else
			{
				$items[$key] = NULL;
			}
		}

		if ($single)
		{
			return ($items === FALSE OR count($items) > 0) ? current($items) : NULL;
		}
		else
		{
			return ($items === FALSE) ? array() : $items;
		}
	}

	/**
	 * Get cache items by tag
	 */
	public function get_tag($tags)
	{
		Kohana_Log::add('debug', __('Cache: XCache driver does not support tags'));
		return NULL;
	}

	/**
	 * Delete cache item by key
	 */
	public function delete($keys)
	{
		foreach ($keys as $key)
		{
			if ( ! xcache_unset($key))
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Delete cache items by tag
	 */
	public function delete_tag($tags)
	{
		Kohana_Log::add('debug', __('Cache: XCache driver does not support tags'));
		return NULL;
	}

	/**
	 * Empty the cache
	 */
	public function delete_all()
	{
		$this->auth();
		$result = TRUE;

		for ($i = 0, $max = xcache_count(XC_TYPE_VAR); $i < $max; $i++)
		{
			if (xcache_clear_cache(XC_TYPE_VAR, $i) !== NULL)
			{
				$result = FALSE;
				break;
			}
		}

		// Undo the login
		$this->auth(TRUE);

		return $result;
	}

	private function auth($reverse = FALSE)
	{
		static $backup = array();

		$keys = array('PHP_AUTH_USER', 'PHP_AUTH_PW');

		foreach ($keys as $key)
		{
			if ($reverse)
			{
				if (isset($backup[$key]))
				{
					$_SERVER[$key] = $backup[$key];
					unset($backup[$key]);
				}
				else
				{
					unset($_SERVER[$key]);
				}
			}
			else
			{
				$value = getenv($key);

				if ( ! empty($value))
				{
					$backup[$key] = $value;
				}

				$_SERVER[$key] = $this->config->{$key};
			}
		}
	}
} // End Cache XCache Driver
