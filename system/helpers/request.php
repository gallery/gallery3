<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Request helper class.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class request_Core {

	// Possible HTTP methods
	protected static $http_methods = array('get', 'head', 'options', 'post', 'put', 'delete');

	// Character sets from client's HTTP Accept-Charset request header
	protected static $accept_charsets;

	// Content codings from client's HTTP Accept-Encoding request header
	protected static $accept_encodings;

	// Language tags from client's HTTP Accept-Language request header
	protected static $accept_languages;

	// Content types from client's HTTP Accept request header
	protected static $accept_types;

	// The current user agent and its parsed attributes
	protected static $user_agent;

	/**
	 * Returns the HTTP referrer, or the default if the referrer is not set.
	 *
	 * @param   mixed   default to return
	 * @param   bool    Remove base URL
	 * @return  string
	 */
	public static function referrer($default = FALSE, $remove_base = FALSE)
	{
		if ( ! empty($_SERVER['HTTP_REFERER']))
		{
			// Set referrer
			$ref = $_SERVER['HTTP_REFERER'];

			if ($remove_base === TRUE AND (strpos($ref, url::base(FALSE)) === 0))
			{
				// Remove the base URL from the referrer
				$ref = substr($ref, strlen(url::base(FALSE)));
			}
		}

		return isset($ref) ? $ref : $default;
	}

	/**
	 * Returns the current request protocol, based on $_SERVER['https']. In CLI
	 * mode, NULL will be returned.
	 *
	 * @return  string
	 */
	public static function protocol()
	{
		if (Kohana::$server_api === 'cli')
		{
			return NULL;
		}
		elseif ( ! empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] === 'on')
		{
			return 'https';
		}
		else
		{
			return 'http';
		}
	}

	/**
	 * Tests if the current request is an AJAX request by checking the X-Requested-With HTTP
	 * request header that most popular JS frameworks now set for AJAX calls.
	 *
	 * @return  boolean
	 */
	public static function is_ajax()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
	}

	/**
	 * Returns current request method.
	 *
	 * @throws  Kohana_Exception in case of an unknown request method
	 * @return  string
	 */
	public static function method()
	{
		$method = strtolower($_SERVER['REQUEST_METHOD']);

		if ( ! in_array($method, request::$http_methods))
			throw new Kohana_Exception('Invalid request method :method:', array(':method:' => $method));

		return $method;
	}

	/**
	 * Retrieves current user agent information
	 * keys:  browser, version, platform, mobile, robot
	 *
	 * @param   string  key
	 * @return  mixed   NULL or the parsed value
	 */
	public static function user_agent($key = 'agent')
	{
		// Retrieve raw user agent without parsing
		if ($key === 'agent')
		{
			if (request::$user_agent === NULL)
				return request::$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';

			if (is_array(request::$user_agent))
				return request::$user_agent['agent'];

			return request::$user_agent;
		}

		if ( ! is_array(request::$user_agent))
		{
			request::$user_agent = array();
			request::$user_agent['agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';

			// Parse the user agent and extract basic information
			foreach (Kohana::config('user_agents') as $type => $data)
			{
				foreach ($data as $fragment => $name)
				{
					if (stripos(request::$user_agent['agent'], $fragment) !== FALSE)
					{
						if ($type === 'browser' AND preg_match('|'.preg_quote($fragment).'[^0-9.]*+([0-9.][0-9.a-z]*)|i', request::$user_agent['agent'], $match))
						{
							// Set the browser version
							request::$user_agent['version'] = $match[1];
						}

						// Set the agent name
						request::$user_agent[$type] = $name;
						break;
					}
				}
			}
		}

		return isset(request::$user_agent[$key]) ? request::$user_agent[$key] : NULL;
	}

	/**
	 * Returns boolean of whether client accepts content type.
	 *
	 * @param   string   content type
	 * @param   boolean  set to TRUE to disable wildcard checking
	 * @return  boolean
	 */
	public static function accepts($type = NULL, $explicit_check = FALSE)
	{
		request::parse_accept_content_header();

		if ($type === NULL)
			return request::$accept_types;

		return (request::accepts_at_quality($type, $explicit_check) > 0);
	}

	/**
	 * Returns boolean indicating if the client accepts a charset
	 *
	 * @param   string
	 * @return  boolean
	 */
	public static function accepts_charset($charset = NULL)
	{
		request::parse_accept_charset_header();

		if ($charset === NULL)
			return request::$accept_charsets;

		return (request::accepts_charset_at_quality($charset) > 0);
	}

	/**
	 * Returns boolean indicating if the client accepts an encoding
	 *
	 * @param   string
	 * @param   boolean set to TRUE to disable wildcard checking
	 * @return  boolean
	 */
	public static function accepts_encoding($encoding = NULL, $explicit_check = FALSE)
	{
		request::parse_accept_encoding_header();

		if ($encoding === NULL)
			return request::$accept_encodings;

		return (request::accepts_encoding_at_quality($encoding, $explicit_check) > 0);
	}

	/**
	 * Returns boolean indicating if the client accepts a language tag
	 *
	 * @param   string  language tag
	 * @param   boolean set to TRUE to disable prefix and wildcard checking
	 * @return  boolean
	 */
	public static function accepts_language($tag = NULL, $explicit_check = FALSE)
	{
		request::parse_accept_language_header();

		if ($tag === NULL)
			return request::$accept_languages;

		return (request::accepts_language_at_quality($tag, $explicit_check) > 0);
	}

	/**
	 * Compare the q values for given array of content types and return the one with the highest value.
	 * If items are found to have the same q value, the first one encountered in the given array wins.
	 * If all items in the given array have a q value of 0, FALSE is returned.
	 *
	 * @param   array    content types
	 * @param   boolean  set to TRUE to disable wildcard checking
	 * @return  mixed    string mime type with highest q value, FALSE if none of the given types are accepted
	 */
	public static function preferred_accept($types, $explicit_check = FALSE)
	{
		$max_q = 0;
		$preferred = FALSE;

		foreach ($types as $type)
		{
			$q = request::accepts_at_quality($type, $explicit_check);

			if ($q > $max_q)
			{
				$max_q = $q;
				$preferred = $type;
			}
		}

		return $preferred;
	}

	/**
	 * Compare the q values for a given array of character sets and return the
	 * one with the highest value. If items are found to have the same q value,
	 * the first one encountered takes precedence. If all items in the given
	 * array have a q value of 0, FALSE is returned.
	 *
	 * @param   array   character sets
	 * @return  mixed
	 */
	public static function preferred_charset($charsets)
	{
		$max_q = 0;
		$preferred = FALSE;

		foreach ($charsets as $charset)
		{
			$q = request::accepts_charset_at_quality($charset);

			if ($q > $max_q)
			{
				$max_q = $q;
				$preferred = $charset;
			}
		}

		return $preferred;
	}

	/**
	 * Compare the q values for a given array of encodings and return the one with
	 * the highest value. If items are found to have the same q value, the first
	 * one encountered takes precedence. If all items in the given array have
	 * a q value of 0, FALSE is returned.
	 *
	 * @param   array   encodings
	 * @param   boolean set to TRUE to disable wildcard checking
	 * @return  mixed
	 */
	public static function preferred_encoding($encodings, $explicit_check = FALSE)
	{
		$max_q = 0;
		$preferred = FALSE;

		foreach ($encodings as $encoding)
		{
			$q = request::accepts_encoding_at_quality($encoding, $explicit_check);

			if ($q > $max_q)
			{
				$max_q = $q;
				$preferred = $encoding;
			}
		}

		return $preferred;
	}

	/**
	 * Compare the q values for a given array of language tags and return the
	 * one with the highest value. If items are found to have the same q value,
	 * the first one encountered takes precedence. If all items in the given
	 * array have a q value of 0, FALSE is returned.
	 *
	 * @param   array   language tags
	 * @param   boolean set to TRUE to disable prefix and wildcard checking
	 * @return  mixed
	 */
	public static function preferred_language($tags, $explicit_check = FALSE)
	{
		$max_q = 0;
		$preferred = FALSE;

		foreach ($tags as $tag)
		{
			$q = request::accepts_language_at_quality($tag, $explicit_check);

			if ($q > $max_q)
			{
				$max_q = $q;
				$preferred = $tag;
			}
		}

		return $preferred;
	}

	/**
	 * Returns quality factor at which the client accepts content type
	 *
	 * @param   string   content type (e.g. "image/jpg", "jpg")
	 * @param   boolean  set to TRUE to disable wildcard checking
	 * @return  integer|float
	 */
	public static function accepts_at_quality($type, $explicit_check = FALSE)
	{
		request::parse_accept_content_header();

		// Normalize type
		$type = strtolower($type);

		// General content type (e.g. "jpg")
		if (strpos($type, '/') === FALSE)
		{
			// Don't accept anything by default
			$q = 0;

			// Look up relevant mime types
			foreach ((array) Kohana::config('mimes.'.$type) as $type)
			{
				$q2 = request::accepts_at_quality($type, $explicit_check);
				$q = ($q2 > $q) ? $q2 : $q;
			}

			return $q;
		}

		// Content type with subtype given (e.g. "image/jpg")
		$type = explode('/', $type, 2);

		// Exact match
		if (isset(request::$accept_types[$type[0]][$type[1]]))
			return request::$accept_types[$type[0]][$type[1]];

		if ($explicit_check === FALSE)
		{
			// Wildcard match
			if (isset(request::$accept_types[$type[0]]['*']))
				return request::$accept_types[$type[0]]['*'];

			// Catch-all wildcard match
			if (isset(request::$accept_types['*']['*']))
				return request::$accept_types['*']['*'];
		}

		// Content type not accepted
		return 0;
	}

	/**
	 * Returns quality factor at which the client accepts a charset
	 *
	 * @param   string  charset (e.g., "ISO-8859-1", "utf-8")
	 * @return  integer|float
	 */
	public static function accepts_charset_at_quality($charset)
	{
		request::parse_accept_charset_header();

		// Normalize charset
		$charset = strtolower($charset);

		// Exact match
		if (isset(request::$accept_charsets[$charset]))
			return request::$accept_charsets[$charset];

		if (isset(request::$accept_charsets['*']))
			return request::$accept_charsets['*'];

		if ($charset === 'iso-8859-1')
			return 1;

		return 0;
	}

	/**
	 * Returns quality factor at which the client accepts an encoding
	 *
	 * @param   string  encoding (e.g., "gzip", "deflate")
	 * @param   boolean set to TRUE to disable wildcard checking
	 * @return  integer|float
	 */
	public static function accepts_encoding_at_quality($encoding, $explicit_check = FALSE)
	{
		request::parse_accept_encoding_header();

		// Normalize encoding
		$encoding = strtolower($encoding);

		// Exact match
		if (isset(request::$accept_encodings[$encoding]))
			return request::$accept_encodings[$encoding];

		if ($explicit_check === FALSE)
		{
			if (isset(request::$accept_encodings['*']))
				return request::$accept_encodings['*'];

			if ($encoding === 'identity')
				return 1;
		}

		return 0;
	}

	/**
	 * Returns quality factor at which the client accepts a language
	 *
	 * @param   string  tag (e.g., "en", "en-us", "fr-ca")
	 * @param   boolean set to TRUE to disable prefix and wildcard checking
	 * @return  integer|float
	 */
	public static function accepts_language_at_quality($tag, $explicit_check = FALSE)
	{
		request::parse_accept_language_header();

		$tag = explode('-', strtolower($tag), 2);

		if (isset(request::$accept_languages[$tag[0]]))
		{
			if (isset($tag[1]))
			{
				// Exact match
				if (isset(request::$accept_languages[$tag[0]][$tag[1]]))
					return request::$accept_languages[$tag[0]][$tag[1]];

				// A prefix matches
				if ($explicit_check === FALSE AND isset(request::$accept_languages[$tag[0]]['*']))
					return request::$accept_languages[$tag[0]]['*'];
			}
			else
			{
				// No subtags
				if (isset(request::$accept_languages[$tag[0]]['*']))
					return request::$accept_languages[$tag[0]]['*'];
			}
		}

		if ($explicit_check === FALSE AND isset(request::$accept_languages['*']))
			return request::$accept_languages['*'];

		return 0;
	}

	/**
	 * Parses a HTTP Accept or Accept-* header for q values
	 *
	 * @param   string  header data
	 * @return  array
	 */
	protected static function parse_accept_header($header)
	{
		$result = array();

		// Remove linebreaks and parse the HTTP Accept header
		foreach (explode(',', str_replace(array("\r", "\n"), '', strtolower($header))) as $entry)
		{
			// Explode each entry in content type and possible quality factor
			$entry = explode(';', trim($entry), 2);

			$q = (isset($entry[1]) AND preg_match('~\bq\s*+=\s*+([.0-9]+)~', $entry[1], $match)) ? (float) $match[1] : 1;

			// Overwrite entries with a smaller q value
			if ( ! isset($result[$entry[0]]) OR $q > $result[$entry[0]])
			{
				$result[$entry[0]] = $q;
			}
		}

		return $result;
	}

	/**
	 * Parses a client's HTTP Accept-Charset header
	 */
	protected static function parse_accept_charset_header()
	{
		// Run this function just once
		if (request::$accept_charsets !== NULL)
			return;

		// No HTTP Accept-Charset header found
		if (empty($_SERVER['HTTP_ACCEPT_CHARSET']))
		{
			// Accept everything
			request::$accept_charsets['*'] = 1;
		}
		else
		{
			request::$accept_charsets = request::parse_accept_header($_SERVER['HTTP_ACCEPT_CHARSET']);
		}
	}

	/**
	 * Parses a client's HTTP Accept header
	 */
	protected static function parse_accept_content_header()
	{
		// Run this function just once
		if (request::$accept_types !== NULL)
			return;

		// No HTTP Accept header found
		if (empty($_SERVER['HTTP_ACCEPT']))
		{
			// Accept everything
			request::$accept_types['*']['*'] = 1;
		}
		else
		{
			request::$accept_types = array();

			foreach (request::parse_accept_header($_SERVER['HTTP_ACCEPT']) as $type => $q)
			{
				// Explode each content type (e.g. "text/html")
				$type = explode('/', $type, 2);

				// Skip invalid content types
				if ( ! isset($type[1]))
					continue;

				request::$accept_types[$type[0]][$type[1]] = $q;
			}
		}
	}

	/**
	 * Parses a client's HTTP Accept-Encoding header
	 */
	protected static function parse_accept_encoding_header()
	{
		// Run this function just once
		if (request::$accept_encodings !== NULL)
			return;

		// No HTTP Accept-Encoding header found
		if ( ! isset($_SERVER['HTTP_ACCEPT_ENCODING']))
		{
			// Accept everything
			request::$accept_encodings['*'] = 1;
		}
		elseif ($_SERVER['HTTP_ACCEPT_ENCODING'] === '')
		{
			// Accept only identity
			request::$accept_encodings['identity'] = 1;
		}
		else
		{
			request::$accept_encodings = request::parse_accept_header($_SERVER['HTTP_ACCEPT_ENCODING']);
		}
	}

	/**
	 * Parses a client's HTTP Accept-Language header
	 */
	protected static function parse_accept_language_header()
	{
		// Run this function just once
		if (request::$accept_languages !== NULL)
			return;

		// No HTTP Accept-Language header found
		if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			// Accept everything
			request::$accept_languages['*'] = 1;
		}
		else
		{
			request::$accept_languages = array();

			foreach (request::parse_accept_header($_SERVER['HTTP_ACCEPT_LANGUAGE']) as $tag => $q)
			{
				// Explode each language (e.g. "en-us")
				$tag = explode('-', $tag, 2);

				request::$accept_languages[$tag[0]][isset($tag[1]) ? $tag[1] : '*'] = $q;
			}
		}
	}

} // End request
