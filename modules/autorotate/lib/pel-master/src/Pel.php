<?php

/**
 * PEL: PHP Exif Library.
 * A library with support for reading and
 * writing all Exif headers in JPEG and TIFF images using PHP.
 *
 * Copyright (C) 2004, 2005, 2006, 2007 Martin Geisler.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program in the file COPYING; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA 02110-1301 USA
 */
namespace lsolesen\pel;

/**
 * Class with miscellaneous static methods.
 *
 * This class will contain various methods that govern the overall
 * behavior of PEL.
 *
 * Debugging output from PEL can be turned on and off by assigning
 * true or false to {@link Pel::$debug}.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 */
class Pel
{

    /**
     * Flag that controls if dgettext can be used.
     * Is set to true or fals at the first access
     *
     * @var boolean|NULL
     */
    private static $hasdgetext = null;

    /**
     * Flag for controlling debug information.
     *
     * The methods producing debug information ({@link debug()} and
     * {@link warning()}) will only output something if this variable is
     * set to true.
     *
     * @var boolean
     */
    private static $debug = false;

    /**
     * Flag for strictness of parsing.
     *
     * If this variable is set to true, then most errors while loading
     * images will result in exceptions being thrown. Otherwise a
     * warning will be emitted (using {@link Pel::warning}) and the
     * exceptions will be appended to {@link Pel::$exceptions}.
     *
     * Some errors will still be fatal and result in thrown exceptions,
     * but an effort will be made to skip over as much garbage as
     * possible.
     *
     * @var boolean
     */
    private static $strict = false;

    /**
     * Stored exceptions.
     *
     * When {@link Pel::$strict} is set to false exceptions will be
     * accumulated here instead of being thrown.
     */
    private static $exceptions = array();

    /**
     * Quality setting for encoding JPEG images.
     *
     * This controls the quality used then PHP image resources are
     * encoded into JPEG images. This happens when you create a
     * {@link PelJpeg} object based on an image resource.
     *
     * The default is 75 for average quality images, but you can change
     * this to an integer between 0 and 100.
     *
     * @var int
     */
    private static $quality = 75;

    /**
     * Set the JPEG encoding quality.
     *
     * @param int $quality
     *            an integer between 0 and 100 with 75 being
     *            average quality and 95 very good quality.
     */
    public static function setJPEGQuality($quality)
    {
        self::$quality = $quality;
    }

    /**
     * Get current setting for JPEG encoding quality.
     *
     * @return int the quality.
     */
    public static function getJPEGQuality()
    {
        return self::$quality;
    }

    /**
     * Return list of stored exceptions.
     *
     * When PEL is parsing in non-strict mode, it will store most
     * exceptions instead of throwing them. Use this method to get hold
     * of them when a call returns.
     *
     * Code for using this could look like this:
     *
     * <code>
     * Pel::setStrictParsing(true);
     * Pel::clearExceptions();
     *
     * $jpeg = new PelJpeg($file);
     *
     * // Check for exceptions.
     * foreach (Pel::getExceptions() as $e) {
     * printf("Exception: %s\n", $e->getMessage());
     * if ($e instanceof PelEntryException) {
     * // Warn about entries that couldn't be loaded.
     * printf("Warning: Problem with %s.\n",
     * PelTag::getName($e->getType(), $e->getTag()));
     * }
     * }
     * </code>
     *
     * This gives applications total control over the amount of error
     * messages shown and (hopefully) provides the necessary information
     * for proper error recovery.
     *
     * @return array the exceptions.
     */
    public static function getExceptions()
    {
        return self::$exceptions;
    }

    /**
     * Clear list of stored exceptions.
     *
     * Use this function before a call to some method if you intend to
     * check for exceptions afterwards.
     */
    public static function clearExceptions()
    {
        self::$exceptions = array();
    }

    /**
     * Conditionally throw an exception.
     *
     * This method will throw the passed exception when strict parsing
     * in effect (see {@link setStrictParsing()}). Otherwise the
     * exception is stored (it can be accessed with {@link
     * getExceptions()}) and a warning is issued (with {@link
     * Pel::warning}).
     *
     * @param PelException $e
     *            the exceptions.
     */
    public static function maybeThrow(PelException $e)
    {
        if (self::$strict) {
            throw $e;
        } else {
            self::$exceptions[] = $e;
            self::warning('%s (%s:%s)', $e->getMessage(), basename($e->getFile()), $e->getLine());
        }
    }

