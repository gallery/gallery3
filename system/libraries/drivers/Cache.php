<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Cache driver abstract class.
 *
 * $Id$
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
abstract class Cache_Driver {
	/**
	 * Set cache items  
	 */
	abstract public function set($items, $tags = NULL, $lifetime = NULL);

	/**
	 * Get a cache items by key 
	 */
	abstract public function get($keys, $single = FALSE);

	/**
	 * Get cache items by tag 
	 */
	abstract public function get_tag($tags);

	/**
	 * Delete cache item by key 
	 */
	abstract public function delete($keys);

	/**
	 * Delete cache items by tag 
	 */
	abstract public function delete_tag($tags);

	/**
	 * Empty the cache
	 */
	abstract public function delete_all();
} // End Cache Driver