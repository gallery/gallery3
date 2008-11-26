<?php defined('SYSPATH') or die('No direct access allowed.');
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
 * Locale timezone. Defaults to use the server timezone.
 * @see http://php.net/timezones
 */
$config['timezone'] = '';

// i18n settings

/**
 * The locale of the built-in localization messages (locale of strings in translate() calls).
 * This can't be changed easily, unless all localization strings are replaced in all source files
 * as well.
 */
$config['root_locale'] = 'en';

/**
 * The default locale of this installation.
 */
$config['default_locale'] = 'en_US';

/**
 * The path to the folder with translation files.
 */
$config['locale_dir'] = VARPATH . 'locale/';