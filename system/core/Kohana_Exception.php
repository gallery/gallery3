<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Kohana Exceptions
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

class Kohana_Exception_Core extends Exception {

	public static $enabled = FALSE;

	// Template file
	public static $template = 'kohana/error';

	// Show stack traces in errors
	public static $trace_output = TRUE;

	// Show source code in errors
	public static $source_output = TRUE;

	// To hold unique identifier to distinguish error output
	protected $instance_identifier;

	// Error code
	protected $code = E_KOHANA;

	/**
	 * Creates a new translated exception.
	 *
	 * @param string error message
	 * @param array translation variables
	 * @return void
	 */
	public function __construct($message, array $variables = NULL, $code = 0)
	{
		$this->instance_identifier = uniqid();

		// Translate the error message
		$message = __($message, $variables);

		// Sets $this->message the proper way
		parent::__construct($message, $code);
	}

	/**
	 * Enable Kohana exception handling.
	 *
	 * @uses    Kohana_Exception::$template
	 * @return  void
	 */
	public static function enable()
	{
		if ( ! Kohana_Exception::$enabled)
		{
			set_exception_handler(array('Kohana_Exception', 'handle'));

			Kohana_Exception::$enabled = TRUE;
		}
	}

	/**
	 * Disable Kohana exception handling.
	 *
	 * @return  void
	 */
	public static function disable()
	{
		if (Kohana_Exception::$enabled)
		{
			restore_exception_handler();

			Kohana_Exception::$enabled = FALSE;
		}
	}

