<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Kohana PHP Error Exceptions
 *
 * $Id: Kohana_PHP_Exception.php 4729 2009-12-29 20:35:19Z isaiah $
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

class Kohana_PHP_Exception_Core extends Kohana_Exception {

	public static $enabled = FALSE;

	/**
	 * Enable Kohana PHP error handling.
	 *
	 * @return  void
	 */
	public static function enable()
	{
		if ( ! Kohana_PHP_Exception::$enabled)
		{
			// Handle runtime errors
			set_error_handler(array('Kohana_PHP_Exception', 'error_handler'));

			// Handle errors which halt execution
			Event::add('system.shutdown', array('Kohana_PHP_Exception', 'shutdown_handler'));

			Kohana_PHP_Exception::$enabled = TRUE;
		}
	}

	/**
	 * Disable Kohana PHP error handling.
	 *
	 * @return  void
	 */
	public static function disable()
	{
		if (Kohana_PHP_Exception::$enabled)
		{
			restore_error_handler();

			Event::clear('system.shutdown', array('Kohana_PHP_Exception', 'shutdown_handler'));

			Kohana_PHP_Exception::$enabled = FALSE;
		}
	}

	/**
	 * Create a new PHP error exception.
	 *
	 * @return  void
	 */
	public function __construct($code, $error, $file, $line, $context = NULL)
	{
		parent::__construct($error);

		// Set the error code, file, line, and context manually
		$this->code = $code;
		$this->file = $file;
		$this->line = $line;
	}

	/**
	 * PHP error handler.
	 *
	 * @throws  Kohana_PHP_Exception
	 * @return  void
	 */
	public static function error_handler($code, $error, $file, $line, $context = NULL)
	{
		// Respect error_reporting settings
		if (error_reporting() & $code)
		{
			// Throw an exception
			throw new Kohana_PHP_Exception($code, $error, $file, $line, $context);
		}
	}

	/**
	 * Catches errors that are not caught by the error handler, such as E_PARSE.
	 *
	 * @uses    Kohana_Exception::handle()
	 * @return  void
	 */
	public static function shutdown_handler()
	{
		if (Kohana_PHP_Exception::$enabled AND $error = error_get_last() AND (error_reporting() & $error['type']))
		{
			// Fake an exception for nice debugging
			Kohana_Exception::handle(new Kohana_PHP_Exception($error['type'], $error['message'], $error['file'], $error['line']));
		}
	}

} // End Kohana PHP Exception
