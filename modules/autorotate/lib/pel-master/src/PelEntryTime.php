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
 * Classes used to hold ASCII strings.
 *
 * The classes defined here are to be used for Exif entries holding
 * ASCII strings, such as {@link PelTag::MAKE}, {@link
 * PelTag::SOFTWARE}, and {@link PelTag::DATE_TIME}. For
 * entries holding normal textual ASCII strings the class {@link
 * PelEntryAscii} should be used, but for entries holding
 * timestamps the class {@link PelEntryTime} would be more
 * convenient instead. Copyright information is handled by the {@link
 * PelEntryCopyright} class.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public
 *          License (GPL)
 * @package PEL
 */

/**
 * Class for holding a date and time.
 *
 * This class can hold a timestamp, and it will be used as
 * in this example where the time is advanced by one week:
 * <code>
 * $entry = $ifd->getEntry(PelTag::DATE_TIME_ORIGINAL);
 * $time = $entry->getValue();
 * print('The image was taken on the ' . date('jS', $time));
 * $entry->setValue($time + 7 * 24 * 3600);
 * </code>
 *
 * The example used a standard UNIX timestamp, which is the default
 * for this class.
 *
 * But the Exif format defines dates outside the range of a UNIX
 * timestamp (about 1970 to 2038) and so you can also get access to
 * the timestamp in two other formats: a simple string or a Julian Day
 * Count. Please see the Calendar extension in the PHP Manual for more
 * information about the Julian Day Count.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 */
class PelEntryTime extends PelEntryAscii
{

    /**
     * Constant denoting a UNIX timestamp.
     */
    const UNIX_TIMESTAMP = 1;

    /**
     * Constant denoting a Exif string.
     */
    const EXIF_STRING = 2;

    /**
     * Constant denoting a Julian Day Count.
     */
    const JULIAN_DAY_COUNT = 3;

    /**
     * The Julian Day Count of the timestamp held by this entry.
     *
     * This is an integer counting the number of whole days since
     * January 1st, 4713 B.C. The fractional part of the timestamp held
     * by this entry is stored in {@link $seconds}.
     *
     * @var int
     */
    private $day_count;

    /**
     * The number of seconds into the day of the timestamp held by this
     * entry.
     *
     * The number of whole days is stored in {@link $day_count} and the
     * number of seconds left-over is stored here.
     *
     * @var int
     */
    private $seconds;

    /**
     * Make a new entry for holding a timestamp.
     *
     * @param integer $tag
     *            the Exif tag which this entry represents. There are
     *            only three standard tags which hold timestamp, so this should be
     *            one of the constants {@link PelTag::DATE_TIME}, {@link
     *            PelTag::DATE_TIME_ORIGINAL}, or {@link
     *            PelTag::DATE_TIME_DIGITIZED}.
     *
     * @param integer $timestamp
     *            the timestamp held by this entry in the correct form
     *            as indicated by the third argument. For {@link UNIX_TIMESTAMP}
     *            this is an integer counting the number of seconds since January
     *            1st 1970, for {@link EXIF_STRING} this is a string of the form
     *            'YYYY:MM:DD hh:mm:ss', and for {@link JULIAN_DAY_COUNT} this is a
     *            floating point number where the integer part denotes the day
     *            count and the fractional part denotes the time of day (0.25 means
     *            6:00, 0.75 means 18:00).
     *
     * @param integer $type
     *            the type of the timestamp. This must be one of
     *            {@link UNIX_TIMESTAMP}, {@link EXIF_STRING}, or
     *            {@link JULIAN_DAY_COUNT}.
     */
    public function __construct($tag, $timestamp, $type = self::UNIX_TIMESTAMP)
    {
        parent::__construct($tag);
        $this->setValue($timestamp, $type);
    }

    /**
     * Return the timestamp of the entry.
     *
     * The timestamp held by this entry is returned in one of three
     * formats: as a standard UNIX timestamp (default), as a fractional
     * Julian Day Count, or as a string.
     *
     * @param integer $type
     *            the type of the timestamp. This must be one of
     *            {@link UNIX_TIMESTAMP}, {@link EXIF_STRING}, or
     *            {@link JULIAN_DAY_COUNT}.
     *
     * @return integer the timestamp held by this entry in the correct form
     *         as indicated by the type argument. For {@link UNIX_TIMESTAMP}
     *         this is an integer counting the number of seconds since January
     *         1st 1970, for {@link EXIF_STRING} this is a string of the form
     *         'YYYY:MM:DD hh:mm:ss', and for {@link JULIAN_DAY_COUNT} this is a
     *         floating point number where the integer part denotes the day
     *         count and the fractional part denotes the time of day (0.25 means
     *         6:00, 0.75 means 18:00).
     */
    public function getValue($type = self::UNIX_TIMESTAMP)
    {
        switch ($type) {
            case self::UNIX_TIMESTAMP:
                $seconds = $this->convertJdToUnix($this->day_count);
                if ($seconds === false) {
                    /*
                     * We get false if the Julian Day Count is outside the range
                     * of a UNIX timestamp.
                     */
                    return false;
                } else {
                    return $seconds + $this->seconds;
                }
                break;
            case self::EXIF_STRING:
                list ($year, $month, $day) = $this->convertJdToGregorian($this->day_count);
                $hours = (int) ($this->seconds / 3600);
                $minutes = (int) ($this->seconds % 3600 / 60);
                $seconds = $this->seconds % 60;
                return sprintf('%04d:%02d:%02d %02d:%02d:%02d', $year, $month, $day, $hours, $minutes, $seconds);
            case self::JULIAN_DAY_COUNT:
                return $this->day_count + $this->seconds / 86400;
            default:
                throw new PelInvalidArgumentException(
                    'Expected UNIX_TIMESTAMP (%d), ' . 'EXIF_STRING (%d), or ' . 'JULIAN_DAY_COUNT (%d) for $type, ' .
                         'got %d.',
                        self::UNIX_TIMESTAMP,
                        self::EXIF_STRING,
                        self::JULIAN_DAY_COUNT,
                        $type);
        }
    }

