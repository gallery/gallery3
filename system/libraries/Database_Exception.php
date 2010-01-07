<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database exceptions.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Database_Exception_Core extends Kohana_Exception {

	// Database error code
	protected $code = E_DATABASE_ERROR;

} // End Database_Exception