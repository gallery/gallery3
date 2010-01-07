<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * A port of phputf8 to a unified file/class.
 *
 * This file is licensed differently from the rest of Kohana. As a port of
 * phputf8, which is LGPL software, this file is released under the LGPL.
 *
 * PCRE needs to be compiled with UTF-8 support (--enable-utf8).
 * Support for Unicode properties is highly recommended (--enable-unicode-properties).
 * @see http://php.net/manual/reference.pcre.pattern.modifiers.php
 *
 * string functions.
 * @see http://php.net/mbstring
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */

class utf8_Core {

	/**
	 * Replaces text within a portion of a UTF-8 string.
	 * @see http://php.net/substr_replace
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   input string
	 * @param   string   replacement string
	 * @param   integer  offset
	 * @return  string
	 */
	public static function substr_replace($str, $replacement, $offset, $length = NULL)
	{
		if (text::is_ascii($str))
			return ($length === NULL) ? substr_replace($str, $replacement, $offset) : substr_replace($str, $replacement, $offset, $length);

		$length = ($length === NULL) ? mb_strlen($str) : (int) $length;
		preg_match_all('/./us', $str, $str_array);
		preg_match_all('/./us', $replacement, $replacement_array);

		array_splice($str_array[0], $offset, $length, $replacement_array[0]);
		return implode('', $str_array[0]);
	}

	/**
	 * Makes a UTF-8 string's first character uppercase.
	 * @see http://php.net/ucfirst
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   mixed case string
	 * @return  string
	 */
	public static function ucfirst($str)
	{
		if (text::is_ascii($str))
			return ucfirst($str);

		preg_match('/^(.?)(.*)$/us', $str, $matches);
		return mb_strtoupper($matches[1]).$matches[2];
	}

	/**
	 * Case-insensitive UTF-8 string comparison.
	 * @see http://php.net/strcasecmp
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   string to compare
	 * @param   string   string to compare
	 * @return  integer  less than 0 if str1 is less than str2
	 * @return  integer  greater than 0 if str1 is greater than str2
	 * @return  integer  0 if they are equal
	 */
	public static function strcasecmp($str1, $str2)
	{
		if (text::is_ascii($str1) AND text::is_ascii($str2))
			return strcasecmp($str1, $str2);

		$str1 = mb_strtolower($str1);
		$str2 = mb_strtolower($str2);
		return strcmp($str1, $str2);
	}

