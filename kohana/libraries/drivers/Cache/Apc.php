<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * APC-based Cache driver.
 *
 * $Id: Apc.php 4046 2009-03-05 19:23:29Z Shadowhand $
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Cache_Apc_Driver implements Cache_Driver {

	public function __construct()
	{
		if ( ! extension_loaded('apc'))
			throw new Kohana_Exception('cache.extension_not_loaded', 'apc');
	}

	public function get($id)
	{
		return (($return = apc_fetch($id)) === FALSE) ? NULL : $return;
	}

	public function set($id, $data, array $tags = NULL, $lifetime)
	{
		if ( ! empty($tags))
		{
			Kohana::log('error', 'Cache: tags are unsupported by the APC driver');
		}

		return apc_store($id, $data, $lifetime);
	}

	public function find($tag)
	{
		Kohana::log('error', 'Cache: tags are unsupported by the APC driver');

		return array();
	}

	public function delete($id, $tag = FALSE)
	{
		if ($tag === TRUE)
		{
			Kohana::log('error', 'Cache: tags are unsupported by the APC driver');
			return FALSE;
		}
		elseif ($id === TRUE)
		{
			return apc_clear_cache('user');
		}
		else
		{
			return apc_delete($id);
		}
	}

	public function delete_expired()
	{
		return TRUE;
	}

} // End Cache APC Driver