<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Memcache-based Cache driver.
 *
 * $Id$
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Cache_Memcache_Driver implements Cache_Driver {

	const TAGS_KEY = 'memcache_tags_array';

	// Cache backend object and flags
	protected $backend;
	protected $flags;

	// The persistent lifetime value for expirations of 0
	protected $persistent_lifetime;

	// Tags array
	protected $tags;

	// Have the tags been changed?
	protected $tags_changed = FALSE;

	public function __construct()
	{
		if ( ! extension_loaded('memcache'))
			throw new Kohana_Exception('cache.extension_not_loaded', 'memcache');

		$this->backend = new Memcache;
		$this->flags = Kohana::config('cache_memcache.compression') ? MEMCACHE_COMPRESSED : FALSE;

		$servers = Kohana::config('cache_memcache.servers');

		foreach ($servers as $server)
		{
			// Make sure all required keys are set
			$server += array('host' => '127.0.0.1', 'port' => 11211, 'persistent' => FALSE);

			// Add the server to the pool
			$this->backend->addServer($server['host'], $server['port'], (bool) $server['persistent'])
				or Kohana::log('error', 'Cache: Connection failed: '.$server['host']);
		}

		// Set "persistent lifetime" value to one year
		$this->persistent_lifetime = strtotime('now +1 year');

		// Load tags
		$this->tags = $this->backend->get(self::TAGS_KEY);

		if ( ! is_array($this->tags))
		{
			// Create a new tags array
			$this->tags = array();

			// Tags have been created
			$this->tags_changed = TRUE;
		}
	}

	public function __destruct()
	{
		if ($this->tags_changed === TRUE)
		{
			// Save the tags
			$this->backend->set(self::TAGS_KEY, $this->tags, $this->flags, $this->persistent_lifetime);
		}
	}

	public function find($tag)
	{
		if (isset($this->tags[$tag]) AND $results = $this->backend->get($this->tags[$tag]))
		{
				// Return all the found caches
				return $results;
		}
		else
		{
			// No matching tags
			return array();
		}
	}

	public function get($id)
	{
		return (($return = $this->backend->get($id)) === FALSE) ? NULL : $return;
	}

	public function set($id, $data, array $tags = NULL, $lifetime)
	{
		if ( ! empty($tags))
		{
			// Tags will be changed
			$this->tags_changed = TRUE;

			foreach ($tags as $tag)
			{
				// Add the id to each tag
				$this->tags[$tag][$id] = $id;
			}
		}

		if ($lifetime === 0)
		{
			// Using an expiration of zero is unreliable, as memcache may delete
			// it without warning. @see http://php.net/memcache_set
			$lifetime = $this->persistent_lifetime;
		}
		else
		{
			// Memcache driver expects unix timestamp
			$lifetime += time();
		}

		// Set a new value
		return $this->backend->set($id, $data, $this->flags, $lifetime);
	}

	public function delete($id, $tag = FALSE)
	{
		// Tags will be changed
		$this->tags_changed = TRUE;

		if ($id === TRUE)
		{
			if ($status = $this->backend->flush())
			{
				// Remove all tags, all items have been deleted
				$this->tags = array();

				// We must sleep after flushing, or overwriting will not work!
				// @see http://php.net/manual/en/function.memcache-flush.php#81420
				sleep(1);
			}

			return $status;
		}
		elseif ($tag === TRUE)
		{
			if (isset($this->tags[$id]))
			{
				foreach ($this->tags[$id] as $_id)
				{
					// Delete each id in the tag
					$this->backend->delete($_id);
				}

				// Delete the tag
				unset($this->tags[$id]);
			}

			return TRUE;
		}
		else
		{
			foreach ($this->tags as $tag => $_ids)
			{
				if (isset($this->tags[$tag][$id]))
				{
					// Remove the id from the tags
					unset($this->tags[$tag][$id]);
				}
			}

			return $this->backend->delete($id);
		}
	}

	public function delete_expired()
	{
		// Tags will be changed
		$this->tags_changed = TRUE;

		foreach ($this->tags as $tag => $_ids)
		{
			foreach ($_ids as $id)
			{
				if ( ! $this->backend->get($id))
				{
					// This id has disappeared, delete it from the tags
					unset($this->tags[$tag][$id]);
				}
			}

			if (empty($this->tags[$tag]))
			{
				// The tag no longer has any valid ids
				unset($this->tags[$tag]);
			}
		}

		// Memcache handles garbage collection internally
		return TRUE;
	}

} // End Cache Memcache Driver
