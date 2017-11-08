<?php

/**
 * PEL: PHP Exif Library.
 * A library with support for reading and
 * writing all Exif headers in JPEG and TIFF images using PHP.
 *
 * Copyright (C) 2004, 2005 Martin Geisler.
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
 * Routines for converting back and forth between bytes and integers.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public
 *          License (GPL)
 * @package PEL
 */

/**
 * Conversion functions to and from bytes and integers.
 *
 * The functions found in this class are used to convert bytes into
 * integers of several sizes ({@link bytesToShort}, {@link
 * bytesToLong}, and {@link bytesToRational}) and convert integers of
 * several sizes into bytes ({@link shortToBytes} and {@link
 * longToBytes}).
 *
 * All the methods are static and they all rely on an argument that
 * specifies the byte order to be used, this must be one of the class
 * constants {@link LITTLE_ENDIAN} or {@link BIG_ENDIAN}. These
 * constants will be referred to as the pseudo type PelByteOrder
 * throughout the documentation.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 */
class PelConvert
{

    /**
     * Little-endian (Intel) byte order.
     *
     * Data stored in little-endian byte order store the least
     * significant byte first, so the number 0x12345678 becomes 0x78
     * 0x56 0x34 0x12 when stored with little-endian byte order.
     */
    const LITTLE_ENDIAN = true;

    /**
     * Big-endian (Motorola) byte order.
     *
     * Data stored in big-endian byte order store the most significant
     * byte first, so the number 0x12345678 becomes 0x12 0x34 0x56 0x78
     * when stored with big-endian byte order.
     */
    const BIG_ENDIAN = false;

    /**
     * Convert an unsigned short into two bytes.
     *
     * @param integer $value
     *            the unsigned short that will be converted. The lower
     *            two bytes will be extracted regardless of the actual size passed.
     *
     * @param integer $endian
     *            one of {@link LITTLE_ENDIAN} and {@link
     *            BIG_ENDIAN}.
     *
     * @return string the bytes representing the unsigned short.
     */
    public static function shortToBytes($value, $endian)
    {
        if ($endian == self::LITTLE_ENDIAN) {
            return chr($value) . chr($value >> 8);
        } else {
            return chr($value >> 8) . chr($value);
        }
    }

    /**
     * Convert a signed short into two bytes.
     *
     * @param integer $value
     *            the signed short that will be converted. The lower
     *            two bytes will be extracted regardless of the actual size passed.
     *
     * @param integer $endian
     *            one of {@link LITTLE_ENDIAN} and {@link
     *            BIG_ENDIAN}.
     *
     * @return string the bytes representing the signed short.
     */
    public static function sShortToBytes($value, $endian)
    {
        /*
         * We can just use shortToBytes, since signed shorts fits well
         * within the 32 bit signed integers used in PHP.
         */
        return self::shortToBytes($value, $endian);
    }

    /**
     * Convert an unsigned long into four bytes.
     *
     * Because PHP limits the size of integers to 32 bit signed, one
     * cannot really have an unsigned integer in PHP. But integers
     * larger than 2^31-1 will be promoted to 64 bit signed floating
     * point numbers, and so such large numbers can be handled too.
     *
     * @param integer $value
     *            the unsigned long that will be converted. The
     *            argument will be treated as an unsigned 32 bit integer and the
     *            lower four bytes will be extracted. Treating the argument as an
     *            unsigned integer means that the absolute value will be used. Use
     *            {@link sLongToBytes} to convert signed integers.
     *
     * @param integer $endian
     *            one of {@link LITTLE_ENDIAN} and {@link
     *            BIG_ENDIAN}.
     *
     * @return string the bytes representing the unsigned long.
     */
    public static function longToBytes($value, $endian)
    {
        /*
         * We cannot convert the number to bytes in the normal way (using
         * shifts and modulo calculations) because the PHP operator >> and
         * function chr() clip their arguments to 2^31-1, which is the
         * largest signed integer known to PHP. But luckily base_convert
         * handles such big numbers.
         */
        $hex = str_pad(base_convert($value, 10, 16), 8, '0', STR_PAD_LEFT);
        if ($endian == self::LITTLE_ENDIAN) {
            return (chr(hexdec($hex{6} . $hex{7})) . chr(hexdec($hex{4} . $hex{5})) . chr(hexdec($hex{2} . $hex{3})) .
                 chr(hexdec($hex{0} . $hex{1})));
        } else {
            return (chr(hexdec($hex{0} . $hex{1})) . chr(hexdec($hex{2} . $hex{3})) . chr(hexdec($hex{4} . $hex{5})) .
                 chr(hexdec($hex{6} . $hex{7})));
        }
    }