	/**
	 * Get a single line of text representing the exception:
	 *
	 * Error [ Code ]: Message ~ File [ Line ]
	 *
	 * @param   object  Exception
	 * @return  string
	 */
	public static function text($e)
	{
		return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
			get_class($e), $e->getCode(), strip_tags($e->getMessage()), Kohana_Exception::debug_path($e->getFile()), $e->getLine());
	}

	/**
	 * exception handler, displays the error message, source of the
	 * exception, and the stack trace of the error.
	 *
	 * @uses    Kohana::message()
	 * @uses    Kohana_Exception::text()
	 * @param   object   exception object
	 * @return  void
	 */
	public static function handle(Exception $e)
	{
		try
		{
			// Get the exception information
			$type    = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();

			// Create a text version of the exception
			$error = Kohana_Exception::text($e);

			// Add this exception to the log
			Kohana_Log::add('error', $error);

			// Manually save logs after exceptions
			Kohana_Log::save();

			if (Kohana::config('kohana/core.display_errors') === FALSE)
			{
				// Do not show the details
				$file = $line = NULL;
				$trace = array();

				$template = '_disabled';
			}
			else
			{
				$file = $e->getFile();
				$line = $e->getLine();
				$trace = $e->getTrace();

				$template = '';
			}

			if ($e instanceof Kohana_Exception)
			{
				$template = $e->getTemplate().$template;

				if ( ! headers_sent())
				{
					$e->sendHeaders();
				}

				// Use the human-readable error name
				$code = Kohana::message('kohana/core.errors.'.$code);
			}
			else
			{
				$template = Kohana_Exception::$template.$template;

				if ( ! headers_sent())
				{
					header('HTTP/1.1 500 Internal Server Error');
				}

				if ($e instanceof ErrorException)
				{
					// Use the human-readable error name
					$code = Kohana::message('kohana/core.errors.'.$e->getSeverity());

					if (version_compare(PHP_VERSION, '5.3', '<'))
					{
						// Workaround for a bug in ErrorException::getTrace() that exists in
						// all PHP 5.2 versions. @see http://bugs.php.net/45895
						for ($i = count($trace) - 1; $i > 0; --$i)
						{
							if (isset($trace[$i - 1]['args']))
							{
								// Re-position the arguments
								$trace[$i]['args'] = $trace[$i - 1]['args'];

								unset($trace[$i - 1]['args']);
							}
						}
					}
				}
			}

			// Clean the output buffer if one exists
			ob_get_level() and ob_clean();

			if ($template = Kohana::find_file('views', $template))
			{
				include $template;
			}
		}
		catch (Exception $e)
		{
			// Clean the output buffer if one exists
			ob_get_level() and ob_clean();

			// Display the exception text
			echo Kohana_Exception::text($e), "\n";
		}

		if (Kohana::$server_api === 'cli')
		{
			// Exit with an error status
			exit(1);
		}
	}

	/**
	 * Returns the template for this exception.
	 *
	 * @uses    Kohana_Exception::$template
	 * @return  string
	 */
	public function getTemplate()
	{
		return Kohana_Exception::$template;
	}

	/**
	 * Sends an Internal Server Error header.
	 *
	 * @return  void
	 */
	public function sendHeaders()
	{
		// Send the 500 header
		header('HTTP/1.1 500 Internal Server Error');
	}

	/**
	 * Returns an HTML string of information about a single variable.
	 *
	 * Borrows heavily on concepts from the Debug class of {@link http://nettephp.com/ Nette}.
	 *
	 * @param   mixed    variable to dump
	 * @param   integer  maximum length of strings
	 * @param   integer  maximum levels of recursion
	 * @return  string
	 */
	public static function dump($value, $length = 128, $max_level = 5)
	{
		return Kohana_Exception::_dump($value, $length, $max_level);
	}

	/**
	 * Helper for Kohana_Exception::dump(), handles recursion in arrays and objects.
	 *
	 * @param   mixed    variable to dump
	 * @param   integer  maximum length of strings
	 * @param   integer  maximum levels of recursion
	 * @param   integer  current recursion level (internal)
	 * @return  string
	 */
	private static function _dump( & $var, $length = 128, $max_level = 5, $level = 0)
	{
		if ($var === NULL)
		{
			return '<small>NULL</small>';
		}
		elseif (is_bool($var))
		{
			return '<small>bool</small> '.($var ? 'TRUE' : 'FALSE');
		}
		elseif (is_float($var))
		{
			return '<small>float</small> '.$var;
		}
		elseif (is_resource($var))
		{
			if (($type = get_resource_type($var)) === 'stream' AND $meta = stream_get_meta_data($var))
			{
				$meta = stream_get_meta_data($var);

				if (isset($meta['uri']))
				{
					$file = $meta['uri'];

					if (function_exists('stream_is_local'))
					{
						// Only exists on PHP >= 5.2.4
						if (stream_is_local($file))
						{
							$file = Kohana_Exception::debug_path($file);
						}
					}

					return '<small>resource</small><span>('.$type.')</span> '.htmlspecialchars($file, ENT_NOQUOTES, Kohana::CHARSET);
				}
			}
			else
			{
				return '<small>resource</small><span>('.$type.')</span>';
			}
		}
		elseif (is_string($var))
		{
			if (strlen($var) > $length)
			{
				// Encode the truncated string
				$str = htmlspecialchars(substr($var, 0, $length), ENT_NOQUOTES, Kohana::CHARSET).'&nbsp;&hellip;';
			}
			else
			{
				// Encode the string
				$str = htmlspecialchars($var, ENT_NOQUOTES, Kohana::CHARSET);
			}

			return '<small>string</small><span>('.strlen($var).')</span> "'.$str.'"';
		}
		elseif (is_array($var))
		{
			$output = array();

			// Indentation for this variable
			$space = str_repeat($s = '    ', $level);

			static $marker;

			if ($marker === NULL)
			{
				// Make a unique marker
				$marker = uniqid("\x00");
			}

			if (empty($var))
			{
				// Do nothing
			}
			elseif (isset($var[$marker]))
			{
				$output[] = "(\n$space$s*RECURSION*\n$space)";
			}
			elseif ($level <= $max_level)
			{
				$output[] = "<span>(";

				$var[$marker] = TRUE;
				foreach ($var as $key => & $val)
				{
					if ($key === $marker) continue;
					if ( ! is_int($key))
					{
						$key = '"'.$key.'"';
					}

					$output[] = "$space$s$key => ".Kohana_Exception::_dump($val, $length, $max_level, $level + 1);
				}
				unset($var[$marker]);

				$output[] = "$space)</span>";
			}
			else
			{
				// Depth too great
				$output[] = "(\n$space$s...\n$space)";
			}

			return '<small>array</small><span>('.count($var).')</span> '.implode("\n", $output);
		}
		elseif (is_object($var))
		{
			// Copy the object as an array
			$array = (array) $var;

			$output = array();

			// Indentation for this variable
			$space = str_repeat($s = '    ', $level);

			$hash = spl_object_hash($var);

			// Objects that are being dumped
			static $objects = array();

			if (empty($var))
			{
				// Do nothing
			}
			elseif (isset($objects[$hash]))
			{
				$output[] = "{\n$space$s*RECURSION*\n$space}";
			}
			elseif ($level <= $max_level)
			{
				$output[] = "<code>{";

				$objects[$hash] = TRUE;
				foreach ($array as $key => & $val)
				{
					if ($key[0] === "\x00")
					{
						// Determine if the access is private or protected
						$access = '<small>'.($key[1] === '*' ? 'protected' : 'private').'</small>';

						// Remove the access level from the variable name
						$key = substr($key, strrpos($key, "\x00") + 1);
					}
					else
					{
						$access = '<small>public</small>';
					}

					$output[] = "$space$s$access $key => ".Kohana_Exception::_dump($val, $length, $max_level, $level + 1);
				}
				unset($objects[$hash]);

				$output[] = "$space}</code>";
			}
			else
			{
				// Depth too great
				$output[] = "{\n$space$s...\n$space}";
			}

			return '<small>object</small> <span>'.get_class($var).'('.count($array).')</span> '.implode("\n", $output);
		}
		else
		{
			return '<small>'.gettype($var).'</small> '.htmlspecialchars(print_r($var, TRUE), ENT_NOQUOTES, Kohana::CHARSET);
		}
	}

	/**
	 * Removes APPPATH, SYSPATH, MODPATH, and DOCROOT from filenames, replacing
	 * them with the plain text equivalents.
	 *
	 * @param   string  path to sanitize
	 * @return  string
	 */
	public static function debug_path($file)
	{
		// Normalize directory separator
		$file = str_replace('\\', '/', $file);

		if (strpos($file, APPPATH) === 0)
		{
			$file = 'APPPATH/'.substr($file, strlen(APPPATH));
		}
		elseif (strpos($file, SYSPATH) === 0)
		{
			$file = 'SYSPATH/'.substr($file, strlen(SYSPATH));
		}
		elseif (strpos($file, MODPATH) === 0)
		{
			$file = 'MODPATH/'.substr($file, strlen(MODPATH));
		}
		elseif (strpos($file, DOCROOT) === 0)
		{
			$file = 'DOCROOT/'.substr($file, strlen(DOCROOT));
		}

		return $file;
	}

	/**
	 * Returns an array of lines from a file.
	 *
	 *     // Returns the current line of the current file
	 *     echo Kohana_Exception::debug_source(__FILE__, __LINE__);
	 *
	 * @param   string   file to open
	 * @param   integer  line number to find
	 * @param   integer  number of padding lines
	 * @return  array
	 */
	public static function debug_source($file, $line_number, $padding = 5)
	{
		// Make sure we can read the source file
		if ( ! is_readable($file))
			return array();

		// Open the file and set the line position
		$file = fopen($file, 'r');
		$line = 0;

		// Set the reading range
		$range = array('start' => $line_number - $padding, 'end' => $line_number + $padding);

		// Set the zero-padding amount for line numbers
		$format = '% '.strlen($range['end']).'d';

		$source = array();
		while (($row = fgets($file)) !== FALSE)
		{
			// Increment the line number
			if (++$line > $range['end'])
				break;

			if ($line >= $range['start'])
			{
				$source[sprintf($format, $line)] = $row;
			}
		}

		// Close the file
		fclose($file);

		return $source;
	}

	/**
	 * Returns an array of strings that represent each step in the backtrace.
	 *
	 * @param   array  trace to analyze
	 * @return  array
	 */
	public static function trace($trace = NULL)
	{
		if ($trace === NULL)
		{
			// Start a new trace
			$trace = debug_backtrace();
		}

		// Non-standard function calls
		$statements = array('include', 'include_once', 'require', 'require_once');

		$output = array();
		foreach ($trace as $step)
		{
			if ( ! isset($step['function']))
			{
				// Invalid trace step
				continue;
			}

			if (isset($step['file']) AND isset($step['line']))
			{
				// Include the source of this step
				$source = Kohana_Exception::debug_source($step['file'], $step['line']);
			}

			if (isset($step['file']))
			{
				$file = $step['file'];

				if (isset($step['line']))
				{
					$line = $step['line'];
				}
			}

			// function()
			$function = $step['function'];

			if (in_array($step['function'], $statements))
			{
				if (empty($step['args']))
				{
					// No arguments
					$args = array();
				}
				else
				{
					// Sanitize the file path
					$args = array($step['args'][0]);
				}
			}
			elseif (isset($step['args']))
			{
				if ($step['function'] === '{closure}')
				{
					// Introspection on closures in a stack trace is impossible
					$params = NULL;
				}
				else
				{
					if (isset($step['class']))
					{
						if (method_exists($step['class'], $step['function']))
						{
							$reflection = new ReflectionMethod($step['class'], $step['function']);
						}
						else
						{
							$reflection = new ReflectionMethod($step['class'], '__call');
						}
					}
					else
					{
						$reflection = new ReflectionFunction($step['function']);
					}

					// Get the function parameters
					$params = $reflection->getParameters();
				}

				$args = array();

				foreach ($step['args'] as $i => $arg)
				{
					if (isset($params[$i]))
					{
						// Assign the argument by the parameter name
						$args[$params[$i]->name] = $arg;
					}
					else
					{
						// Assign the argument by number
						$args[$i] = $arg;
					}
				}
			}

			if (isset($step['class']))
			{
				// Class->method() or Class::method()
				$function = $step['class'].$step['type'].$step['function'];
			}

			$output[] = array(
				'function' => $function,
				'args'     => isset($args)   ? $args : NULL,
				'file'     => isset($file)   ? $file : NULL,
				'line'     => isset($line)   ? $line : NULL,
				'source'   => isset($source) ? $source : NULL,
			);

			unset($function, $args, $file, $line, $source);
		}

		return $output;
	}

} // End Kohana Exception