    /**
     * Enable/disable strict parsing.
     *
     * If strict parsing is enabled, then most errors while loading
     * images will result in exceptions being thrown. Otherwise a
     * warning will be emitted (using {@link Pel::warning}) and the
     * exceptions will be stored for later use via {@link
     * getExceptions()}.
     *
     * Some errors will still be fatal and result in thrown exceptions,
     * but an effort will be made to skip over as much garbage as
     * possible.
     *
     * @param boolean $flag
     *            use true to enable strict parsing, false to
     *            diable.
     */
    public static function setStrictParsing($flag)
    {
        self::$strict = $flag;
    }

    /**
     * Get current setting for strict parsing.
     *
     * @return boolean true if strict parsing is in effect, false
     *         otherwise.
     */
    public static function getStrictParsing()
    {
        return self::$strict;
    }

    /**
     * Enable/disable debugging output.
     *
     * @param boolean $flag
     *            use true to enable debug output, false to
     *            diable.
     */
    public static function setDebug($flag)
    {
        self::$debug = $flag;
    }

    /**
     * Get current setting for debug output.
     *
     * @return boolean true if debug is enabled, false otherwise.
     */
    public static function getDebug()
    {
        return self::$debug;
    }

    /**
     * Conditionally output debug information.
     *
     * This method works just like printf() except that it always
     * terminates the output with a newline, and that it only outputs
     * something if the {@link Pel::$debug} is true.
     *
     * @param string $format
     *            the format string.
     *
     * @param mixed ...$args
     *            any number of arguments can be given. The
     *            arguments will be available for the format string as usual with
     *            sprintf().
     */
    public static function debug($format)
    {
        if (self::$debug) {
            $args = func_get_args();
            $str = array_shift($args);
            vprintf($str . "\n", $args);
        }
    }

    /**
     * Conditionally output a warning.
     *
     * This method works just like printf() except that it prepends the
     * output with the string 'Warning: ', terminates the output with a
     * newline, and that it only outputs something if the PEL_DEBUG
     * defined to some true value.
     *
     * @param string $format
     *            the format string.
     *
     * @param mixed ...$args
     *            any number of arguments can be given. The
     *            arguments will be available for the format string as usual with
     *            sprintf().
     */
    public static function warning($format)
    {
        if (self::$debug) {
            $args = func_get_args();
            $str = array_shift($args);
            vprintf('Warning: ' . $str . "\n", $args);
        }
    }

    /**
     * Translate a string.
     *
     * This static function will use Gettext to translate a string. By
     * always using this function for static string one is assured that
     * the translation will be taken from the correct text domain.
     * Dynamic strings should be passed to {@link fmt} instead.
     *
     * @param string $str
     *            the string that should be translated.
     *
     * @return string the translated string, or the original string if
     *         no translation could be found.
     */
    public static function tra($str)
    {
        return self::dgettextWrapper('pel', $str);
    }

    /**
     * Translate and format a string.
     *
     * This static function will first use Gettext to translate a format
     * string, which will then have access to any extra arguments. By
     * always using this function for dynamic string one is assured that
     * the translation will be taken from the correct text domain. If
     * the string is static, use {@link tra} instead as it will be
     * faster.
     *
     * @param string $format
     *            the format string. This will be translated
     *            before being used as a format string.
     *
     * @param mixed ...$args
     *            any number of arguments can be given. The
     *            arguments will be available for the format string as usual with
     *            sprintf().
     *
     * @return string the translated string, or the original string if
     *         no translation could be found.
     */
    public static function fmt($format)
    {
        $args = func_get_args();
        $str = array_shift($args);
        return vsprintf(self::dgettextWrapper('pel', $str), $args);
    }

    /**
     * Warapper for dgettext.
     * The untranslated stub will be return in the case that dgettext is not available.
     *
     * @param string $domain
     * @param string $str
     * @return string
     */
    private static function dgettextWrapper($domain, $str)
    {
        if (self::$hasdgetext === null) {
            self::$hasdgetext = function_exists('dgettext');
            if (self::$hasdgetext === true) {
                bindtextdomain('pel', __DIR__ . '/locale');
            }
        }
        if (self::$hasdgetext) {
            return dgettext($domain, $str);
        } else {
            return $str;
        }
    }
}
