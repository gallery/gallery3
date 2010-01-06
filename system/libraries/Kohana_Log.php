<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Logging class.
 *
 * $Id: Kohana_Log.php 4729 2009-12-29 20:35:19Z isaiah $
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Log_Core {

	// Configuration
	protected static $config;

	// Drivers
	protected static $drivers;

	// Logged messages
	protected static $messages;

	/**
	 * Add a new message to the log.
	 *
	 * @param   string  type of message
	 * @param   string  message text
	 * @return  void
	 */
	public static function add($type, $message)
	{
		// Make sure the drivers and config are loaded
		if ( ! is_array(Kohana_Log::$config))
		{
			Kohana_Log::$config = Kohana::config('log');
		}

		if ( ! is_array(Kohana_Log::$drivers))
		{
			foreach ( (array) Kohana::config('log.drivers') as $driver_name)
			{
				// Set driver name
				$driver = 'Log_'.ucfirst($driver_name).'_Driver';

				// Load the driver
				if ( ! Kohana::auto_load($driver))
					throw new Kohana_Exception('Log Driver Not Found: %driver%', array('%driver%' => $driver));

				// Initialize the driver
				$driver = new $driver(array_merge(Kohana::config('log'), Kohana::config('log_'.$driver_name)));

				// Validate the driver
				if ( ! ($driver instanceof Log_Driver))
					throw new Kohana_Exception('%driver% does not implement the Log_Driver interface', array('%driver%' => $driver));

				Kohana_Log::$drivers[] = $driver;
			}

			// Always save logs on shutdown
			Event::add('system.shutdown', array('Kohana_Log', 'save'));
		}

		Kohana_Log::$messages[] = array('date' => time(), 'type' => $type, 'message' => $message);
	}

	/**
	 * Save all currently logged messages.
	 *
	 * @return  void
	 */
	public static function save()
	{
		if (empty(Kohana_Log::$messages))
			return;

		foreach (Kohana_Log::$drivers as $driver)
		{
			// We can't throw exceptions here or else we will get a
			// Exception thrown without a stack frame error
			try
			{
				$driver->save(Kohana_Log::$messages);
			}
			catch(Exception $e){}
		}

		// Reset the messages
		Kohana_Log::$messages = array();
	}
}