<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database helper class.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
 class db_Core {

	public static function query($sql)
	{
		return new Database_Query($sql);
	}

	public static function build($database = 'default')
	{
		return new Database_Builder($database);
	}

	public static function select($columns = NULL)
	{
		return db::build()->select($columns);
	}

	public static function insert($table = NULL, $set = NULL)
	{
		return db::build()->insert($table, $set);
	}

	public static function update($table = NULL, $set = NULL, $where = NULL)
	{
		return db::build()->update($table, $set, $where);
	}

	public static function delete($table = NULL, $where = NULL)
	{
		return db::build()->delete($table, $where);
	}

	public static function expr($expression)
	{
		return new Database_Expression($expression);
	}

} // End db
