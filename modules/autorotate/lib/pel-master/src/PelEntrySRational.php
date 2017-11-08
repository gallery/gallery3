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
 * Classes used to manipulate rational numbers.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public
 *          License (GPL)
 * @package PEL
 */

/**
 * Class for holding signed rational numbers.
 *
 * This class can hold rational numbers, consisting of a numerator and
 * denominator both of which are of type unsigned long. Each rational
 * is represented by an array with just two entries: the numerator and
 * the denominator, in that order.
 *
 * The class can hold either just a single rational or an array of
 * rationals. The class will be used to manipulate any of the Exif
 * tags which can have format {@link PelFormat::SRATIONAL}.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 */
class PelEntrySRational extends PelEntrySLong
{

    /**
     * Make a new entry that can hold a signed rational.
     *
     * @param
     *            int the tag which this entry represents. This should
     *            be one of the constants defined in {@link PelTag}, e.g., {@link
     *            PelTag::SHUTTER_SPEED_VALUE}, or any other tag which can have
     *            format {@link PelFormat::SRATIONAL}.
     *
     * @param array $value...
     *            the rational(s) that this entry will
     *            represent. The arguments passed must obey the same rules as the
     *            argument to {@link setValue}, namely that each argument should be
     *            an array with two entries, both of which must be within range of
     *            a signed long (32 bit), that is between -2147483648 and
     *            2147483647 (inclusive). If not, then a {@link
     *            PelOverflowException} will be thrown.
     */
    public function __construct($tag, $value = null)
    {
        $this->tag = $tag;
        $this->format = PelFormat::SRATIONAL;
        $this->dimension = 2;
        $this->min = - 2147483648;
        $this->max = 2147483647;

        $value = func_get_args();
        array_shift($value);
        $this->setValueArray($value);
    }

    /**
     * Format a rational number.
     *
     * The rational will be returned as a string with a slash '/'
     * between the numerator and denominator. Care is taken to display
     * '-1/2' instead of the ugly but mathematically equivalent '1/-2'.
     *
     * @param
     *            array the rational which will be formatted.
     *
     * @param
     *            boolean not used.
     *
     * @return string the rational formatted as a string suitable for
     *         display.
     */
    public function formatNumber($number, $brief = false)
    {
        if ($number[1] < 0) {
            /* Turn output like 1/-2 into -1/2. */
            return (- $number[0]) . '/' . (- $number[1]);
        } else {
            return $number[0] . '/' . $number[1];
        }
    }

    /**
     * Get the value of an entry as text.
     *
     * The value will be returned in a format suitable for presentation,
     * e.g., rationals will be returned as 'x/y', ASCII strings will be
     * returned as themselves etc.
     *
     * @param
     *            boolean some values can be returned in a long or more
     *            brief form, and this parameter controls that.
     *
     * @return string the value as text.
     */
    public function getText($brief = false)
    {
        if (isset($this->value[0])) {
            $v = $this->value[0];
        }

        switch ($this->tag) {
            case PelTag::SHUTTER_SPEED_VALUE:
                // CC (e->components, 1, v);
                // if (!v_srat.denominator) return (NULL);
                return Pel::fmt('%.0f/%.0f sec. (APEX: %d)', $v[0], $v[1], pow(sqrt(2), $v[0] / $v[1]));

            case PelTag::BRIGHTNESS_VALUE:
                // CC (e->components, 1, v);
                //
                // TODO: figure out the APEX thing, or remove this so that it is
                // handled by the default clause at the bottom.
                return sprintf('%d/%d', $v[0], $v[1]);
            // FIXME: How do I calculate the APEX value?

            case PelTag::EXPOSURE_BIAS_VALUE:
                // CC (e->components, 1, v);
                // if (!v_srat.denominator) return (NULL);
                return sprintf('%s%.01f', $v[0] * $v[1] > 0 ? '+' : '', $v[0] / $v[1]);

            default:
                return parent::getText($brief);
        }
    }
}
