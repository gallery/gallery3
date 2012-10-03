<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Controls headers that effect client caching of pages
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class expires_Core {

	/**
	 * Sets the amount of time before content expires
	 *
	 * @param   integer Seconds before the content expires
	 * @param   integer Last modified timestamp in seconds(optional)
 	 * @return  integer Timestamp when the content expires
	 */
	public static function set($seconds = 60, $last_modified=null)
	{
		$now = time();
		$expires = $now + $seconds;
 		if (empty($last_modified))
 		{
 			$last_modified = $now;
 		}

 		 header('Last-Modified: '.gmdate('D, d M Y H:i:s T', $last_modified));

		// HTTP 1.0
		header('Expires: '.gmdate('D, d M Y H:i:s T', $expires));

		// HTTP 1.1
		header('Cache-Control: public,max-age='.$seconds);

		return $expires;
	}

	/**
	 * Parses the If-Modified-Since header
	 *
	 * @return  integer|boolean Timestamp or FALSE when header is lacking or malformed
	 */
	public static function get()
	{
		if ( ! empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			// Some versions of IE6 append "; length=####"
			if (($strpos = strpos($_SERVER['HTTP_IF_MODIFIED_SINCE'], ';')) !== FALSE)
			{
				$mod_time = substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 0, $strpos);
			}
			else
			{
				$mod_time = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
			}

			return strtotime($mod_time);
		}

		return FALSE;
	}

	/**
	 * Checks to see if content should be updated otherwise sends Not Modified status
	 * and exits.
	 *
	 * @uses    exit()
	 * @uses    expires::get()
	 *
	 * @param   integer         Maximum age of the content in seconds
	 * @param   integer Last modified timestamp in seconds(optional)
	 * @return  integer|boolean Timestamp of the If-Modified-Since header or FALSE when header is lacking or malformed
	 */
	public static function check($seconds = 60, $modified=null)
	{
		if ($last_modified = expires::get())
		{
			$now = time();

 			if (empty($last_modified))
 			{
 				$last_modified = $now;
 			}

			if ($modified <= $last_modified)
			{
				// Content has not expired
				header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
				header('Last-Modified: '.gmdate('D, d M Y H:i:s T', $last_modified));

				$expires = $now + $seconds;
				// HTTP 1.0
				header('Expires: '.gmdate('D, d M Y H:i:s T', $expires));

				// HTTP 1.1
				header('Cache-Control: public,max-age='.$seconds);

				// Clear any output
				Event::add('system.display', create_function('', 'Kohana::$output = "";'));

				exit;
			}
		}

		return $last_modified;
	}

	/**
	 * Check if expiration headers are already set
	 *
	 * @return boolean
	 */
	public static function headers_set()
	{
		foreach (headers_list() as $header)
		{
			if (strncasecmp($header, 'Expires:', 8) === 0
				OR strncasecmp($header, 'Cache-Control:', 14) === 0
				OR strncasecmp($header, 'Last-Modified:', 14) === 0)
			{
				return TRUE;
			}
		}

		return FALSE;
	}

} // End expires
