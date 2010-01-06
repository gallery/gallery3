<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ORM Validation exceptions.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class ORM_Validation_Exception_Core extends Database_Exception {

	/**
	 * Handles Database Validation Exceptions
	 *
	 * @param Validation $array
	 * @return
	 */
	public static function handle_validation($table, Validation $array)
	{
		$exception = new ORM_Validation_Exception('ORM Validation has failed for :table model',array(':table'=>$table));
		$exception->validation = $array;
		throw $exception;
	}
} // End ORM_Validation_Exception