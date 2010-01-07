<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * HTTP Config
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

/**
 * HTTP-EQUIV type meta tags
 *
 */
$config['meta_equiv'] = array
(
	'cache-control',
	'content-type', 'content-script-type', 'content-style-type',
	'content-disposition',
	'content-language',
	'default-style',
	'expires',
	'ext-cache',
	'pics-label',
	'pragma',
	'refresh',
	'set-cookie',
	'vary',
	'window-target',
);