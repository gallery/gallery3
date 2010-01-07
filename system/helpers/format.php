<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Format helper class.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class format_Core {

	/**
	 * Formats a number according to the current locale.
	 *
	 * @param   float
	 * @param   int|boolean number of fractional digits or TRUE to use the locale default
	 * @return  string
	 */
	public static function number($number, $decimals = 0)
	{
		$locale = localeconv();

		if ($decimals === TRUE)
			return number_format($number, $locale['frac_digits'], $locale['decimal_point'], $locale['thousands_sep']);

		return number_format($number, $decimals, $locale['decimal_point'], $locale['thousands_sep']);
	}

	/**
	 * Formats a phone number according to the specified format.
	 *
	 * @param   string  phone number
	 * @param   string  format string
	 * @return  string
	 */
	public static function phone($number, $format = '3-3-4')
	{
		// Get rid of all non-digit characters in number string
		$number_clean = preg_replace('/\D+/', '', (string) $number);

		// Array of digits we need for a valid format
		$format_parts = preg_split('/[^1-9][^0-9]*/', $format, -1, PREG_SPLIT_NO_EMPTY);

		// Number must match digit count of a valid format
		if (strlen($number_clean) !== array_sum($format_parts))
			return $number;

		// Build regex
		$regex = '(\d{'.implode('})(\d{', $format_parts).'})';

		// Build replace string
		for ($i = 1, $c = count($format_parts); $i <= $c; $i++)
		{
			$format = preg_replace('/(?<!\$)[1-9][0-9]*/', '\$'.$i, $format, 1);
		}

		// Hocus pocus!
		return preg_replace('/^'.$regex.'$/', $format, $number_clean);
	}

	/**
	 * Formats a URL to contain a protocol at the beginning.
	 *
	 * @param   string  possibly incomplete URL
	 * @return  string
	 */
	public static function url($str = '')
	{
		// Clear protocol-only strings like "http://"
		if ($str === '' OR substr($str, -3) === '://')
			return '';

		// If no protocol given, prepend "http://" by default
		if (strpos($str, '://') === FALSE)
			return 'http://'.$str;

		// Return the original URL
		return $str;
	}

	/**
	 * Normalizes a hexadecimal HTML color value. All values will be converted
	 * to lowercase, have a "#" prepended and contain six characters.
	 *
	 * @param   string  hexadecimal HTML color value
	 * @return  string
	 */
	public static function color($str = '')
	{
		// Reject invalid values
		if ( ! valid::color($str))
			return '';

		// Convert to lowercase
		$str = strtolower($str);

		// Prepend "#"
		if ($str[0] !== '#')
		{
			$str = '#'.$str;
		}

		// Expand short notation
		if (strlen($str) === 4)
		{
			$str = '#'.$str[1].$str[1].$str[2].$str[2].$str[3].$str[3];
		}

		return $str;
	}

} // End format