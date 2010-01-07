<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Number helper class.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class num_Core {

	/**
	 * Round a number to the nearest nth
	 *
	 * @param   integer  number to round
	 * @param   integer  number to round to
	 * @return  integer
	 */
	public static function round($number, $nearest = 5)
	{
		return round($number / $nearest) * $nearest;
	}

} // End num