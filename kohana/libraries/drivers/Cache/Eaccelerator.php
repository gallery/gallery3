<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Eaccelerator-based Cache driver.
 *
 * $Id: Eaccelerator.php 4046 2009-03-05 19:23:29Z Shadowhand $
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Cache_Eaccelerator_Driver implements Cache_Driver {

	public function __construct()
	{
		if ( ! extension_loaded('eaccelerator'))
			throw new Kohana_Exception('cache.extension_not_loaded', 'eaccelerator');
	}

	public function get($id)
	{
		return eaccelerator_get($id);
	}

	public function find($tag)
	{
		Kohana::log('error', 'tags are unsupported by the eAccelerator driver');

		return array();
	}

	public function set($id, $data, array $tags = NULL, $lifetime)
	{
		if ( ! empty($tags))
		{
			Kohana::log('error', 'tags are unsupported by the eAccelerator driver');
		}

		return eaccelerator_put($id, $data, $lifetime);
	}

	public function delete($id, $tag = FALSE)
	{
		if ($tag === TRUE)
		{
			Kohana::log('error', 'tags are unsupported by the eAccelerator driver');
			return FALSE;
		}
		elseif ($id === TRUE)
		{
			return eaccelerator_clean();
		}
		else
		{
			return eaccelerator_rm($id);
		}
	}

	public function delete_expired()
	{
		eaccelerator_gc();

		return TRUE;
	}

} // End Cache eAccelerator Driver