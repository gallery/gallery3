<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Security helper class.
 *
 * $Id: security.php 4698 2009-12-08 18:39:33Z isaiah $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class security_Core {

	/**
	 * Sanitize a string with the xss_clean method.
	 *
	 * @param   string  string to sanitize
	 * @param   string  xss_clean method to use ('htmlpurifier' or defaults to built-in method)
	 * @return  string
	 */
	public static function xss_clean($str, $tool = NULL)
	{
		return Input::instance()->xss_clean($str, $tool);
	}

	/**
	 * Remove image tags from a string.
	 *
	 * @param   string  string to sanitize
	 * @return  string
	 */
	public static function strip_image_tags($str)
	{
		return preg_replace('#<img\s.*?(?:src\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', '$1', $str);
	}

} // End security