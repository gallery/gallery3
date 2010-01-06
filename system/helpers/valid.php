<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * The Valid Helper provides functions to help validate data. They can be used as standalone static functions or
 * as rules for the Validation Library.
 *
 * ###### Validation Library Example:
 *     $data = new Validation($_POST);
 *     $data->add_rules('phone', 'required', 'valid::phone[7, 10, 11, 14]')
 *
 *     if ($data->validate())
 *     {
 *         echo 'The phone number is valid';
 *     }
 *     else
 *     {
 *         echo Kohana::debug($data->errors());
 *     }
 *
 * [!!] The *valid::* part of the rule is optional, but is recommended to avoid conflicts with php functions.
 *
 * For more informaiton see the [Validation] Library.
 *
 * ###### Standalone Example:
 *     if (valid::phone($_POST['phone'], array(7, 10, 11, 14))
 *     {
 *         echo 'The phone number is valid';
 *     }
 *     else
 *     {
 *         echo 'Not valid';
 *     }
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class valid_Core {

	/**
	 * Validate an email address. This method is more strict than valid::email_rfc();
	 *
	 * ###### Example:
	 *     $email = 'bill@gates.com';
	 *     if (valid::email($email))
	 *     {
	 *         echo "Valid email";
	 *     }
	 *     else
	 *     {
	 *         echo "Invalid email";
	 *     }
	 *
	 * @param   string   A email address
	 * @return  boolean
	 */
	public static function email($email)
	{
		return (bool) preg_match('/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD', (string) $email);
	}

	/**
	 * Validate the domain of an email address by checking if the domain has a
	 * valid MX record.
	 *
	 * [!!] This function will always return `TRUE` if the checkdnsrr() function isn't avaliable (All Windows platforms before php 5.3)
	 *
	 * @param   string   email address
	 * @return  boolean
	 */
	public static function email_domain($email)
	{
		// If we can't prove the domain is invalid, consider it valid
		// Note: checkdnsrr() is not implemented on Windows platforms
		if ( ! function_exists('checkdnsrr'))
			return TRUE;

		// Check if the email domain has a valid MX record
		return (bool) checkdnsrr(preg_replace('/^[^@]+@/', '', $email), 'MX');
	}

	/**
	 * RFC compliant email validation. This function is __LESS__ strict than [valid::email]. Choose carefully.
	 *
	 * @see  Originally by Cal Henderson, modified to fit Kohana syntax standards:
	 * @see  http://www.iamcal.com/publish/articles/php/parsing_email/
	 * @see  http://www.w3.org/Protocols/rfc822/
	 *
	 * @param   string   email address
	 * @return  boolean
	 */
	public static function email_rfc($email)
	{
		$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
		$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
		$atom  = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
		$pair  = '\\x5c[\\x00-\\x7f]';

		$domain_literal = "\\x5b($dtext|$pair)*\\x5d";
		$quoted_string  = "\\x22($qtext|$pair)*\\x22";
		$sub_domain     = "($atom|$domain_literal)";
		$word           = "($atom|$quoted_string)";
		$domain         = "$sub_domain(\\x2e$sub_domain)*";
		$local_part     = "$word(\\x2e$word)*";
		$addr_spec      = "$local_part\\x40$domain";

		return (bool) preg_match('/^'.$addr_spec.'$/D', (string) $email);
	}

	/**
	 * Basic URL validation.
	 *
	 * ###### Example:
	 *     $url = 'http://www.kohanaphp.com';
	 *     if (valid::url($url))
	 *     {
	 *         echo "Valid url";
	 *     }
	 *     else
	 *     {
	 *         echo "Invalid url";
	 *     }
	 *
	 * @param   string   URL
	 * @return  boolean
	 */
	public static function url($url)
	{
		return (bool) filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED);
	}

	/**
	 * Validates an IP Address. This only tests to see if the ip address is valid,
	 * it doesn't check to see if the ip address is actually in use. Has optional support for
	 * IPv6, and private ip address ranges.
	 *
	 * @param   string   IP address
	 * @param   boolean  allow IPv6 addresses
	 * @param   boolean  allow private IP networks
	 * @return  boolean
	 */
	public static function ip($ip, $ipv6 = FALSE, $allow_private = TRUE)
	{
		// By default do not allow private and reserved range IPs
		$flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
		if ($allow_private === TRUE)
			$flags =  FILTER_FLAG_NO_RES_RANGE;

		if ($ipv6 === TRUE)
			return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flags);

		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flags | FILTER_FLAG_IPV4);
	}

	/**
	 * Validates a credit card number using the [Luhn (mod10)](http://en.wikipedia.org/wiki/Luhn_algorithm)
	 * formula.
	 *
	 * ###### Example:
	 *     $cc_number = '4111111111111111';
	 *     if (valid::credit_card($cc_number, array('visa', 'mastercard')))
	 *     {
	 *         echo "Valid number";
	 *     }
	 *     else
	 *     {
	 *         echo "Invalid number";
	 *     }
	 *
	 * @param   integer       credit card number
	 * @param   string|array  card type, or an array of card types
	 * @return  boolean
	 */
	public static function credit_card($number, $type = NULL)
	{
		// Remove all non-digit characters from the number
		if (($number = preg_replace('/\D+/', '', $number)) === '')
			return FALSE;

		if ($type == NULL)
		{
			// Use the default type
			$type = 'default';
		}
		elseif (is_array($type))
		{
			foreach ($type as $t)
			{
				// Test each type for validity
				if (valid::credit_card($number, $t))
					return TRUE;
			}

			return FALSE;
		}

		$cards = Kohana::config('credit_cards');

		// Check card type
		$type = strtolower($type);

		if ( ! isset($cards[$type]))
			return FALSE;

		// Check card number length
		$length = strlen($number);

		// Validate the card length by the card type
		if ( ! in_array($length, preg_split('/\D+/', $cards[$type]['length'])))
			return FALSE;

		// Check card number prefix
		if ( ! preg_match('/^'.$cards[$type]['prefix'].'/', $number))
			return FALSE;

		// No Luhn check required
		if ($cards[$type]['luhn'] == FALSE)
			return TRUE;

		// Checksum of the card number
		$checksum = 0;

		for ($i = $length - 1; $i >= 0; $i -= 2)
		{
			// Add up every 2nd digit, starting from the right
			$checksum += substr($number, $i, 1);
		}

		for ($i = $length - 2; $i >= 0; $i -= 2)
		{
			// Add up every 2nd digit doubled, starting from the right
			$double = substr($number, $i, 1) * 2;

			// Subtract 9 from the double where value is greater than 10
			$checksum += ($double >= 10) ? $double - 9 : $double;
		}

		// If the checksum is a multiple of 10, the number is valid
		return ($checksum % 10 === 0);
	}

	/**
	 * Checks if a phone number is valid. This function will strip all non-digit
	 * characters from the phone number for testing.
	 *
	 * ###### Example:
	 *     $phone_number = '(201) 664-0274';
	 *     if (valid::phone($phone_number))
	 *     {
	 *         echo "Valid phone number";
	 *     }
	 *     else
	 *     {
	 *         echo "Invalid phone number";
	 *     }
	 *
	 * @param   string   phone number to check
	 * @return  boolean
	 */
	public static function phone($number, $lengths = NULL)
	{
		if ( ! is_array($lengths))
		{
			$lengths = array(7,10,11);
		}

		// Remove all non-digit characters from the number
		$number = preg_replace('/\D+/', '', $number);

		// Check if the number is within range
		return in_array(strlen($number), $lengths);
	}

	/**
	 * Tests if a string is a valid date using the php
	 * [strtotime()](http://php.net/strtotime) function
	 *
	 * @param   string   date to check
	 * @return  boolean
	 */
	public static function date($str)
	{
		return (strtotime($str) !== FALSE);
	}

	/**
	 * Checks whether a string consists of alphabetical characters only.
	 *
	 * @param   string   input string
	 * @param   boolean  trigger UTF-8 compatibility
	 * @return  boolean
	 */
	public static function alpha($str, $utf8 = FALSE)
	{
		return ($utf8 === TRUE)
			? (bool) preg_match('/^\pL++$/uD', (string) $str)
			: ctype_alpha((string) $str);
	}

	/**
	 * Checks whether a string consists of alphabetical characters and numbers only.
	 *
	 * @param   string   input string
	 * @param   boolean  trigger UTF-8 compatibility
	 * @return  boolean
	 */
	public static function alpha_numeric($str, $utf8 = FALSE)
	{
		return ($utf8 === TRUE)
			? (bool) preg_match('/^[\pL\pN]++$/uD', (string) $str)
			: ctype_alnum((string) $str);
	}

	/**
	 * Checks whether a string consists of alphabetical characters, numbers, underscores and dashes only.
	 *
	 * @param   string   input string
	 * @param   boolean  trigger UTF-8 compatibility
	 * @return  boolean
	 */
	public static function alpha_dash($str, $utf8 = FALSE)
	{
		return ($utf8 === TRUE)
			? (bool) preg_match('/^[-\pL\pN_]++$/uD', (string) $str)
			: (bool) preg_match('/^[-a-z0-9_]++$/iD', (string) $str);
	}

	/**
	 * Checks whether a string consists of digits only (no dots or dashes).
	 *
	 * @param   string   input string
	 * @param   boolean  trigger UTF-8 compatibility
	 * @return  boolean
	 */
	public static function digit($str, $utf8 = FALSE)
	{
		return ($utf8 === TRUE)
			? (bool) preg_match('/^\pN++$/uD', (string) $str)
			: ctype_digit((string) $str);
	}

	/**
	 * Checks whether a string is a valid number (negative and decimal numbers allowed).
	 * This function uses [localeconv()](http://www.php.net/manual/en/function.localeconv.php)
	 * to support international number formats.
	 *
	 * @param   string   input string
	 * @return  boolean
	 */
	public static function numeric($str)
	{
		// Use localeconv to set the decimal_point value: Usually a comma or period.
		$locale = localeconv();
		return (bool) preg_match('/^-?[0-9'.$locale['decimal_point'].']++$/D', (string) $str);
	}

	/**
	 * Tests if an integer is within a range.
	 *
	 * @param   integer  number to check
	 * @param   array    valid range of input
	 * @return  boolean
	 */
	public static function range($number, array $range)
	{
		// Invalid by default
		$status = FALSE;

		if (is_int($number) OR ctype_digit($number))
		{
			if (count($range) > 1)
			{
				if ($number >= $range[0] AND $number <= $range[1])
				{
					// Number is within the required range
					$status = TRUE;
				}
			}
			elseif ($number >= $range[0])
			{
				// Number is greater than the minimum
				$status = TRUE;
			}
		}

		return $status;
	}

	/**
	 * Checks if a string is a proper decimal format. The format array can be
	 * used to specify a decimal length, or a number and decimal length, eg:
	 * array(2) would force the number to have 2 decimal places, array(4,2)
	 * would force the number to have 4 digits and 2 decimal places.
	 *
	 * ###### Example:
	 *     $decimal = '4.5';
	 *     if (valid::decimal($decimal, array(2,1)))
	 *     {
	 *         echo "Valid decimal";
	 *     }
	 *     else
	 *     {
	 *         echo "Invalid decimal";
	 *     }
	 *     
	 *     Output: Invalid decimal
	 *
	 * @param   string   input string
	 * @param   array    decimal format: y or x,y
	 * @return  boolean
	 */
	public static function decimal($str, $format = NULL)
	{
		// Create the pattern
		$pattern = '/^[0-9]%s\.[0-9]%s$/';

		if ( ! empty($format))
		{
			if (count($format) > 1)
			{
				// Use the format for number and decimal length
				$pattern = sprintf($pattern, '{'.$format[0].'}', '{'.$format[1].'}');
			}
			elseif (count($format) > 0)
			{
				// Use the format as decimal length
				$pattern = sprintf($pattern, '+', '{'.$format[0].'}');
			}
		}
		else
		{
			// No format
			$pattern = sprintf($pattern, '+', '+');
		}

		return (bool) preg_match($pattern, (string) $str);
	}

	/**
	 * Checks if a string is a proper hexadecimal HTML color value. The validation
	 * is quite flexible as it does not require an initial "#" and also allows for
	 * the short notation using only three instead of six hexadecimal characters.
	 * You may want to normalize these values with format::color().
	 *
	 * @param   string   input string
	 * @return  boolean
	 */
	public static function color($str)
	{
		return (bool) preg_match('/^#?+[0-9a-f]{3}(?:[0-9a-f]{3})?$/iD', $str);
	}

} // End valid