    /**
     * Convert a signed long into four bytes.
     *
     * @param integer $value
     *            the signed long that will be converted. The argument
     *            will be treated as a signed 32 bit integer, from which the lower
     *            four bytes will be extracted.
     *
     * @param integer $endian
     *            one of {@link LITTLE_ENDIAN} and {@link
     *            BIG_ENDIAN}.
     *
     * @return string the bytes representing the signed long.
     */
    public static function sLongToBytes($value, $endian)
    {
        /*
         * We can convert the number into bytes in the normal way using
         * shifts and modulo calculations here (in contrast with
         * longToBytes) because PHP automatically handles 32 bit signed
         * integers for us.
         */
        if ($endian == self::LITTLE_ENDIAN) {
            return (chr($value) . chr($value >> 8) . chr($value >> 16) . chr($value >> 24));
        } else {
            return (chr($value >> 24) . chr($value >> 16) . chr($value >> 8) . chr($value));
        }
    }

    /**
     * Extract an unsigned byte from a string of bytes.
     *
     * @param string $bytes
     *            the bytes.
     *
     * @param integer $offset
     *            The byte found at the offset will be
     *            returned as an integer. The must be at least one byte available
     *            at offset.
     *
     * @return integer $offset the unsigned byte found at offset, e.g., an integer
     *         in the range 0 to 255.
     */
    public static function bytesToByte($bytes, $offset)
    {
        return ord($bytes{$offset});
    }

    /**
     * Extract a signed byte from bytes.
     *
     * @param string $bytes
     *            the bytes.
     *
     * @param integer $offset
     *            the offset. The byte found at the offset will be
     *            returned as an integer. The must be at least one byte available
     *            at offset.
     *
     * @return integer the signed byte found at offset, e.g., an integer in
     *         the range -128 to 127.
     */
    public static function bytesToSByte($bytes, $offset)
    {
        $n = self::bytesToByte($bytes, $offset);
        if ($n > 127) {
            return $n - 256;
        } else {
            return $n;
        }
    }

    /**
     * Extract an unsigned short from bytes.
     *
     * @param string $bytes
     *            the bytes.
     *
     * @param integer $offset
     *            the offset. The short found at the offset will be
     *            returned as an integer. There must be at least two bytes
     *            available beginning at the offset given.
     * @param integer $endian
     *            one of {@link LITTLE_ENDIAN} and {@link
     *            BIG_ENDIAN}.
     * @return integer the unsigned short found at offset, e.g., an integer
     *         in the range 0 to 65535.
     *
     */
    public static function bytesToShort($bytes, $offset, $endian)
    {
        if ($endian == self::LITTLE_ENDIAN) {
            return (ord($bytes{$offset + 1}) * 256 + ord($bytes{$offset}));
        } else {
            return (ord($bytes{$offset}) * 256 + ord($bytes{$offset + 1}));
        }
    }

    /**
     * Extract a signed short from bytes.
     *
     * @param string $bytes
     *
     * @param integer $offset
     *            The short found at offset will be returned
     *            as an integer. There must be at least two bytes available
     *            beginning at the offset given.
     * @param integer $endian
     *            one of {@link LITTLE_ENDIAN} and {@link
     *            BIG_ENDIAN}.
     * @return integer the signed byte found at offset, e.g., an integer in
     *         the range -32768 to 32767.
     *
     */
    public static function bytesToSShort($bytes, $offset, $endian)
    {
        $n = self::bytesToShort($bytes, $offset, $endian);
        if ($n > 32767) {
            return $n - 65536;
        } else {
            return $n;
        }
    }

