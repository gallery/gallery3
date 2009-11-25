<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Log API driver.
 *
 * @package    Kohana_Log
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Log_Syslog_Driver extends Log_Driver {

	protected $syslog_levels = array('error' => LOG_ERR,
	                                 'alert' => LOG_WARNING,
	                                 'info'  => LOG_INFO,
	                                 'debug' => LOG_DEBUG);

	public function save(array $messages)
	{
		// Open the connection to syslog
		openlog($this->config['ident'], LOG_CONS, LOG_USER);

		do
		{
			// Load the next message
			list ($date, $type, $text) = array_shift($messages);

			syslog($this->syslog_levels[$type], $text);
		}
		while ( ! empty($messages));

		// Close connection to syslog
		closelog();
	}
}