<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Session driver interface
 *
 * $Id: Session.php 4729 2009-12-29 20:35:19Z isaiah $
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
interface Session_Driver {

	/**
	 * Opens a session.
	 *
	 * @param   string   save path
	 * @param   string   session name
	 * @return  boolean
	 */
	public function open($path, $name);

	/**
	 * Closes a session.
	 *
	 * @return  boolean
	 */
	public function close();

	/**
	 * Reads a session.
	 *
	 * @param   string  session id
	 * @return  string
	 */
	public function read($id);

	/**
	 * Writes a session.
	 *
	 * @param   string   session id
	 * @param   string   session data
	 * @return  boolean
	 */
	public function write($id, $data);

	/**
	 * Destroys a session.
	 *
	 * @param   string   session id
	 * @return  boolean
	 */
	public function destroy($id);

	/**
	 * Regenerates the session id.
	 *
	 * @return  string
	 */
	public function regenerate();

	/**
	 * Garbage collection.
	 *
	 * @param   integer  session expiration period
	 * @return  boolean
	 */
	public function gc($maxlifetime);

} // End Session Driver Interface