    /**
     * Extract an unsigned long from bytes.
     *
     * @param string $bytes
     *
     * @param integer $offset
     *            The long found at offset will be returned
     *            as an integer. There must be at least four bytes available
     *            beginning at the offset given.
     * @param integer $endian
     *            one of {@link LITTLE_ENDIAN} and {@link
     *            BIG_ENDIAN}.
     * @return integer the unsigned long found at offset, e.g., an integer
     *         in the range 0 to 4294967295.
     *
     */
    public static function bytesToLong($bytes, $offset, $endian)
    {
        if ($endian == self::LITTLE_ENDIAN) {
            return (ord($bytes{$offset + 3}) * 16777216 + ord($bytes{$offset + 2}) * 65536 +
                 ord($bytes{$offset + 1}) * 256 + ord($bytes{$offset}));
        } else {
            return (ord($bytes{$offset}) * 16777216 + ord($bytes{$offset + 1}) * 65536 + ord($bytes{$offset + 2}) * 256 +
                 ord($bytes{$offset + 3}));
        }
    }

    /**
     * Extract a signed long from bytes.
     *
     * @param string $bytes
     *
     * @param integer $offset
     *            The long found at offset will be returned
     *            as an integer. There must be at least four bytes available
     *            beginning at the offset given.
     * @param integer $endian
     *            one of {@link LITTLE_ENDIAN} and {@link
     *            BIG_ENDIAN}. *
     * @return integer the signed long found at offset, e.g., an integer in
     *         the range -2147483648 to 2147483647.
     *
     */
    public static function bytesToSLong($bytes, $offset, $endian)
    {
        $n = self::bytesToLong($bytes, $offset, $endian);
        if ($n > 2147483647) {
            return $n - 4294967296;
        } else {
            return $n;
        }
    }

    /**
     * Extract an unsigned rational from bytes.
     *
     * @param string $bytes
     *
     * @param integer $offset
     *            The rational found at offset will be
     *            returned as an array. There must be at least eight bytes
     *            available beginning at the offset given.
     * @param integer $endian
     *            one of {@link LITTLE_ENDIAN} and {@link
     *            BIG_ENDIAN}. *
     * @return array the unsigned rational found at offset, e.g., an
     *         array with two integers in the range 0 to 4294967295.
     *
     */
    public static function bytesToRational($bytes, $offset, $endian)
    {
        return array(
            self::bytesToLong($bytes, $offset, $endian),
            self::bytesToLong($bytes, $offset + 4, $endian)
        );
    }

    /**
     * Extract a signed rational from bytes.
     *
     * @param string $bytes
     *
     * @param integer $offset
     *            The rational found at offset will be
     *            returned as an array. There must be at least eight bytes
     *            available beginning at the offset given.
     * @param integer $endian
     *            one of {@link LITTLE_ENDIAN} and {@link
     *            BIG_ENDIAN}.
     * @return array the signed rational found at offset, e.g., an array
     *         with two integers in the range -2147483648 to 2147483647.
     *
     */
    public static function bytesToSRational($bytes, $offset, $endian)
    {
        return array(
            self::bytesToSLong($bytes, $offset, $endian),
            self::bytesToSLong($bytes, $offset + 4, $endian)
        );
    }

    /**
     * Format bytes for dumping.
     *
     * This method is for debug output, it will format a string as a
     * hexadecimal dump suitable for display on a terminal. The output
     * is printed directly to standard out.
     *
     * @param string $bytes
     *            the bytes that will be dumped.
     *
     * @param integer $max
     *            the maximum number of bytes to dump. If this is left
     *            out (or left to the default of 0), then the entire string will be
     *            dumped.
     * @return void
     */
    public static function bytesToDump($bytes, $max = 0)
    {
        $s = strlen($bytes);

        if ($max > 0) {
            $s = min($max, $s);
        }
        $line = 24;

        for ($i = 0; $i < $s; $i ++) {
            printf('%02X ', ord($bytes{$i}));

            if (($i + 1) % $line == 0) {
                print("\n");
            }
        }
        print("\n");
    }
}