	/**
	 * Returns a string or an array with all occurrences of search in subject (ignoring case).
	 * replaced with the given replace value.
	 * @see     http://php.net/str_ireplace
	 *
	 * @note    It's not fast and gets slower if $search and/or $replace are arrays.
	 * @author  Harry Fuecks <hfuecks@gmail.com
	 *
	 * @param   string|array  text to replace
	 * @param   string|array  replacement text
	 * @param   string|array  subject text
	 * @param   integer       number of matched and replaced needles will be returned via this parameter which is passed by reference
	 * @return  string        if the input was a string
	 * @return  array         if the input was an array
	 */
	public static function str_ireplace($search, $replace, $str, & $count = NULL)
	{
		if (text::is_ascii($search) AND text::is_ascii($replace) AND text::is_ascii($str))
			return str_ireplace($search, $replace, $str, $count);

		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = utf8::str_ireplace($search, $replace, $val, $count);
			}
			return $str;
		}

		if (is_array($search))
		{
			$keys = array_keys($search);

			foreach ($keys as $k)
			{
				if (is_array($replace))
				{
					if (array_key_exists($k, $replace))
					{
						$str = utf8::str_ireplace($search[$k], $replace[$k], $str, $count);
					}
					else
					{
						$str = utf8::str_ireplace($search[$k], '', $str, $count);
					}
				}
				else
				{
					$str = utf8::str_ireplace($search[$k], $replace, $str, $count);
				}
			}
			return $str;
		}

		$search = mb_strtolower($search);
		$str_lower = mb_strtolower($str);

		$total_matched_strlen = 0;
		$i = 0;

		while (preg_match('/(.*?)'.preg_quote($search, '/').'/s', $str_lower, $matches))
		{
			$matched_strlen = strlen($matches[0]);
			$str_lower = substr($str_lower, $matched_strlen);

			$offset = $total_matched_strlen + strlen($matches[1]) + ($i * (strlen($replace) - 1));
			$str = substr_replace($str, $replace, $offset, strlen($search));

			$total_matched_strlen += $matched_strlen;
			$i++;
		}

		$count += $i;
		return $str;
	}

	/**
	 * Case-insenstive UTF-8 version of strstr. Returns all of input string
	 * from the first occurrence of needle to the end.
	 * @see http://php.net/stristr
	 *
	 * @author Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   input string
	 * @param   string   needle
	 * @return  string   matched substring if found
	 * @return  boolean  FALSE if the substring was not found
	 */
	public static function stristr($str, $search)
	{
		if (text::is_ascii($str) AND text::is_ascii($search))
			return stristr($str, $search);

		if ($search == '')
			return $str;

		$str_lower = mb_strtolower($str);
		$search_lower = mb_strtolower($search);

		preg_match('/^(.*?)'.preg_quote($search, '/').'/s', $str_lower, $matches);

		if (isset($matches[1]))
			return substr($str, strlen($matches[1]));

		return FALSE;
	}

	/**
	 * Finds the length of the initial segment matching mask.
	 * @see http://php.net/strspn
	 *
	 * @author Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   input string
	 * @param   string   mask for search
	 * @param   integer  start position of the string to examine
	 * @param   integer  length of the string to examine
	 * @return  integer  length of the initial segment that contains characters in the mask
	 */
	public static function strspn($str, $mask, $offset = NULL, $length = NULL)
	{
		if ($str == '' OR $mask == '')
			return 0;

		if (text::is_ascii($str) AND text::is_ascii($mask))
			return ($offset === NULL) ? strspn($str, $mask) : (($length === NULL) ? strspn($str, $mask, $offset) : strspn($str, $mask, $offset, $length));

		if ($offset !== NULL OR $length !== NULL)
		{
			$str = mb_substr($str, $offset, $length);
		}

		// Escape these characters:  - [ ] . : \ ^ /
		// The . and : are escaped to prevent possible warnings about POSIX regex elements
		$mask = preg_replace('#[-[\].:\\\\^/]#', '\\\\$0', $mask);
		preg_match('/^[^'.$mask.']+/u', $str, $matches);

		return isset($matches[0]) ? mb_strlen($matches[0]) : 0;
	}

	/**
	 * Finds the length of the initial segment not matching mask.
	 * @see http://php.net/strcspn
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   input string
	 * @param   string   mask for search
	 * @param   integer  start position of the string to examine
	 * @param   integer  length of the string to examine
	 * @return  integer  length of the initial segment that contains characters not in the mask
	 */
	public static function strcspn($str, $mask, $offset = NULL, $length = NULL)
	{
		if ($str == '' OR $mask == '')
			return 0;

		if (text::is_ascii($str) AND text::is_ascii($mask))
			return ($offset === NULL) ? strcspn($str, $mask) : (($length === NULL) ? strcspn($str, $mask, $offset) : strcspn($str, $mask, $offset, $length));

		if ($str !== NULL OR $length !== NULL)
		{
			$str = mb_substr($str, $offset, $length);
		}

		// Escape these characters:  - [ ] . : \ ^ /
		// The . and : are escaped to prevent possible warnings about POSIX regex elements
		$mask = preg_replace('#[-[\].:\\\\^/]#', '\\\\$0', $mask);
		preg_match('/^[^'.$mask.']+/u', $str, $matches);

		return isset($matches[0]) ? mb_strlen($matches[0]) : 0;
	}

	/**
	 * Pads a UTF-8 string to a certain length with another string.
	 * @see http://php.net/str_pad
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   input string
	 * @param   integer  desired string length after padding
	 * @param   string   string to use as padding
	 * @param   string   padding type: STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH
	 * @return  string
	 */
	public static function str_pad($str, $final_str_length, $pad_str = ' ', $pad_type = STR_PAD_RIGHT)
	{
		if (text::is_ascii($str) AND text::is_ascii($pad_str))
		{
			return str_pad($str, $final_str_length, $pad_str, $pad_type);
		}

		$str_length = mb_strlen($str);

		if ($final_str_length <= 0 OR $final_str_length <= $str_length)
		{
			return $str;
		}

		$pad_str_length = mb_strlen($pad_str);
		$pad_length = $final_str_length - $str_length;

		if ($pad_type == STR_PAD_RIGHT)
		{
			$repeat = ceil($pad_length / $pad_str_length);
			return mb_substr($str.str_repeat($pad_str, $repeat), 0, $final_str_length);
		}

		if ($pad_type == STR_PAD_LEFT)
		{
			$repeat = ceil($pad_length / $pad_str_length);
			return mb_substr(str_repeat($pad_str, $repeat), 0, floor($pad_length)).$str;
		}

		if ($pad_type == STR_PAD_BOTH)
		{
			$pad_length /= 2;
			$pad_length_left = floor($pad_length);
			$pad_length_right = ceil($pad_length);
			$repeat_left = ceil($pad_length_left / $pad_str_length);
			$repeat_right = ceil($pad_length_right / $pad_str_length);

			$pad_left = mb_substr(str_repeat($pad_str, $repeat_left), 0, $pad_length_left);
			$pad_right = mb_substr(str_repeat($pad_str, $repeat_right), 0, $pad_length_left);
			return $pad_left.$str.$pad_right;
		}

		trigger_error('utf8::str_pad: Unknown padding type (' . $pad_type . ')', E_USER_ERROR);
	}

	/**
	 * Converts a UTF-8 string to an array.
	 * @see http://php.net/str_split
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   input string
	 * @param   integer  maximum length of each chunk
	 * @return  array
	 */
	public static function str_split($str, $split_length = 1)
	{
		$split_length = (int) $split_length;

		if (text::is_ascii($str))
		{
			return str_split($str, $split_length);
		}

		if ($split_length < 1)
		{
			return FALSE;
		}

		if (mb_strlen($str) <= $split_length)
		{
			return array($str);
		}

		preg_match_all('/.{'.$split_length.'}|[^\x00]{1,'.$split_length.'}$/us', $str, $matches);

		return $matches[0];
	}

	/**
	 * Reverses a UTF-8 string.
	 * @see http://php.net/strrev
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   string to be reversed
	 * @return  string
	 */
	public static function strrev($str)
	{
		if (text::is_ascii($str))
			return strrev($str);

		preg_match_all('/./us', $str, $matches);
		return implode('', array_reverse($matches[0]));
	}

	/**
	 * Strips whitespace (or other UTF-8 characters) from the beginning and
	 * end of a string.
	 * @see http://php.net/trim
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 *
	 * @param   string   input string
	 * @param   string   string of characters to remove
	 * @return  string
	 */
	public static function trim($str, $charlist = NULL)
	{
		if ($charlist === NULL)
			return trim($str);

		return utf8::ltrim(utf8::rtrim($str, $charlist), $charlist);
	}

	/**
	 * Strips whitespace (or other UTF-8 characters) from the beginning of a string.
	 * @see http://php.net/ltrim
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 *
	 * @param   string   input string
	 * @param   string   string of characters to remove
	 * @return  string
	 */
	public static function ltrim($str, $charlist = NULL)
	{
		if ($charlist === NULL)
			return ltrim($str);

		if (text::is_ascii($charlist))
			return ltrim($str, $charlist);

		$charlist = preg_replace('#[-\[\]:\\\\^/]#', '\\\\$0', $charlist);

		return preg_replace('/^['.$charlist.']+/u', '', $str);
	}

	/**
	 * Strips whitespace (or other UTF-8 characters) from the end of a string.
	 * @see http://php.net/rtrim
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 *
	 * @param   string   input string
	 * @param   string   string of characters to remove
	 * @return  string
	 */
	public static function rtrim($str, $charlist = NULL)
	{
		if ($charlist === NULL)
			return rtrim($str);

		if (text::is_ascii($charlist))
			return rtrim($str, $charlist);

		$charlist = preg_replace('#[-\[\]:\\\\^/]#', '\\\\$0', $charlist);

		return preg_replace('/['.$charlist.']++$/uD', '', $str);
	}

	/**
	 * Returns the unicode ordinal for a character.
	 * @see http://php.net/ord
	 *
	 * @author Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string   UTF-8 encoded character
	 * @return  integer
	 */
	public static function ord($chr)
	{
		$ord0 = ord($chr);

		if ($ord0 >= 0 AND $ord0 <= 127)
		{
			return $ord0;
		}

		if ( ! isset($chr[1]))
		{
			trigger_error('Short sequence - at least 2 bytes expected, only 1 seen', E_USER_WARNING);
			return FALSE;
		}

		$ord1 = ord($chr[1]);

		if ($ord0 >= 192 AND $ord0 <= 223)
		{
			return ($ord0 - 192) * 64 + ($ord1 - 128);
		}

		if ( ! isset($chr[2]))
		{
			trigger_error('Short sequence - at least 3 bytes expected, only 2 seen', E_USER_WARNING);
			return FALSE;
		}

		$ord2 = ord($chr[2]);

		if ($ord0 >= 224 AND $ord0 <= 239)
		{
			return ($ord0 - 224) * 4096 + ($ord1 - 128) * 64 + ($ord2 - 128);
		}

		if ( ! isset($chr[3]))
		{
			trigger_error('Short sequence - at least 4 bytes expected, only 3 seen', E_USER_WARNING);
			return FALSE;
		}

		$ord3 = ord($chr[3]);

		if ($ord0 >= 240 AND $ord0 <= 247)
		{
			return ($ord0 - 240) * 262144 + ($ord1 - 128) * 4096 + ($ord2-128) * 64 + ($ord3 - 128);
		}

		if ( ! isset($chr[4]))
		{
			trigger_error('Short sequence - at least 5 bytes expected, only 4 seen', E_USER_WARNING);
			return FALSE;
		}

		$ord4 = ord($chr[4]);

		if ($ord0 >= 248 AND $ord0 <= 251)
		{
			return ($ord0 - 248) * 16777216 + ($ord1-128) * 262144 + ($ord2 - 128) * 4096 + ($ord3 - 128) * 64 + ($ord4 - 128);
		}

		if ( ! isset($chr[5]))
		{
			trigger_error('Short sequence - at least 6 bytes expected, only 5 seen', E_USER_WARNING);
			return FALSE;
		}

		if ($ord0 >= 252 AND $ord0 <= 253)
		{
			return ($ord0 - 252) * 1073741824 + ($ord1 - 128) * 16777216 + ($ord2 - 128) * 262144 + ($ord3 - 128) * 4096 + ($ord4 - 128) * 64 + (ord($chr[5]) - 128);
		}

		if ($ord0 >= 254 AND $ord0 <= 255)
		{
			trigger_error('Invalid UTF-8 with surrogate ordinal '.$ord0, E_USER_WARNING);
			return FALSE;
		}
	}

	/**
	 * Takes an UTF-8 string and returns an array of ints representing the Unicode characters.
	 * Astral planes are supported i.e. the ints in the output can be > 0xFFFF.
	 * Occurrances of the BOM are ignored. Surrogates are not allowed.
	 *
	 * The Original Code is Mozilla Communicator client code.
	 * The Initial Developer of the Original Code is Netscape Communications Corporation.
	 * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
	 * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see http://hsivonen.iki.fi/php-utf8/.
	 * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>.
	 *
	 * @param   string   UTF-8 encoded string
	 * @return  array    unicode code points
	 * @return  boolean  FALSE if the string is invalid
	 */
	public static function to_unicode($str)
	{
		$mState = 0; // cached expected number of octets after the current octet until the beginning of the next UTF8 character sequence
		$mUcs4  = 0; // cached Unicode character
		$mBytes = 1; // cached expected number of octets in the current sequence

		$out = array();

		$len = strlen($str);

		for ($i = 0; $i < $len; $i++)
		{
			$in = ord($str[$i]);

			if ($mState == 0)
			{
				// When mState is zero we expect either a US-ASCII character or a
				// multi-octet sequence.
				if (0 == (0x80 & $in))
				{
					// US-ASCII, pass straight through.
					$out[] = $in;
					$mBytes = 1;
				}
				elseif (0xC0 == (0xE0 & $in))
				{
					// First octet of 2 octet sequence
					$mUcs4 = $in;
					$mUcs4 = ($mUcs4 & 0x1F) << 6;
					$mState = 1;
					$mBytes = 2;
				}
				elseif (0xE0 == (0xF0 & $in))
				{
					// First octet of 3 octet sequence
					$mUcs4 = $in;
					$mUcs4 = ($mUcs4 & 0x0F) << 12;
					$mState = 2;
					$mBytes = 3;
				}
				elseif (0xF0 == (0xF8 & $in))
				{
					// First octet of 4 octet sequence
					$mUcs4 = $in;
					$mUcs4 = ($mUcs4 & 0x07) << 18;
					$mState = 3;
					$mBytes = 4;
				}
				elseif (0xF8 == (0xFC & $in))
				{
					// First octet of 5 octet sequence.
					//
					// This is illegal because the encoded codepoint must be either
					// (a) not the shortest form or
					// (b) outside the Unicode range of 0-0x10FFFF.
					// Rather than trying to resynchronize, we will carry on until the end
					// of the sequence and let the later error handling code catch it.
					$mUcs4 = $in;
					$mUcs4 = ($mUcs4 & 0x03) << 24;
					$mState = 4;
					$mBytes = 5;
				}
				elseif (0xFC == (0xFE & $in))
				{
					// First octet of 6 octet sequence, see comments for 5 octet sequence.
					$mUcs4 = $in;
					$mUcs4 = ($mUcs4 & 1) << 30;
					$mState = 5;
					$mBytes = 6;
				}
				else
				{
					// Current octet is neither in the US-ASCII range nor a legal first octet of a multi-octet sequence.
					trigger_error('utf8::to_unicode: Illegal sequence identifier in UTF-8 at byte '.$i, E_USER_WARNING);
					return FALSE;
				}
			}
			else
			{
				// When mState is non-zero, we expect a continuation of the multi-octet sequence
				if (0x80 == (0xC0 & $in))
				{
					// Legal continuation
					$shift = ($mState - 1) * 6;
					$tmp = $in;
					$tmp = ($tmp & 0x0000003F) << $shift;
					$mUcs4 |= $tmp;

					// End of the multi-octet sequence. mUcs4 now contains the final Unicode codepoint to be output
					if (0 == --$mState)
					{
						// Check for illegal sequences and codepoints

						// From Unicode 3.1, non-shortest form is illegal
						if (((2 == $mBytes) AND ($mUcs4 < 0x0080)) OR
							((3 == $mBytes) AND ($mUcs4 < 0x0800)) OR
							((4 == $mBytes) AND ($mUcs4 < 0x10000)) OR
							(4 < $mBytes) OR
							// From Unicode 3.2, surrogate characters are illegal
							(($mUcs4 & 0xFFFFF800) == 0xD800) OR
							// Codepoints outside the Unicode range are illegal
							($mUcs4 > 0x10FFFF))
						{
							trigger_error('utf8::to_unicode: Illegal sequence or codepoint in UTF-8 at byte '.$i, E_USER_WARNING);
							return FALSE;
						}

						if (0xFEFF != $mUcs4)
						{
							// BOM is legal but we don't want to output it
							$out[] = $mUcs4;
						}

						// Initialize UTF-8 cache
						$mState = 0;
						$mUcs4  = 0;
						$mBytes = 1;
					}
				}
				else
				{
					// ((0xC0 & (*in) != 0x80) AND (mState != 0))
					// Incomplete multi-octet sequence
					trigger_error('utf8::to_unicode: Incomplete multi-octet sequence in UTF-8 at byte '.$i, E_USER_WARNING);
					return FALSE;
				}
			}
		}

		return $out;
	}

	/**
	 * Takes an array of ints representing the Unicode characters and returns a UTF-8 string.
	 * Astral planes are supported i.e. the ints in the input can be > 0xFFFF.
	 * Occurrances of the BOM are ignored. Surrogates are not allowed.
	 *
	 * The Original Code is Mozilla Communicator client code.
	 * The Initial Developer of the Original Code is Netscape Communications Corporation.
	 * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
	 * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see http://hsivonen.iki.fi/php-utf8/.
	 * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>.
	 *
	 * @param   array    unicode code points representing a string
	 * @return  string   utf8 string of characters
	 * @return  boolean  FALSE if a code point cannot be found
	 */
	public static function from_unicode($arr)
	{
		ob_start();

		$keys = array_keys($arr);

		foreach ($keys as $k)
		{
			// ASCII range (including control chars)
			if (($arr[$k] >= 0) AND ($arr[$k] <= 0x007f))
			{
				echo chr($arr[$k]);
			}
			// 2 byte sequence
			elseif ($arr[$k] <= 0x07ff)
			{
				echo chr(0xc0 | ($arr[$k] >> 6));
				echo chr(0x80 | ($arr[$k] & 0x003f));
			}
			// Byte order mark (skip)
			elseif ($arr[$k] == 0xFEFF)
			{
				// nop -- zap the BOM
			}
			// Test for illegal surrogates
			elseif ($arr[$k] >= 0xD800 AND $arr[$k] <= 0xDFFF)
			{
				// Found a surrogate
				trigger_error('utf8::from_unicode: Illegal surrogate at index: '.$k.', value: '.$arr[$k], E_USER_WARNING);
				return FALSE;
			}
			// 3 byte sequence
			elseif ($arr[$k] <= 0xffff)
			{
				echo chr(0xe0 | ($arr[$k] >> 12));
				echo chr(0x80 | (($arr[$k] >> 6) & 0x003f));
				echo chr(0x80 | ($arr[$k] & 0x003f));
			}
			// 4 byte sequence
			elseif ($arr[$k] <= 0x10ffff)
			{
				echo chr(0xf0 | ($arr[$k] >> 18));
				echo chr(0x80 | (($arr[$k] >> 12) & 0x3f));
				echo chr(0x80 | (($arr[$k] >> 6) & 0x3f));
				echo chr(0x80 | ($arr[$k] & 0x3f));
			}
			// Out of range
			else
			{
				trigger_error('utf8::from_unicode: Codepoint out of Unicode range at index: '.$k.', value: '.$arr[$k], E_USER_WARNING);
				return FALSE;
			}
		}

		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

} // End utf8