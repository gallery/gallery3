<?php defined('SYSPATH') OR die('No direct access allowed.');


// Different log levels
$config['log_levels'] = array
(
	'error' => 1,
	'alert' => 2,
	'info'  => 3,
	'debug' => 4,
);

// See different log levels above
$config['log_threshold'] = 1;

$config['date_format'] = 'Y-m-d H:i:s P';

// We can define multiple logging backends at the same time.
$config['drivers'] = array('file');