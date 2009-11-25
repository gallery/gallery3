<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Kohana Controller class. The controller class must be extended to work
 * properly, so this class is defined as abstract.
 *
 * $Id: Controller.php 4679 2009-11-10 01:45:52Z isaiah $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
abstract class Controller_Core {

	// Allow all controllers to run in production by default
	const ALLOW_PRODUCTION = TRUE;

	/**
	 * Loads URI, and Input into this controller.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		if (Kohana::$instance == NULL)
		{
			// Set the instance to the first controller loaded
			Kohana::$instance = $this;
		}

		// URI should always be available
		$this->uri = URI::instance();

		// Input should always be available
		$this->input = Input::instance();
	}

	/**
	 * Handles methods that do not exist.
	 *
	 * @param   string  method name
	 * @param   array   arguments
	 * @return  void
	 */
	public function __call($method, $args)
	{
		// Default to showing a 404 page
		Event::run('system.404');
	}

} // End Controller Class