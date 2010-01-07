<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Log Config
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

/**
 * Different log levels
 */
$config['log_levels'] = array
(
	'error' => 1,
	'alert' => 2,
	'info'  => 3,
	'debug' => 4,
);

/**
 * See different log levels above
 */
$config['log_threshold'] = 1;

/**
 * Log Date format
 */
$config['date_format'] = 'Y-m-d H:i:s P';

/**
 * We can define multiple logging backends at the same time.
 */
$config['drivers'] = array('file');