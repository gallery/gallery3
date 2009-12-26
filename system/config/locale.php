<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Core
 *
 * Default language locale name(s).
 * First item must be a valid i18n directory name, subsequent items are alternative locales
 * for OS's that don't support the first (e.g. Windows). The first valid locale in the array will be used.
 * @see http://php.net/setlocale
 */
$config['language'] = array('en_US', 'English_United States');

/**
 * Locale timezone. Defaults to the timezone you have set in your php config
 * This cannot be left empty, a valid timezone is required!
 * @see http://php.net/timezones
 */
$config['timezone'] = ini_get('date.timezone');