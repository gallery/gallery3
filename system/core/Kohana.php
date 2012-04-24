<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Provides Kohana-specific helper functions. This is where the magic happens!
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

// Test of Kohana is running in Windows
define('KOHANA_IS_WIN', DIRECTORY_SEPARATOR === '\\');

abstract class Kohana_Core {

	const VERSION  = '2.4';
	const CODENAME = 'no_codename';
	const CHARSET  = 'UTF-8';
	const LOCALE = 'en_US';

	// The singleton instance of the controller
	public static $instance;

	// Output buffering level
	protected static $buffer_level;

	// The final output that will displayed by Kohana
	public static $output = '';

	// The current locale
	public static $locale;

	// Include paths
	protected static $include_paths;
	protected static $include_paths_hash = '';

	// Cache lifetime
	protected static $cache_lifetime;

	// Internal caches and write status
	protected static $internal_cache = array();
	protected static $write_cache;
	protected static $internal_cache_path;
	protected static $internal_cache_key;
	protected static $internal_cache_encrypt;

	// Server API that PHP is using. Allows testing of different APIs.
	public static $server_api = PHP_SAPI;

	/**
	 * Sets up the PHP environment. Adds error/exception handling, output
	 * buffering, and adds an auto-loading method for loading classes.
	 *
	 * This method is run immediately when this file is loaded, and is
	 * benchmarked as environment_setup.
	 *
	 * For security, this function also destroys the $_REQUEST global variable.
	 * Using the proper global (GET, POST, COOKIE, etc) is inherently more secure.
	 * The recommended way to fetch a global variable is using the Input library.
	 * @see http://www.php.net/globals
	 *
	 * @return  void
	 */
	public static function setup()
	{
		static $run;

		// Only run this function once
		if ($run === TRUE)
			return;

		$run = TRUE;

		// Start the environment setup benchmark
		Benchmark::start(SYSTEM_BENCHMARK.'_environment_setup');

		// Define Kohana error constant
		define('E_KOHANA', 42);

		// Define 404 error constant
		define('E_PAGE_NOT_FOUND', 43);

		// Define database error constant
		define('E_DATABASE_ERROR', 44);

		// Set the default charset for mb_* functions
		mb_internal_encoding(Kohana::CHARSET);

		if (Kohana_Config::instance()->loaded() === FALSE)
		{
			// Re-parse the include paths
			Kohana::include_paths(TRUE);
		}

		if (Kohana::$cache_lifetime = Kohana::config('core.internal_cache'))
		{
			// Are we using encryption for caches?
			Kohana::$internal_cache_encrypt	= Kohana::config('core.internal_cache_encrypt');

			if(Kohana::$internal_cache_encrypt===TRUE)
			{
				Kohana::$internal_cache_key = Kohana::config('core.internal_cache_key');

				// Be sure the key is of acceptable length for the mcrypt algorithm used
				Kohana::$internal_cache_key = substr(Kohana::$internal_cache_key, 0, 24);
			}

			// Set the directory to be used for the internal cache
			if ( ! Kohana::$internal_cache_path = Kohana::config('core.internal_cache_path'))
			{
				Kohana::$internal_cache_path = APPPATH.'cache/';
			}

			// Load cached configuration and language files
			Kohana::$internal_cache['configuration'] = Kohana::cache('configuration', Kohana::$cache_lifetime);
			Kohana::$internal_cache['language']      = Kohana::cache('language', Kohana::$cache_lifetime);

			// Load cached file paths
			Kohana::$internal_cache['find_file_paths'] = Kohana::cache('find_file_paths', Kohana::$cache_lifetime);

			// Enable cache saving
			Event::add('system.shutdown', array('Kohana', 'internal_cache_save'));
		}

		// Start output buffering
		ob_start(array('Kohana', 'output_buffer'));

		// Save buffering level
		Kohana::$buffer_level = ob_get_level();

		// Set autoloader
		spl_autoload_register(array('Kohana', 'auto_load'));

		// Register a shutdown function to handle system.shutdown events
		register_shutdown_function(array('Kohana', 'shutdown'));

		// Send default text/html UTF-8 header
		header('Content-Type: text/html; charset='.Kohana::CHARSET);

		// Load i18n
		new I18n;

		// Enable exception handling
		Kohana_Exception::enable();

		// Enable error handling
		Kohana_PHP_Exception::enable();

		// Load locales
		$locales = Kohana::config('locale.language');

		// Make first locale the defined Kohana charset
		$locales[0] .= '.'.Kohana::CHARSET;

		// Set locale information
		Kohana::$locale = setlocale(LC_ALL, $locales);

		// Default to the default locale when none of the user defined ones where accepted
		Kohana::$locale = ! Kohana::$locale ? Kohana::LOCALE.'.'.Kohana::CHARSET : Kohana::$locale;

		// Set locale for the I18n system
		I18n::set_locale(Kohana::$locale);

		// Set and validate the timezone
		date_default_timezone_set(Kohana::config('locale.timezone'));

		// register_globals is enabled
		if (ini_get('register_globals'))
		{
			if (isset($_REQUEST['GLOBALS']))
			{
				// Prevent GLOBALS override attacks
				exit('Global variable overload attack.');
			}

			// Destroy the REQUEST global
			$_REQUEST = array();

			// These globals are standard and should not be removed
			$preserve = array('GLOBALS', '_REQUEST', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER', '_ENV', '_SESSION');

			// This loop has the same effect as disabling register_globals
			foreach (array_diff(array_keys($GLOBALS), $preserve) as $key)
			{
				global $$key;
				$$key = NULL;

				// Unset the global variable
				unset($GLOBALS[$key], $$key);
			}

			// Warn the developer about register globals
			Kohana_Log::add('debug', 'Disable register_globals! It is evil and deprecated: http://php.net/register_globals');
		}

		// Enable Kohana routing
		Event::add('system.routing', array('Router', 'find_uri'));
		Event::add('system.routing', array('Router', 'setup'));

		// Enable Kohana controller initialization
		Event::add('system.execute', array('Kohana', 'instance'));

		// Enable Kohana 404 pages
		Event::add('system.404', array('Kohana_404_Exception', 'trigger'));

		if (Kohana::config('core.enable_hooks') === TRUE)
		{
			// Find all the hook files
			$hooks = Kohana::list_files('hooks', TRUE);

			foreach ($hooks as $file)
			{
				// Load the hook
				include $file;
			}
		}

		// Stop the environment setup routine
		Benchmark::stop(SYSTEM_BENCHMARK.'_environment_setup');
	}

	/**
	 * Cleans up the PHP environment. Disables error/exception handling and the
	 * auto-loading method and closes the output buffer.
	 *
	 * This method does not need to be called during normal system execution,
	 * however in some advanced situations it can be helpful. @see #1781
	 *
	 * @return  void
	 */
	public static function cleanup()
	{
		static $run;

		// Only run this function once
		if ($run === TRUE)
			return;

		$run = TRUE;

		Kohana_Exception::disable();

		Kohana_PHP_Exception::disable();

		spl_autoload_unregister(array('Kohana', 'auto_load'));

		Kohana::close_buffers();
	}

	/**
	 * Loads the controller and initializes it. Runs the pre_controller,
	 * post_controller_constructor, and post_controller events. Triggers
	 * a system.404 event when the route cannot be mapped to a controller.
	 *
	 * This method is benchmarked as controller_setup and controller_execution.
	 *
	 * @return  object  instance of controller
	 */
	public static function & instance()
	{
		if (Kohana::$instance === NULL)
		{
			Benchmark::start(SYSTEM_BENCHMARK.'_controller_setup');

			// Include the Controller file
			require_once Router::$controller_path;

			try
			{
				// Start validation of the controller
				$class = new ReflectionClass(ucfirst(Router::$controller).'_Controller');
			}
			catch (ReflectionException $e)
			{
				// Controller does not exist
				Event::run('system.404');
			}

			if ($class->isAbstract() OR (IN_PRODUCTION AND $class->getConstant('ALLOW_PRODUCTION') == FALSE))
			{
				// Controller is not allowed to run in production
				Event::run('system.404');
			}

			// Run system.pre_controller
			Event::run('system.pre_controller');

			// Create a new controller instance
			$controller = $class->newInstance();

			// Controller constructor has been executed
			Event::run('system.post_controller_constructor');

			try
			{
				// Load the controller method
				$method = $class->getMethod(Router::$method);

				// Method exists
				if (Router::$method[0] === '_')
				{
					// Do not allow access to hidden methods
					Event::run('system.404');
				}

				if ($method->isProtected() or $method->isPrivate())
				{
					// Do not attempt to invoke protected methods
					throw new ReflectionException('protected controller method');
				}

				// Default arguments
				$arguments = Router::$arguments;
			}
			catch (ReflectionException $e)
			{
				// Use __call instead
				$method = $class->getMethod('__call');

				// Use arguments in __call format
				$arguments = array(Router::$method, Router::$arguments);
			}

			// Stop the controller setup benchmark
			Benchmark::stop(SYSTEM_BENCHMARK.'_controller_setup');

			// Start the controller execution benchmark
			Benchmark::start(SYSTEM_BENCHMARK.'_controller_execution');

			// Execute the controller method
			$method->invokeArgs($controller, $arguments);

			// Controller method has been executed
			Event::run('system.post_controller');

			// Stop the controller execution benchmark
			Benchmark::stop(SYSTEM_BENCHMARK.'_controller_execution');
		}

		return Kohana::$instance;
	}

	/**
	 * Get all include paths. APPPATH is the first path, followed by module
	 * paths in the order they are configured, follow by the SYSPATH.
	 *
	 * @param   boolean  re-process the include paths
	 * @return  array
	 */
	public static function include_paths($process = FALSE)
	{
		if ($process === TRUE)
		{
			// Add APPPATH as the first path
			Kohana::$include_paths = array(APPPATH);

			foreach (Kohana::config('core.modules') as $path)
			{
				if ($path = str_replace('\\', '/', realpath($path)))
				{
					// Add a valid path
					Kohana::$include_paths[] = $path.'/';
				}
			}

			// Add SYSPATH as the last path
			Kohana::$include_paths[] = SYSPATH;

			Kohana::$include_paths_hash = md5(serialize(Kohana::$include_paths));
		}

		return Kohana::$include_paths;
	}

	/**
	 * Get a config item or group proxies Kohana_Config.
	 *
	 * @param   string   item name
	 * @param   boolean  force a forward slash (/) at the end of the item
	 * @param   boolean  is the item required?
	 * @return  mixed
	 */
	public static function config($key, $slash = FALSE, $required = FALSE)
	{
		return Kohana_Config::instance()->get($key,$slash,$required);
	}

	/**
	 * Load data from a simple cache file. This should only be used internally,
	 * and is NOT a replacement for the Cache library.
	 *
	 * @param   string   unique name of cache
	 * @param   integer  expiration in seconds
	 * @return  mixed
	 */
	public static function cache($name, $lifetime)
	{
		if ($lifetime > 0)
		{
			$path = Kohana::$internal_cache_path.'kohana_'.$name;

			if (is_file($path))
			{
				// Check the file modification time
				if ((time() - filemtime($path)) < $lifetime)
				{
					// Cache is valid! Now, do we need to decrypt it?
					if(Kohana::$internal_cache_encrypt===TRUE)
					{
						$data		= file_get_contents($path);

						$iv_size	= mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
						$iv			= mcrypt_create_iv($iv_size, MCRYPT_RAND);

						$decrypted_text	= mcrypt_decrypt(MCRYPT_RIJNDAEL_256, Kohana::$internal_cache_key, $data, MCRYPT_MODE_ECB, $iv);

						$cache	= unserialize($decrypted_text);

						// If the key changed, delete the cache file
						if(!$cache)
							unlink($path);

						// If cache is false (as above) return NULL, otherwise, return the cache
						return ($cache ? $cache : NULL);
					}
					else
					{
						return unserialize(file_get_contents($path));
					}
				}
				else
				{
					// Cache is invalid, delete it
					unlink($path);
				}
			}
		}

		// No cache found
		return NULL;
	}

	/**
	 * Save data to a simple cache file. This should only be used internally, and
	 * is NOT a replacement for the Cache library.
	 *
	 * @param   string   cache name
	 * @param   mixed    data to cache
	 * @param   integer  expiration in seconds
	 * @return  boolean
	 */
	public static function cache_save($name, $data, $lifetime)
	{
		if ($lifetime < 1)
			return FALSE;

		$path = Kohana::$internal_cache_path.'kohana_'.$name;

		if ($data === NULL)
		{
			// Delete cache
			return (is_file($path) and unlink($path));
		}
		else
		{
			// Using encryption? Encrypt the data when we write it
			if(Kohana::$internal_cache_encrypt===TRUE)
			{
				// Encrypt and write data to cache file
				$iv_size	= mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
				$iv			= mcrypt_create_iv($iv_size, MCRYPT_RAND);

				// Serialize and encrypt!
				$encrypted_text	= mcrypt_encrypt(MCRYPT_RIJNDAEL_256, Kohana::$internal_cache_key, serialize($data), MCRYPT_MODE_ECB, $iv);

				return (bool) file_put_contents($path, $encrypted_text);
			}
			else
			{
				// Write data to cache file
				return (bool) file_put_contents($path, serialize($data));
			}
		}
	}

	/**
	 * Kohana output handler. Called during ob_clean, ob_flush, and their variants.
	 *
	 * @param   string  current output buffer
	 * @return  string
	 */
	public static function output_buffer($output)
	{
		// Could be flushing, so send headers first
		if ( ! Event::has_run('system.send_headers'))
		{
			// Run the send_headers event
			Event::run('system.send_headers');
		}

		// Set final output
		Kohana::$output = $output;

		// Set and return the final output
		return Kohana::$output;
	}

	/**
	 * Closes all open output buffers, either by flushing or cleaning, and stores
	 * output buffer for display during shutdown.
	 *
	 * @param   boolean  disable to clear buffers, rather than flushing
	 * @return  void
	 */
	public static function close_buffers($flush = TRUE)
	{
		if (ob_get_level() >= Kohana::$buffer_level)
		{
			// Set the close function
			$close = ($flush === TRUE) ? 'ob_end_flush' : 'ob_end_clean';

			while (ob_get_level() > Kohana::$buffer_level)
			{
				// Flush or clean the buffer
				$close();
			}

			// Store the Kohana output buffer.  Apparently there was a change in PHP
			// 5.4 such that if you call this you wind up with a blank page.
			// Disabling it for now.  See ticket #1839
			if (version_compare(PHP_VERSION, "5.4", "<")) {
				ob_end_clean();
			}
		}
	}

	/**
	 * Triggers the shutdown of Kohana by closing the output buffer, runs the system.display event.
	 *
	 * @return  void
	 */
	public static function shutdown()
	{
		static $run;

		// Only run this function once
		if ($run === TRUE)
			return;

		$run = TRUE;

		// Run system.shutdown event
		Event::run('system.shutdown');

		// Close output buffers
		Kohana::close_buffers(TRUE);

		// Run the output event
		Event::run('system.display', Kohana::$output);

		// Render the final output
		Kohana::render(Kohana::$output);
	}

	/**
	 * Inserts global Kohana variables into the generated output and prints it.
	 *
	 * @param   string  final output that will displayed
	 * @return  void
	 */
	public static function render($output)
	{
		if (Kohana::config('core.render_stats') === TRUE)
		{
			// Fetch memory usage in MB
			$memory = function_exists('memory_get_usage') ? (memory_get_usage() / 1024 / 1024) : 0;

			// Fetch benchmark for page execution time
			$benchmark = Benchmark::get(SYSTEM_BENCHMARK.'_total_execution');

			// Replace the global template variables
			$output = str_replace(
				array
				(
					'{kohana_version}',
					'{kohana_codename}',
					'{execution_time}',
					'{memory_usage}',
					'{included_files}',
				),
				array
				(
					KOHANA::VERSION,
					KOHANA::CODENAME,
					$benchmark['time'],
					number_format($memory, 2).'MB',
					count(get_included_files()),
				),
				$output
			);
		}

		if ($level = Kohana::config('core.output_compression') AND ini_get('output_handler') !== 'ob_gzhandler' AND (int) ini_get('zlib.output_compression') === 0)
		{
			if ($compress = request::preferred_encoding(array('gzip','deflate'), TRUE))
			{
				if ($level < 1 OR $level > 9)
				{
					// Normalize the level to be an integer between 1 and 9. This
					// step must be done to prevent gzencode from triggering an error
					$level = max(1, min($level, 9));
				}

				if ($compress === 'gzip')
				{
					// Compress output using gzip
					$output = gzencode($output, $level);
				}
				elseif ($compress === 'deflate')
				{
					// Compress output using zlib (HTTP deflate)
					$output = gzdeflate($output, $level);
				}

				// This header must be sent with compressed content to prevent
				// browser caches from breaking
				header('Vary: Accept-Encoding');

				// Send the content encoding header
				header('Content-Encoding: '.$compress);

				// Sending Content-Length in CGI can result in unexpected behavior
				if (stripos(Kohana::$server_api, 'cgi') === FALSE)
				{
					header('Content-Length: '.strlen($output));
				}
			}
		}

		echo $output;
	}

	/**
	 * Provides class auto-loading.
	 *
	 * @throws  Kohana_Exception
	 * @param   string  name of class
	 * @return  bool
	 */
	public static function auto_load($class)
	{
		if (class_exists($class, FALSE) OR interface_exists($class, FALSE))
			return TRUE;

		if (($suffix = strrpos($class, '_')) > 0)
		{
			// Find the class suffix
			$suffix = substr($class, $suffix + 1);
		}
		else
		{
			// No suffix
			$suffix = FALSE;
		}

		if ($suffix === 'Core')
		{
			$type = 'libraries';
			$file = substr($class, 0, -5);
		}
		elseif ($suffix === 'Controller')
		{
			$type = 'controllers';
			// Lowercase filename
			$file = strtolower(substr($class, 0, -11));
		}
		elseif ($suffix === 'Model')
		{
			$type = 'models';
			// Lowercase filename
			$file = strtolower(substr($class, 0, -6));
		}
		elseif ($suffix === 'Driver')
		{
			$type = 'libraries/drivers';
			$file = str_replace('_', '/', substr($class, 0, -7));
		}
		else
		{
			// This could be either a library or a helper, but libraries must
			// always be capitalized, so we check if the first character is
			// uppercase. If it is, we are loading a library, not a helper.
			$type = ($class[0] < 'a') ? 'libraries' : 'helpers';
			$file = $class;
		}

		if ($filename = Kohana::find_file($type, $file))
		{
			// Load the class
			require $filename;
		}
		else
		{
			// The class could not be found
			return FALSE;
		}

		if ($filename = Kohana::find_file($type, Kohana::config('core.extension_prefix').$class))
		{
			// Load the class extension
			require $filename;
		}
		elseif ($suffix !== 'Core' AND class_exists($class.'_Core', FALSE))
		{
			// Class extension to be evaluated
			$extension = 'class '.$class.' extends '.$class.'_Core { }';

			// Start class analysis
			$core = new ReflectionClass($class.'_Core');

			if ($core->isAbstract())
			{
				// Make the extension abstract
				$extension = 'abstract '.$extension;
			}

			// Transparent class extensions are handled using eval. This is
			// a disgusting hack, but it gets the job done.
			eval($extension);
		}

		return TRUE;
	}

	/**
	 * Find a resource file in a given directory. Files will be located according
	 * to the order of the include paths. Configuration and i18n files will be
	 * returned in reverse order.
	 *
	 * @throws  Kohana_Exception  if file is required and not found
	 * @param   string   directory to search in
	 * @param   string   filename to look for (without extension)
	 * @param   boolean  file required
	 * @param   string   file extension
	 * @return  array    if the type is config, i18n or l10n
	 * @return  string   if the file is found
	 * @return  FALSE    if the file is not found
	 */
	public static function find_file($directory, $filename, $required = FALSE, $ext = FALSE)
	{
		// NOTE: This test MUST be not be a strict comparison (===), or empty
		// extensions will be allowed!
		if ($ext == '')
		{
			// Use the default extension
			$ext = EXT;
		}
		else
		{
			// Add a period before the extension
			$ext = '.'.$ext;
		}

		// Search path
		$search = $directory.'/'.$filename.$ext;

		if (isset(Kohana::$internal_cache['find_file_paths'][Kohana::$include_paths_hash][$search]))
			return Kohana::$internal_cache['find_file_paths'][Kohana::$include_paths_hash][$search];

		// Load include paths
		$paths = Kohana::$include_paths;

		// Nothing found, yet
		$found = NULL;

		if ($directory === 'config' OR $directory === 'messages' OR $directory === 'i18n')
		{
			// Search in reverse, for merging
			$paths = array_reverse($paths);

			foreach ($paths as $path)
			{
				if (is_file($path.$search))
				{
					// A matching file has been found
					$found[] = $path.$search;
				}
			}
		}
		else
		{
			foreach ($paths as $path)
			{
				if (is_file($path.$search))
				{
					// A matching file has been found
					$found = $path.$search;

					// Stop searching
					break;
				}
			}
		}

		if ($found === NULL)
		{
			if ($required === TRUE)
			{
				// If the file is required, throw an exception
				throw new Kohana_Exception('The requested :resource:, :file:, could not be found', array(':resource:' => __($directory), ':file:' =>$filename));
			}
			else
			{
				// Nothing was found, return FALSE
				$found = FALSE;
			}
		}

		if ( ! isset(Kohana::$write_cache['find_file_paths']))
		{
			// Write cache at shutdown
			Kohana::$write_cache['find_file_paths'] = TRUE;
		}

		return Kohana::$internal_cache['find_file_paths'][Kohana::$include_paths_hash][$search] = $found;
	}

	/**
	 * Lists all files and directories in a resource path.
	 *
	 * @param   string   directory to search
	 * @param   boolean  list all files to the maximum depth?
	 * @param   string   list all files having extension $ext
	 * @param   string   full path to search (used for recursion, *never* set this manually)
	 * @return  array    filenames and directories
	 */
	public static function list_files($directory, $recursive = FALSE, $ext = EXT, $path = FALSE)
	{
		$files = array();

		if ($path === FALSE)
		{
			$paths = array_reverse(Kohana::include_paths());

			foreach ($paths as $path)
			{
				// Recursively get and merge all files
				$files = array_merge($files, Kohana::list_files($directory, $recursive, $ext, $path.$directory));
			}
		}
		else
		{
			$path = rtrim($path, '/').'/';

			if (is_readable($path) AND $items = glob($path.'*'))
			{
				$ext_pos = 0 - strlen($ext);

				foreach ($items as $index => $item)
				{
					$item = str_replace('\\', '/', $item);

					if (is_dir($item))
					{
						// Handle recursion
						if ($recursive === TRUE)
						{
							// Filename should only be the basename
							$item = pathinfo($item, PATHINFO_BASENAME);

							// Append sub-directory search
							$files = array_merge($files, Kohana::list_files($directory, TRUE, $ext, $path.$item));
						}
					}
					else
					{
						// File extension must match
						if ($ext_pos === 0 OR substr($item, $ext_pos) === $ext)
						{
							$files[] = $item;
						}
					}
				}
			}
		}

		return $files;
	}


	/**
	 * Fetch a message item.
	 *
	 * @param   string  language key to fetch
	 * @param   array   additional information to insert into the line
	 * @return  string  i18n language string, or the requested key if the i18n item is not found
	 */
	public static function message($key, $args = array())
	{
		// Extract the main group from the key
		$group = explode('.', $key, 2);
		$group = $group[0];

		if ( ! isset(Kohana::$internal_cache['messages'][$group]))
		{
			// Messages for this group
			$messages = array();

			if ($file = Kohana::find_file('messages', $group))
			{
				include $file[0];
			}

			if ( ! isset(Kohana::$write_cache['messages']))
			{
				// Write language cache
				Kohana::$write_cache['messages'] = TRUE;
			}

			Kohana::$internal_cache['messages'][$group] = $messages;
		}

		// Get the line from cache
		$line = Kohana::key_string(Kohana::$internal_cache['messages'], $key);

		if ($line === NULL)
		{
			Kohana_Log::add('error', 'Missing messages entry '.$key.' for message '.$group);

			// Return the key string as fallback
			return $key;
		}

		if (is_string($line) AND func_num_args() > 1)
		{
			$args = array_slice(func_get_args(), 1);

			// Add the arguments into the line
			$line = vsprintf($line, is_array($args[0]) ? $args[0] : $args);
		}

		return $line;
	}

	/**
	 * Returns the value of a key, defined by a 'dot-noted' string, from an array.
	 *
	 * @param   array   array to search
	 * @param   string  dot-noted string: foo.bar.baz
	 * @return  string  if the key is found
	 * @return  void    if the key is not found
	 */
	public static function key_string($array, $keys)
	{
		if (empty($array))
			return NULL;

		// Prepare for loop
		$keys = explode('.', $keys);

		do
		{
			// Get the next key
			$key = array_shift($keys);

			if (isset($array[$key]))
			{
				if (is_array($array[$key]) AND ! empty($keys))
				{
					// Dig down to prepare the next loop
					$array = $array[$key];
				}
				else
				{
					// Requested key was found
					return $array[$key];
				}
			}
			else
			{
				// Requested key is not set
				break;
			}
		}
		while ( ! empty($keys));

		return NULL;
	}

	/**
	 * Sets values in an array by using a 'dot-noted' string.
	 *
	 * @param   array   array to set keys in (reference)
	 * @param   string  dot-noted string: foo.bar.baz
	 * @return  mixed   fill value for the key
	 * @return  void
	 */
	public static function key_string_set( & $array, $keys, $fill = NULL)
	{
		if (is_object($array) AND ($array instanceof ArrayObject))
		{
			// Copy the array
			$array_copy = $array->getArrayCopy();

			// Is an object
			$array_object = TRUE;
		}
		else
		{
			if ( ! is_array($array))
			{
				// Must always be an array
				$array = (array) $array;
			}

			// Copy is a reference to the array
			$array_copy =& $array;
		}

		if (empty($keys))
			return $array;

		// Create keys
		$keys = explode('.', $keys);

		// Create reference to the array
		$row =& $array_copy;

		for ($i = 0, $end = count($keys) - 1; $i <= $end; $i++)
		{
			// Get the current key
			$key = $keys[$i];

			if ( ! isset($row[$key]))
			{
				if (isset($keys[$i + 1]))
				{
					// Make the value an array
					$row[$key] = array();
				}
				else
				{
					// Add the fill key
					$row[$key] = $fill;
				}
			}
			elseif (isset($keys[$i + 1]))
			{
				// Make the value an array
				$row[$key] = (array) $row[$key];
			}

			// Go down a level, creating a new row reference
			$row =& $row[$key];
		}

		if (isset($array_object))
		{
			// Swap the array back in
			$array->exchangeArray($array_copy);
		}
	}

	/**
	 * Quick debugging of any variable. Any number of parameters can be set.
	 *
	 * @return  string
	 */
	public static function debug()
	{
		if (func_num_args() === 0)
			return;

		// Get params
		$params = func_get_args();
		$output = array();

		foreach ($params as $var)
		{
			$value = is_bool($var) ? ($var ? 'true' : 'false') : print_r($var, TRUE);
			$output[] = '<pre>('.gettype($var).') '.htmlspecialchars($value, ENT_QUOTES, Kohana::CHARSET).'</pre>';
		}

		return implode("\n", $output);
	}

	/**
	 * Saves the internal caches: configuration, include paths, etc.
	 *
	 * @return  boolean
	 */
	public static function internal_cache_save()
	{
		if ( ! is_array(Kohana::$write_cache))
			return FALSE;

		// Get internal cache names
		$caches = array_keys(Kohana::$write_cache);

		// Nothing written
		$written = FALSE;

		foreach ($caches as $cache)
		{
			if (isset(Kohana::$internal_cache[$cache]))
			{
				// Write the cache file
				Kohana::cache_save($cache, Kohana::$internal_cache[$cache], Kohana::config('core.internal_cache'));

				// A cache has been written
				$written = TRUE;
			}
		}

		return $written;
	}

} // End Kohana
