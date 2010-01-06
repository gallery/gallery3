<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Cookie helper class.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class cookie_Core {

	/**
	 * Sets a cookie with the given parameters.
	 *
	 * @param   string   cookie name or array of config options
	 * @param   string   cookie value
	 * @param   integer  number of seconds before the cookie expires
	 * @param   string   URL path to allow
	 * @param   string   URL domain to allow
	 * @param   boolean  HTTPS only
	 * @param   boolean  HTTP only (requires PHP 5.2 or higher)
	 * @return  boolean
	 */
	public static function set($name, $value = NULL, $expire = NULL, $path = NULL, $domain = NULL, $secure = NULL, $httponly = NULL)
	{
		if (headers_sent())
			return FALSE;

		// If the name param is an array, we import it
		is_array($name) and extract($name, EXTR_OVERWRITE);

		// Fetch default options
		$config = Kohana::config('cookie');

		foreach (array('value', 'expire', 'domain', 'path', 'secure', 'httponly') as $item)
		{
			if ($$item === NULL AND isset($config[$item]))
			{
				$$item = $config[$item];
			}
		}

		if ($expire !== 0)
		{
			 // The expiration is expected to be a UNIX timestamp
			$expire += time();
		}

		$value = cookie::salt($name, $value).'~'.$value;

		return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	/**
	 * Fetch a cookie value, using the Input library.
	 *
	 * @param   string   cookie name
	 * @param   mixed    default value
	 * @param   boolean  use XSS cleaning on the value
	 * @return  string
	 */
	public static function get($name = NULL, $default = NULL, $xss_clean = FALSE)
	{
		// Return an array of all the cookies if we don't have a name
		if ($name === NULL)
		{
			$cookies = array();

			foreach($_COOKIE AS $key => $value)
			{
				$cookies[$key] = cookie::get($key, $default, $xss_clean);
			}
			return $cookies;
		}

		if ( ! isset($_COOKIE[$name]))
		{
			return $default;
		}

		// Get the cookie value
		$cookie = $_COOKIE[$name];

		// Find the position of the split between salt and contents
		$split = strlen(cookie::salt($name, NULL));

		if (isset($cookie[$split]) AND $cookie[$split] === '~')
		{
			 // Separate the salt and the value
			list ($hash, $value) = explode('~', $cookie, 2);

			if (cookie::salt($name, $value) === $hash)
			{
				if ($xss_clean === TRUE AND Kohana::config('core.global_xss_filtering') === FALSE)
				{
					return Input::instance()->xss_clean($value);
				}
				// Cookie signature is valid
				return $value;
			}

			 // The cookie signature is invalid, delete it
			cookie::delete($name);
		}

		return $default;
	}

	/**
	 * Nullify and unset a cookie.
	 *
	 * @param   string   cookie name
	 * @param   string   URL path
	 * @param   string   URL domain
	 * @return  boolean
	 */
	public static function delete($name, $path = NULL, $domain = NULL)
	{
		// Delete the cookie from globals
		unset($_COOKIE[$name]);

		// Sets the cookie value to an empty string, and the expiration to 24 hours ago
		return cookie::set($name, '', -86400, $path, $domain, FALSE, FALSE);
	}

	/**
	 * Generates a salt string for a cookie based on the name and value.
	 *
	 * @param	string $name name of cookie
	 * @param	string $value value of cookie
	 * @return	string sha1 hash
	 */
	public static function salt($name, $value)
	{
		// Determine the user agent
		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

		// Cookie salt.
		$salt = Kohana::config('cookie.salt');

		return sha1($agent.$name.$value.$salt);
	}

	final private function __construct()
 	{
		// Static class.
	}

} // End cookie