<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Download helper class.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class download_Core {

	/**
	 * Send headers necessary to invoke a "Save As" dialog
	 *
	 * @link http://support.microsoft.com/kb/260519
	 * @link http://greenbytes.de/tech/tc2231/
	 *
	 * @param   string  file name
	 * @return  string  file name as it was sent
	 */
	public static function dialog($filename)
	{
		$filename = basename($filename);

		header('Content-Disposition: attachment; filename="'.$filename.'"');

		return $filename;
	}

	/**
	 * Send the contents of a file or a data string with the proper MIME type and exit.
	 *
	 * @uses exit()
	 * @uses Kohana::close_buffers()
	 *
	 * @param   string  a file path or file name
	 * @param   string  optional data to send
	 * @return  void
	 */
	public static function send($filename, $data = NULL)
	{
		if ($data === NULL)
		{
			$filepath = realpath($filename);

			$filename = basename($filepath);
			$filesize = filesize($filepath);
		}
		else
		{
			$filename = basename($filename);
			$filesize = strlen($data);
		}

		// Retrieve MIME type by extension
		$mime = Kohana::config('mimes.'.strtolower(substr(strrchr($filename, '.'), 1)));
		$mime = empty($mime) ? 'application/octet-stream' : $mime[0];

		// Close output buffers
		Kohana::close_buffers(FALSE);

		// Clear any output
		Event::add('system.display', create_function('', 'Kohana::$output = "";'));

		// Send headers
		header("Content-Type: $mime");
		header('Content-Length: '.sprintf('%d', $filesize));
		header('Content-Transfer-Encoding: binary');

		// Send data
		if ($data === NULL)
		{
			$handle = fopen($filepath, 'rb');

			fpassthru($handle);
			fclose($handle);
		}
		else
		{
			echo $data;
		}

		exit;
	}

	/**
	 * Force the download of a file by the user's browser by preventing any
	 * caching. Contains a workaround for Internet Explorer.
	 *
	 * @link http://support.microsoft.com/kb/316431
	 * @link http://support.microsoft.com/kb/812935
	 *
	 * @uses download::dialog()
	 * @uses download::send()
	 *
	 * @param   string  a file path or file name
	 * @param   mixed   data to be sent if the filename does not exist
	 * @param   string  suggested filename to display in the download
	 * @return  void
	 */
	public static function force($filename = NULL, $data = NULL, $nicename = NULL)
	{
		download::dialog(empty($nicename) ? $filename : $nicename);

		// Prevent caching
		header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

		if (request::user_agent('browser') === 'Internet Explorer' AND request::user_agent('version') <= '6.0')
		{
			// HTTP 1.0
			header('Pragma:');

			// HTTP 1.1 with IE extensions
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		}
		else
		{
			// HTTP 1.0
			header('Pragma: no-cache');

			// HTTP 1.1
			header('Cache-Control: no-cache, max-age=0');
		}

		if (is_file($filename))
		{
			download::send($filename);
		}
		else
		{
			download::send($filename, $data);
		}
	}

} // End download
