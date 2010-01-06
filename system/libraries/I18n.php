<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Kohana I18N System
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

class I18n_Core
{
	protected static $locale;
	// All the translations will be cached in here, after the first call of get_text()
	protected static $translations = array();

	public static function set_locale($locale)
	{
		// Reset the translations array
		I18n::$translations = array();

		I18n::$locale = $locale;
	}


	/**
	 *
	 * Returns the locale.
	 * If $ext is true, the UTF8 extension gets returned as well, otherwise, just the language code.
	 * Defaults to true.
	 *
	 * @return 							The locale
	 * @param boolean $ext[optional]	Get the Extension?
	 */
	public static function get_locale($ext = true)
	{
		if($ext)
			return I18n::$locale;
		else
			return arr::get(explode('.', I18n::$locale), 0);
	}


	/**
	 *
	 * Translates $string into language I18n::$locale and caches all found translations on the first call
	 *
	 * @return                 The translated String
	 * @param string $string   The String to translate
	 */
	public static function get_text($string)
	{
		if ( ! I18n::$translations)
		{
			$locale = explode('_', I18n::get_locale(FALSE));

			// Get the translation files
			$translation_files = Kohana::find_file('i18n', $locale[0]);

			if($local_translation_files = Kohana::find_file('i18n', $locale[0].'/'.$locale[1]))
				$translation_files = array_merge($translation_files, $local_translation_files);

			if ($translation_files)
			{
				// Merge the translations
				foreach ($translation_files as $file)
				{
					include $file;
					I18n::$translations = array_merge(I18n::$translations, $translations);
				}
			}
		}

		if (isset(I18n::$translations[$string]))
			return I18n::$translations[$string];
		else
			return $string;
	}
}

/**
 * Loads the configured driver and validates it.
 *
 * @param   string  Text to output
 * @param   array   Key/Value pairs of arguments to replace in the string
 * @return  string  Translated text
 */
function __($string, $args = NULL)
{
	// KOHANA_LOCALE is the default locale, in which all of Kohana's __() calls are written in
	if (I18n::get_locale() != Kohana::LOCALE)
	{
		$string = I18n::get_text($string);
	}

	if ($args === NULL)
		return $string;

	return strtr($string, $args);
}
