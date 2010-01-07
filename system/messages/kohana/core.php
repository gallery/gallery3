<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Core Kohana messages
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
$messages = array
(
	'errors' => array
	(
		E_KOHANA             => 'Framework Error',
		E_PAGE_NOT_FOUND     => 'Page Not Found',
		E_DATABASE_ERROR     => 'Database Error',
		E_RECOVERABLE_ERROR  => 'Recoverable Error',
		E_ERROR              => 'Fatal Error',
		E_COMPILE_ERROR      => 'Fatal Error',
		E_CORE_ERROR         => 'Fatal Error',
		E_USER_ERROR         => 'Fatal Error',
		E_PARSE              => 'Syntax Error',
		E_WARNING            => 'Warning Message',
		E_COMPILE_WARNING    => 'Warning Message',
		E_CORE_WARNING       => 'Warning Message',
		E_USER_WARNING       => 'Warning Message',
		E_STRICT             => 'Strict Mode Error',
		E_NOTICE             => 'Runtime Message',
		E_USER_NOTICE        => 'Runtime Message',
	),
);

// E_DEPRECATED is only defined in PHP >= 5.3.0
if (defined('E_DEPRECATED'))
{
	$messages['errors'][E_DEPRECATED] = 'Deprecated';
}