<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Cache settings, defined as arrays, or "groups". If no group name is
 * used when loading the cache library, the group named "default" will be used.
 *
 * Each group can be used independently, and multiple groups can be used at once.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

/**
 * Group Options:
 *
 * - driver   - Cache backend driver. Kohana comes with file, database, and memcache drivers.
 *  -   File cache is fast and reliable, but requires many filesystem lookups.
 *  -   Database cache can be used to cache items remotely, but is slower.
 *  -   Memcache is very high performance, but prevents cache tags from being used.
 *
 * - params   - Driver parameters, specific to each driver.
 *
 * - lifetime - Default lifetime of caches in seconds. By default caches are stored for
 *             thirty minutes. Specific lifetime can also be set when creating a new cache.
 *             Setting this to 0 will never automatically delete caches.
 *
 * -  prefix   - Adds a prefix to all keys and tags. This can have a severe performance impact.
 *
 */
$config['default'] = array
(
	'driver'   => 'file',
	'params'   => array('directory' => APPPATH.'cache', 'gc_probability' => 1000),
	'lifetime' => 1800,
	'prefix'   => NULL
);
