<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Log API driver.
 *
 * $Id: File.php 4679 2009-11-10 01:45:52Z isaiah $
 *
 * @package    Kohana_Log
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Log_File_Driver extends Log_Driver {

	public function save(array $messages)
	{
		// Filename of the log
		$filename = $this->config['log_directory'].'/'.date('Y-m-d').'.log'.EXT;

		if ( ! is_file($filename))
		{
			// Write the SYSPATH checking header
			file_put_contents($filename,
				'<?php defined(\'SYSPATH\') or die(\'No direct script access.\'); ?>'.PHP_EOL.PHP_EOL);

			// Prevent external writes
			chmod($filename, $this->config['posix_permissions']);
		}

		foreach ($messages AS $message)
		{
			if ($this->config['log_levels'][$message['type']] <= $this->config['log_threshold'])
			{
				// Add a new message line
				$messages_to_write[] = date($this->config['date_format'], $message['date']).' --- '.$message['type'].': '.$message['message'];
			}
		}

		if ( ! empty($messages_to_write))
		{
			// Write messages to log file
			file_put_contents($filename, implode(PHP_EOL, $messages_to_write).PHP_EOL, FILE_APPEND);
		}
	}
}