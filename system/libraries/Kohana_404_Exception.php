<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Creates a "Page Not Found" exception.
 *
 * $Id: Kohana_404_Exception.php 4729 2009-12-29 20:35:19Z isaiah $
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

class Kohana_404_Exception_Core extends Kohana_Exception {

	protected $code = E_PAGE_NOT_FOUND;

	/**
	 * Set internal properties.
	 *
	 * @param  string  URI of page
	 * @param  string  custom error template
	 */
	public function __construct($page = NULL)
	{
		if ($page === NULL)
		{
			// Use the complete URI
			$page = Router::$complete_uri;
		}

		parent::__construct('The page you requested, %page%, could not be found.', array('%page%' => $page));
	}

	/**
	 * Throws a new 404 exception.
	 *
	 * @throws  Kohana_404_Exception
	 * @return  void
	 */
	public static function trigger($page = NULL)
	{
		throw new Kohana_404_Exception($page);
	}

	/**
	 * Sends 404 headers, to emulate server behavior.
	 *
	 * @return void
	 */
	public function sendHeaders()
	{
		// Send the 404 header
		header('HTTP/1.1 404 File Not Found');
	}

} // End Kohana 404 Exception