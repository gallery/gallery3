<?php

/**
 * PEL: PHP Exif Library.
 * A library with support for reading and
 * writing all Exif headers in JPEG and TIFF images using PHP.
 *
 * Copyright (C) 2004, 2005, 2006 Martin Geisler.
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
 * Abstract class for numbers.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public
 *          License (GPL)
 * @package PEL
 */

/**
 * Class for holding numbers.
 *
 * This class can hold numbers, with range checks.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 */
abstract class PelEntryNumber extends PelEntry
{

    /**
     * The value held by this entry.
     *
     * @var array
     */
    protected $value = array();

    /**
     * The minimum allowed value.
     *
     * Any attempt to change the value below this variable will result
     * in a {@link PelOverflowException} being thrown.
     *
     * @var int
     */
    protected $min;

    /**
     * The maximum allowed value.
     *
     * Any attempt to change the value over this variable will result in
     * a {@link PelOverflowException} being thrown.
     *
     * @var int
     */
    protected $max;

    /**
     * The dimension of the number held.
     *
     * Normal numbers have a dimension of one, pairs have a dimension of
     * two, etc.
     *
     * @var int
     */
    protected $dimension = 1;

    /**
     * Change the value.
     *
     * This method can change both the number of components and the
     * value of the components. Range checks will be made on the new
     * value, and a {@link PelOverflowException} will be thrown if the
     * value is found to be outside the legal range.
     *
     * The method accept several number arguments. The {@link getValue}
     * method will always return an array except for when a single
     * number is given here.
     *
     * @param int|array $value...
     *            the new value(s). This can be zero or
     *            more numbers, that is, either integers or arrays. The input will
     *            be checked to ensure that the numbers are within the valid range.
     *            If not, then a {@link PelOverflowException} will be thrown.
     *
     * @see getValue
     */
    public function setValue($value)
    {
        $value = func_get_args();
        $this->setValueArray($value);
    }

    /**
     * Change the value.
     *
     * This method can change both the number of components and the
     * value of the components. Range checks will be made on the new
     * value, and a {@link PelOverflowException} will be thrown if the
     * value is found to be outside the legal range.
     *
     * @param
     *            array the new values. The array must contain the new
     *            numbers.
     *
     * @see getValue
     */
    public function setValueArray($value)
    {
        foreach ($value as $v) {
            $this->validateNumber($v);
        }

        $this->components = count($value);
        $this->value = $value;
    }

    /**
     * Return the numeric value held.
     *
     * @return int|array this will either be a single number if there is
     *         only one component, or an array of numbers otherwise.
     */
    public function getValue()
    {
        if ($this->components == 1) {
            return $this->value[0];
        } else {
            return $this->value;
        }
    }

    /**
     * Validate a number.
     *
     * This method will check that the number given is within the range
     * given my {@link getMin()} and {@link getMax()}, inclusive. If
     * not, then a {@link PelOverflowException} is thrown.
     *
     * @param
     *            int|array the number in question.
     *
     * @return void nothing, but will throw a {@link
     *         PelOverflowException} if the number is found to be outside the
     *         legal range and {@link Pel::$strict} is true.
     */
    public function validateNumber($n)
    {
        if ($this->dimension == 1) {
            if ($n < $this->min || $n > $this->max) {
                Pel::maybeThrow(new PelOverflowException($n, $this->min, $this->max));
            }
        } else {
            for ($i = 0; $i < $this->dimension; $i ++) {
                if ($n[$i] < $this->min || $n[$i] > $this->max) {
                    Pel::maybeThrow(new PelOverflowException($n[$i], $this->min, $this->max));
                }
            }
        }
    }

    /**
     * Add a number.
     *
     * This appends a number to the numbers already held by this entry,
     * thereby increasing the number of components by one.
     *
     * @param
     *            int|array the number to be added.
     */
    public function addNumber($n)
    {
        $this->validateNumber($n);
        $this->value[] = $n;
        $this->components ++;
    }

    /**
     * Convert a number into bytes.
     *
     * The concrete subclasses will have to implement this method so
     * that the numbers represented can be turned into bytes.
     *
     * The method will be called once for each number held by the entry.
     *
     * @param
     *            int the number that should be converted.
     *
     * @param
     *            PelByteOrder one of {@link PelConvert::LITTLE_ENDIAN} and
     *            {@link PelConvert::BIG_ENDIAN}, specifying the target byte order.
     *
     * @return string bytes representing the number given.
     */
    abstract public function numberToBytes($number, $order);

    /**
     * Turn this entry into bytes.
     *
     * @param
     *            PelByteOrder the desired byte order, which must be either
     *            {@link PelConvert::LITTLE_ENDIAN} or {@link
     *            PelConvert::BIG_ENDIAN}.
     *
     * @return string bytes representing this entry.
     */
    public function getBytes($o)
    {
        $bytes = '';
        for ($i = 0; $i < $this->components; $i ++) {
            if ($this->dimension == 1) {
                $bytes .= $this->numberToBytes($this->value[$i], $o);
            } else {
                for ($j = 0; $j < $this->dimension; $j ++) {
                    $bytes .= $this->numberToBytes($this->value[$i][$j], $o);
                }
            }
        }
        return $bytes;
    }

    /**
     * Format a number.
     *
     * This method is called by {@link getText} to format numbers.
     * Subclasses should override this method if they need more
     * sophisticated behavior than the default, which is to just return
     * the number as is.
     *
     * @param
     *            int the number which will be formatted.
     *
     * @param
     *            boolean it could be that there is both a verbose and a
     *            brief formatting available, and this argument controls that.
     *
     * @return string the number formatted as a string suitable for
     *         display.
     */
    public function formatNumber($number, $brief = false)
    {
        return $number;
    }

    /**
     * Get the numeric value of this entry as text.
     *
     * @param
     *            boolean use brief output? The numbers will be separated
     *            by a single space if brief output is requested, otherwise a space
     *            and a comma will be used.
     *
     * @return string the numbers(s) held by this entry.
     */
    public function getText($brief = false)
    {
        if ($this->components == 0) {
            return '';
        }

        $str = $this->formatNumber($this->value[0]);
        for ($i = 1; $i < $this->components; $i ++) {
            $str .= ($brief ? ' ' : ', ');
            $str .= $this->formatNumber($this->value[$i]);
        }

        return $str;
    }
}
