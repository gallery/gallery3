<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Router
 *
 * $Id: Router.php 4729 2009-12-29 20:35:19Z isaiah $
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Router_Core {

	protected static $routes;

	public static $current_uri  = '';
	public static $query_string = '';
	public static $complete_uri = '';
	public static $routed_uri   = '';
	public static $url_suffix   = '';

	public static $segments;
	public static $rsegments;

	public static $controller;
	public static $controller_path;

	public static $method    = 'index';
	public static $arguments = array();

	/**
	 * Router setup routine. Automatically called during Kohana setup process.
	 *
	 * @return  void
	 */
	public static function setup()
	{
		if ( ! empty($_SERVER['QUERY_STRING']))
		{
			// Set the query string to the current query string
			Router::$query_string = '?'.urldecode(trim($_SERVER['QUERY_STRING'], '&'));
		}

		if (Router::$routes === NULL)
		{
			// Load routes
			Router::$routes = Kohana::config('routes');
		}

		// Default route status
		$default_route = FALSE;

		if (Router::$current_uri === '')
		{
			// Make sure the default route is set
			if (empty(Router::$routes['_default']))
				throw new Kohana_Exception('Please set a default route in config/routes.php.');

			// Use the default route when no segments exist
			Router::$current_uri = Router::$routes['_default'];

			// Default route is in use
			$default_route = TRUE;
		}

		// Remove all dot-paths from the URI, they are not valid
		Router::$current_uri = preg_replace('#\.[\s./]*/#', '', Router::$current_uri);

		// At this point routed URI and current URI are the same
		Router::$routed_uri = Router::$current_uri = trim(Router::$current_uri, '/');

		if ($default_route === TRUE)
		{
			Router::$complete_uri = Router::$query_string;
			Router::$current_uri = '';
			Router::$segments = array();
		}
		else
		{
			Router::$complete_uri = Router::$current_uri.Router::$query_string;

			// Explode the segments by slashes
			Router::$segments = explode('/', Router::$current_uri);

			if (count(Router::$routes) > 1)
			{
				// Custom routing
				Router::$routed_uri = Router::routed_uri(Router::$current_uri);
			}
		}

		// Explode the routed segments by slashes
		Router::$rsegments = explode('/', Router::$routed_uri);

		// Prepare to find the controller
		$controller_path = '';
		$method_segment  = NULL;

		// Paths to search
		$paths = Kohana::include_paths();

		foreach (Router::$rsegments as $key => $segment)
		{
			// Add the segment to the search path
			$controller_path .= $segment;

			$found = FALSE;
			foreach ($paths as $dir)
			{
				// Search within controllers only
				$dir .= 'controllers/';

				if (is_dir($dir.$controller_path) OR is_file($dir.$controller_path.EXT))
				{
					// Valid path
					$found = TRUE;

					// The controller must be a file that exists with the search path
					if ($c = str_replace('\\', '/', realpath($dir.$controller_path.EXT))
					    AND is_file($c) AND strpos($c, $dir) === 0)
					{
						// Set controller name
						Router::$controller = $segment;

						// Change controller path
						Router::$controller_path = $c;

						// Set the method segment
						$method_segment = $key + 1;

						// Stop searching
						break;
					}
				}
			}

			if ($found === FALSE)
			{
				// Maximum depth has been reached, stop searching
				break;
			}

			// Add another slash
			$controller_path .= '/';
		}

		if ($method_segment !== NULL AND isset(Router::$rsegments[$method_segment]))
		{
			// Set method
			Router::$method = Router::$rsegments[$method_segment];

			if (isset(Router::$rsegments[$method_segment + 1]))
			{
				// Set arguments
				Router::$arguments = array_slice(Router::$rsegments, $method_segment + 1);
			}
		}

		// Last chance to set routing before a 404 is triggered
		Event::run('system.post_routing');

		if (Router::$controller === NULL)
		{
			// No controller was found, so no page can be rendered
			Event::run('system.404');
		}
	}

	/**
	 * Attempts to determine the current URI using CLI, GET, PATH_INFO, ORIG_PATH_INFO, or PHP_SELF.
	 *
	 * @return  void
	 */
	public static function find_uri()
	{
		if (Kohana::$server_api === 'cli')
		{
			// Command line requires a bit of hacking
			if (isset($_SERVER['argv'][1]))
			{
				Router::$current_uri = $_SERVER['argv'][1];

				// Remove GET string from segments
				if (strpos(Router::$current_uri, '?') !== FALSE)
				{
					list(Router::$current_uri, $query) = explode('?', Router::$current_uri, 2);

					// Parse the query string into $_GET
					parse_str($query, $_GET);

					// Convert $_GET to UTF-8
					$_GET = Input::clean($_GET);
				}
			}
		}
		elseif (isset($_GET['kohana_uri']))
		{
			// Use the URI defined in the query string
			Router::$current_uri = $_GET['kohana_uri'];

			// Remove the URI from $_GET
			unset($_GET['kohana_uri']);

			// Remove the URI from $_SERVER['QUERY_STRING']
			$_SERVER['QUERY_STRING'] = preg_replace('~\bkohana_uri\b[^&]*+&?~', '', $_SERVER['QUERY_STRING']);
		}
		else
		{
			if (isset($_SERVER['PATH_INFO']) AND $_SERVER['PATH_INFO'])
			{
				Router::$current_uri = $_SERVER['PATH_INFO'];
			}
			elseif (isset($_SERVER['ORIG_PATH_INFO']) AND $_SERVER['ORIG_PATH_INFO'])
			{
				Router::$current_uri = $_SERVER['ORIG_PATH_INFO'];
			}
			elseif (isset($_SERVER['PHP_SELF']) AND $_SERVER['PHP_SELF'])
			{
				// PATH_INFO is empty during requests to the front controller
				Router::$current_uri = $_SERVER['PHP_SELF'];
			}

			if (isset($_SERVER['SCRIPT_NAME']) AND $_SERVER['SCRIPT_NAME'])
			{
				// Clean up PATH_INFO fallbacks
				// PATH_INFO may be formatted for ISAPI instead of CGI on IIS
				if (strncmp(Router::$current_uri, $_SERVER['SCRIPT_NAME'], strlen($_SERVER['SCRIPT_NAME'])) === 0)
				{
					// Remove the front controller from the current uri
					Router::$current_uri = (string) substr(Router::$current_uri, strlen($_SERVER['SCRIPT_NAME']));
				}
			}
		}

		// Remove slashes from the start and end of the URI
		Router::$current_uri = trim(Router::$current_uri, '/');

		if (Router::$current_uri !== '')
		{
			if ($suffix = Kohana::config('core.url_suffix') AND strpos(Router::$current_uri, $suffix) !== FALSE)
			{
				// Remove the URL suffix
				Router::$current_uri = preg_replace('#'.preg_quote($suffix).'$#u', '', Router::$current_uri);

				// Set the URL suffix
				Router::$url_suffix = $suffix;
			}

			// Reduce multiple slashes into single slashes
			Router::$current_uri = preg_replace('#//+#', '/', Router::$current_uri);
		}
	}

	/**
	 * Generates routed URI from given URI.
	 *
	 * @param  string  URI to convert
	 * @return string  Routed uri
	 */
	public static function routed_uri($uri)
	{
		if (Router::$routes === NULL)
		{
			// Load routes
			Router::$routes = Kohana::config('routes');
		}

		// Prepare variables
		$routed_uri = $uri = trim($uri, '/');

		if (isset(Router::$routes[$uri]))
		{
			// Literal match, no need for regex
			$routed_uri = Router::$routes[$uri];
		}
		else
		{
			// Loop through the routes and see if anything matches
			foreach (Router::$routes as $key => $val)
			{
				if ($key === '_default') continue;

				// Trim slashes
				$key = trim($key, '/');
				$val = trim($val, '/');

				if (preg_match('#^'.$key.'$#u', $uri))
				{
					if (strpos($val, '$') !== FALSE)
					{
						// Use regex routing
						$routed_uri = preg_replace('#^'.$key.'$#u', $val, $uri);
					}
					else
					{
						// Standard routing
						$routed_uri = $val;
					}

					// A valid route has been found
					break;
				}
			}
		}

		if (isset(Router::$routes[$routed_uri]))
		{
			// Check for double routing (without regex)
			$routed_uri = Router::$routes[$routed_uri];
		}

		return trim($routed_uri, '/');
	}

} // End Router