<?php

$messages = array
(
	'errors' => array
	(
		E_KOHANA             => __('Framework Error'),   // __('Please check the Kohana documentation for information about the following error.'),
		E_PAGE_NOT_FOUND     => __('Page Not Found'),    // __('The requested page was not found. It may have moved, been deleted, or archived.'),
		E_DATABASE_ERROR     => __('Database Error'),    // __('A database error occurred while performing the requested procedure. Please review the database error below for more information.'),
		E_RECOVERABLE_ERROR  => __('Recoverable Error'), // __('An error was detected which prevented the loading of this page. If this problem persists, please contact the website administrator.'),

		E_ERROR              => __('Fatal Error'),
		E_COMPILE_ERROR      => __('Fatal Error'),
		E_CORE_ERROR         => __('Fatal Error'),
		E_USER_ERROR         => __('Fatal Error'),
		E_PARSE              => __('Syntax Error'),
		E_WARNING            => __('Warning Message'),
		E_COMPILE_WARNING    => __('Warning Message'),
		E_CORE_WARNING       => __('Warning Message'),
		E_USER_WARNING       => __('Warning Message'),
		E_STRICT             => __('Strict Mode Error'),
		E_NOTICE             => __('Runtime Message'),
		E_USER_NOTICE        => __('Runtime Message'),
	),
	'config'             => 'config file',
	'controller'         => 'controller',
	'helper'             => 'helper',
	'library'            => 'library',
	'driver'             => 'driver',
	'model'              => 'model',
	'view'               => 'view',
);