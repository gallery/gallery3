<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Process queuing/execution class. Allows an unlimited number of callbacks
 * to be added to 'events'. Events can be run multiple times, and can also
 * process event-specific data. By default, Kohana has several system events.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 * @link       http://docs.kohanaphp.com/general/events
 */
abstract class Event_Core {

	// Event callbacks
	protected static $events = array();

	// Cache of events that have been run
	protected static $has_run = array();

	// Data that can be processed during events
	public static $data;

	/**
	 * Add a callback to an event queue.
	 *
	 * @param   string  event name
	 * @param   array   http://php.net/callback
	 * @param   boolean prevent duplicates
	 * @return  boolean
	 */
	public static function add($name, $callback, $unique = FALSE)
	{
		if ( ! isset(Event::$events[$name]))
		{
			// Create an empty event if it is not yet defined
			Event::$events[$name] = array();
		}
		elseif ($unique AND in_array($callback, Event::$events[$name], TRUE))
		{
			// The event already exists
			return FALSE;
		}

		// Add the event
		Event::$events[$name][] = $callback;

		return TRUE;
	}

	/**
	 * Add a callback to an event queue, before a given event.
	 *
	 * @param   string   event name
	 * @param   array    existing event callback
	 * @param   array    event callback
	 * @return  boolean
	 */
	public static function add_before($name, $existing, $callback)
	{
		if (empty(Event::$events[$name]) OR ($key = array_search($existing, Event::$events[$name])) === FALSE)
		{
			// Just add the event if there are no events
			return Event::add($name, $callback);
		}
		else
		{
			// Insert the event immediately before the existing event
			return Event::insert_event($name, $key, $callback);
		}
	}

	/**
	 * Add a callback to an event queue, after a given event.
	 *
	 * @param   string   event name
	 * @param   array    existing event callback
	 * @param   array    event callback
	 * @return  boolean
	 */
	public static function add_after($name, $existing, $callback)
	{
		if (empty(Event::$events[$name]) OR ($key = array_search($existing, Event::$events[$name])) === FALSE)
		{
			// Just add the event if there are no events
			return Event::add($name, $callback);
		}
		else
		{
			// Insert the event immediately after the existing event
			return Event::insert_event($name, $key + 1, $callback);
		}
	}

	/**
	 * Inserts a new event at a specfic key location.
	 *
	 * @param   string   event name
	 * @param   integer  key to insert new event at
	 * @param   array    event callback
	 * @return  void
	 */
	private static function insert_event($name, $key, $callback)
	{
		if (in_array($callback, Event::$events[$name], TRUE))
			return FALSE;

		// Add the new event at the given key location
		Event::$events[$name] = array_merge
		(
			// Events before the key
			array_slice(Event::$events[$name], 0, $key),
			// New event callback
			array($callback),
			// Events after the key
			array_slice(Event::$events[$name], $key)
		);

		return TRUE;
	}

	/**
	 * Replaces an event with another event.
	 *
	 * @param   string   event name
	 * @param   array    event to replace
	 * @param   array    new callback
	 * @return  boolean
	 */
	public static function replace($name, $existing, $callback)
	{
		if (empty(Event::$events[$name]) OR ($key = array_search($existing, Event::$events[$name], TRUE)) === FALSE)
			return FALSE;

		if ( ! in_array($callback, Event::$events[$name], TRUE))
		{
			// Replace the exisiting event with the new event
			Event::$events[$name][$key] = $callback;
		}
		else
		{
			// Remove the existing event from the queue
			unset(Event::$events[$name][$key]);

			// Reset the array so the keys are ordered properly
			Event::$events[$name] = array_values(Event::$events[$name]);
		}

		return TRUE;
	}

	/**
	 * Get all callbacks for an event.
	 *
	 * @param   string  event name
	 * @return  array
	 */
	public static function get($name)
	{
		return empty(Event::$events[$name]) ? array() : Event::$events[$name];
	}

	/**
	 * Clear some or all callbacks from an event.
	 *
	 * @param   string  event name
	 * @param   array   specific callback to remove, FALSE for all callbacks
	 * @return  void
	 */
	public static function clear($name, $callback = FALSE)
	{
		if ($callback === FALSE)
		{
			Event::$events[$name] = array();
		}
		elseif (isset(Event::$events[$name]))
		{
			// Loop through each of the event callbacks and compare it to the
			// callback requested for removal. The callback is removed if it
			// matches.
			foreach (Event::$events[$name] as $i => $event_callback)
			{
				if ($callback === $event_callback)
				{
					unset(Event::$events[$name][$i]);
				}
			}
		}
	}

	/**
	 * Execute all of the callbacks attached to an event.
	 *
	 * @param   string   event name
	 * @param   array    data can be processed as Event::$data by the callbacks
	 * @return  void
	 */
	public static function run($name, & $data = NULL)
	{
		if ( ! empty(Event::$events[$name]))
		{
			// So callbacks can access Event::$data
			Event::$data =& $data;
			$callbacks  =  Event::get($name);

			foreach ($callbacks as $callback)
			{
				call_user_func_array($callback, array(&$data));
			}

			// Do this to prevent data from getting 'stuck'
			$clear_data = '';
			Event::$data =& $clear_data;
		}

		// The event has been run!
		Event::$has_run[$name] = $name;
	}

	/**
	 * Check if a given event has been run.
	 *
	 * @param   string   event name
	 * @return  boolean
	 */
	public static function has_run($name)
	{
		return isset(Event::$has_run[$name]);
	}

} // End Event