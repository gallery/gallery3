<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Session database driver.
 *
 * $Id: Database.php 4729 2009-12-29 20:35:19Z isaiah $
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Session_Database_Driver implements Session_Driver {

	/*
	CREATE TABLE sessions
	(
		session_id VARCHAR(127) NOT NULL,
		last_activity INT(10) UNSIGNED NOT NULL,
		data TEXT NOT NULL,
		PRIMARY KEY (session_id)
	);
	*/

	// Database settings
	protected $db = 'default';
	protected $table = 'sessions';

	// Encryption
	protected $encrypt;

	// Session settings
	protected $session_id;
	protected $written = FALSE;

	public function __construct()
	{
		// Load configuration
		$config = Kohana::config('session');

		if ( ! empty($config['encryption']))
		{
			// Load encryption
			$this->encrypt = Encrypt::instance();
		}

		if (is_array($config['storage']))
		{
			if ( ! empty($config['storage']['group']))
			{
				// Set the group name
				$this->db = $config['storage']['group'];
			}

			if ( ! empty($config['storage']['table']))
			{
				// Set the table name
				$this->table = $config['storage']['table'];
			}
		}

		Kohana_Log::add('debug', 'Session Database Driver Initialized');
	}

	public function open($path, $name)
	{
		return TRUE;
	}

	public function close()
	{
		return TRUE;
	}

	public function read($id)
	{
		// Load the session
		$query = db::select('data')
			->from($this->table)
			->where('session_id', '=', $id)
			->limit(1)
			->execute($this->db);

		if ($query->count() === 0)
		{
			// No current session
			$this->session_id = NULL;

			return '';
		}

		// Set the current session id
		$this->session_id = $id;

		// Load the data
		$data = $query->current()->data;

		return ($this->encrypt === NULL) ? base64_decode($data) : $this->encrypt->decode($data);
	}

	public function write($id, $data)
	{
		if ( ! Session::$should_save)
			return TRUE;

		$data = array
		(
			'session_id' => $id,
			'last_activity' => time(),
			'data' => ($this->encrypt === NULL) ? base64_encode($data) : $this->encrypt->encode($data)
		);

		if ($this->session_id === NULL)
		{
			// Insert a new session
			$query = db::insert($this->table, $data)
				->execute($this->db);
		}
		elseif ($id === $this->session_id)
		{
			// Do not update the session_id
			unset($data['session_id']);

			// Update the existing session
			$query = db::update($this->table)
				->set($data)
				->where('session_id', '=', $id)
				->execute($this->db);
		}
		else
		{
			// Update the session and id
			$query = db::update($this->table)
				->set($data)
				->where('session_id', '=', $this->session_id)
				->execute($this->db);

			// Set the new session id
			$this->session_id = $id;
		}

		return (bool) $query->count();
	}

	public function destroy($id)
	{
		// Delete the requested session
		db::delete($this->table)
			->where('session_id', '=', $id)
			->execute($this->db);

		// Session id is no longer valid
		$this->session_id = NULL;

		return TRUE;
	}

	public function regenerate()
	{
		// Generate a new session id
		session_regenerate_id();

		// Return new session id
		return session_id();
	}

	public function gc($maxlifetime)
	{
		// Delete all expired sessions
		$query = db::delete($this->table)
			->where('last_activity', '<', time() - $maxlifetime)
			->execute($this->db);

		Kohana_Log::add('debug', 'Session garbage collected: '.$query->count().' row(s) deleted.');

		return TRUE;
	}

} // End Session Database Driver