    /**
     * Update the timestamp held by this entry.
     *
     * @param integer $timestamp
     *            the timestamp held by this entry in the correct form
     *            as indicated by the third argument. For {@link UNIX_TIMESTAMP}
     *            this is an integer counting the number of seconds since January
     *            1st 1970, for {@link EXIF_STRING} this is a string of the form
     *            'YYYY:MM:DD hh:mm:ss', and for {@link JULIAN_DAY_COUNT} this is a
     *            floating point number where the integer part denotes the day
     *            count and the fractional part denotes the time of day (0.25 means
     *            6:00, 0.75 means 18:00).
     *
     * @param integer $type
     *            the type of the timestamp. This must be one of
     *            {@link UNIX_TIMESTAMP}, {@link EXIF_STRING}, or
     *            {@link JULIAN_DAY_COUNT}.
     */
    public function setValue($timestamp, $type = self::UNIX_TIMESTAMP)
    {
        switch ($type) {
            case self::UNIX_TIMESTAMP:
                $this->day_count = $this->convertUnixToJd($timestamp);
                $this->seconds = $timestamp % 86400;
                break;

            case self::EXIF_STRING:
                /* Clean the timestamp: some timestamps are broken other
                 * separators than ':' and ' '. */
                $d = preg_split('/[^0-9]+/', $timestamp);
                for ($i = 0; $i < 6; $i ++) {
                    if (empty($d[$i])) {
                        $d[$i] = 0;
                    }
                }
                $this->day_count = $this->convertGregorianToJd($d[0], $d[1], $d[2]);
                $this->seconds = $d[3] * 3600 + $d[4] * 60 + $d[5];
                break;

            case self::JULIAN_DAY_COUNT:
                $this->day_count = (int) floor($timestamp);
                $this->seconds = (int) (86400 * ($timestamp - floor($timestamp)));
                break;

            default:
                throw new PelInvalidArgumentException(
                    'Expected UNIX_TIMESTAMP (%d), ' . 'EXIF_STRING (%d), or ' . 'JULIAN_DAY_COUNT (%d) for $type, ' .
                         'got %d.',
                        self::UNIX_TIMESTAMP,
                        self::EXIF_STRING,
                        self::JULIAN_DAY_COUNT,
                        $type);
        }

        /*
         * Now finally update the string which will be used when this is
         * turned into bytes.
         */
        parent::setValue($this->getValue(self::EXIF_STRING));
    }

    // The following four functions are used for converting back and
    // forth between the date formats. They are used in preference to
    // the ones from the PHP calendar extension to avoid having to
    // fiddle with timezones and to avoid depending on the extension.
    //
    // See http://www.hermetic.ch/cal_stud/jdn.htm#comp for a reference.

    /**
     * Converts a date in year/month/day format to a Julian Day count.
     *
     * @param integer $year
     *            the year.
     * @param integer $month
     *            the month, 1 to 12.
     * @param integer $day
     *            the day in the month.
     * @return integer the Julian Day count.
     */
    public function convertGregorianToJd($year, $month, $day)
    {
        // Special case mapping 0/0/0 -> 0
        if ($year == 0 || $month == 0 || $day == 0) {
            return 0;
        }

        $m1412 = ($month <= 2) ? - 1 : 0;
        return floor((1461 * ($year + 4800 + $m1412)) / 4) + floor((367 * ($month - 2 - 12 * $m1412)) / 12) -
             floor((3 * floor(($year + 4900 + $m1412) / 100)) / 4) + $day - 32075;
    }

    /**
     * Converts a Julian Day count to a year/month/day triple.
     *
     * @param
     *            int the Julian Day count.
     * @return array an array with three entries: year, month, day.
     */
    public function convertJdToGregorian($jd)
    {
        // Special case mapping 0 -> 0/0/0
        if ($jd == 0) {
            return array(
                0,
                0,
                0
            );
        }

        $l = $jd + 68569;
        $n = floor((4 * $l) / 146097);
        $l = $l - floor((146097 * $n + 3) / 4);
        $i = floor((4000 * ($l + 1)) / 1461001);
        $l = $l - floor((1461 * $i) / 4) + 31;
        $j = floor((80 * $l) / 2447);
        $d = $l - floor((2447 * $j) / 80);
        $l = floor($j / 11);
        $m = $j + 2 - (12 * $l);
        $y = 100 * ($n - 49) + $i + $l;
        return array(
            $y,
            $m,
            $d
        );
    }

    /**
     * Converts a UNIX timestamp to a Julian Day count.
     *
     * @param integer $timestamp
     *            the timestamp.
     * @return integer the Julian Day count.
     */
    public function convertUnixToJd($timestamp)
    {
        return (int) (floor($timestamp / 86400) + 2440588);
    }

    /**
     * Converts a Julian Day count to a UNIX timestamp.
     *
     * @param integer $jd
     *            the Julian Day count.
     *
     * @return mixed $timestamp the integer timestamp or false if the
     *         day count cannot be represented as a UNIX timestamp.
     */
    public function convertJdToUnix($jd)
    {
        if ($jd > 0) {
            $timestamp = ($jd - 2440588) * 86400;
            if ($timestamp >= 0) {
                return $timestamp;
            }
        }
        return false;
    }
}
