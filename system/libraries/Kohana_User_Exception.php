<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Creates a custom exception message.
 *
 * $Id: Kohana_User_Exception.php 4729 2009-12-29 20:35:19Z isaiah $
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

class Kohana_User_Exception_Core extends Kohana_Exception {

	/**
	 * Set exception title and message.
	 *
	 * @param   string  exception title string
	 * @param   string  exception message string
	 * @param   string  custom error template
	 */
	public function __construct($title, $message, array $variables = NULL)
	{
		parent::__construct($message, $variables);

		// Code is the error title
		$this->code = $title;
	}

} // End Kohana User Exception
