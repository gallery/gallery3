<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Set your default language and timezone here. For more information about
 * i18n support see the [i18n] library.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

/**
 * Default language locale name(s).
 * First item must be a valid i18n directory name, subsequent items are alternative locales
 * for OS's that don't support the first (e.g. Windows). The first valid locale in the array will be used.
 * @see http://php.net/setlocale
 */
$config['language'] = array('en_US', 'English_United States');

/**
 * Locale timezone. Defaults to the timezone you have set in your php config
 * 
 * [!!] This cannot be left empty, a valid timezone is required!
 * @see http://php.net/timezones
 */
$config['timezone'] = ini_get('date.timezone');