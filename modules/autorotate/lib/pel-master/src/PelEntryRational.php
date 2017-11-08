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
 * Class for holding unsigned rational numbers.
 *
 * This class can hold rational numbers, consisting of a numerator and
 * denominator both of which are of type unsigned long. Each rational
 * is represented by an array with just two entries: the numerator and
 * the denominator, in that order.
 *
 * The class can hold either just a single rational or an array of
 * rationals. The class will be used to manipulate any of the Exif
 * tags which can have format {@link PelFormat::RATIONAL} like in this
 * example:
 *
 * <code>
 * $resolution = $ifd->getEntry(PelTag::X_RESOLUTION);
 * $resolution->setValue(array(1, 300));
 * </code>
 *
 * Here the x-resolution is adjusted to 1/300, which will be 300 DPI,
 * unless the {@link PelTag::RESOLUTION_UNIT resolution unit} is set
 * to something different than 2 which means inches.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 */
class PelEntryRational extends PelEntryLong
{

    /**
     * Make a new entry that can hold an unsigned rational.
     *
     * @param
     *            int the tag which this entry represents. This should
     *            be one of the constants defined in {@link PelTag}, e.g., {@link
     *            PelTag::X_RESOLUTION}, or any other tag which can have format
     *            {@link PelFormat::RATIONAL}.
     *
     * @param array $value...
     *            the rational(s) that this entry will
     *            represent. The arguments passed must obey the same rules as the
     *            argument to {@link setValue}, namely that each argument should be
     *            an array with two entries, both of which must be within range of
     *            an unsigned long (32 bit), that is between 0 and 4294967295
     *            (inclusive). If not, then a {@link PelOverflowException} will be
     *            thrown.
     */
    public function __construct($tag, $value = null)
    {
        $this->tag = $tag;
        $this->format = PelFormat::RATIONAL;
        $this->dimension = 2;
        $this->min = 0;
        $this->max = 4294967295;

        $value = func_get_args();
        array_shift($value);
        $this->setValueArray($value);
    }

    /**
     * Format a rational number.
     *
     * The rational will be returned as a string with a slash '/'
     * between the numerator and denominator.
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
        return $number[0] . '/' . $number[1];
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
            case PelTag::FNUMBER:
                // CC (e->components, 1, v);
                return Pel::fmt('f/%.01f', $v[0] / $v[1]);

            case PelTag::APERTURE_VALUE:
                // CC (e->components, 1, v);
                // if (!v_rat.denominator) return (NULL);
                return Pel::fmt('f/%.01f', pow(2, $v[0] / $v[1] / 2));

            case PelTag::FOCAL_LENGTH:
                // CC (e->components, 1, v);
                // if (!v_rat.denominator) return (NULL);
                return Pel::fmt('%.1f mm', $v[0] / $v[1]);

            case PelTag::SUBJECT_DISTANCE:
                // CC (e->components, 1, v);
                // if (!v_rat.denominator) return (NULL);
                return Pel::fmt('%.1f m', $v[0] / $v[1]);

            case PelTag::EXPOSURE_TIME:
                // CC (e->components, 1, v);
                // if (!v_rat.denominator) return (NULL);
                if ($v[0] / $v[1] < 1) {
                    return Pel::fmt('1/%d sec.', $v[1] / $v[0]);
                } else {
                    return Pel::fmt('%d sec.', $v[0] / $v[1]);
                }
                break;
            case PelTag::GPS_LATITUDE:
            case PelTag::GPS_LONGITUDE:
                $degrees = $this->value[0][0] / $this->value[0][1];
                $minutes = $this->value[1][0] / $this->value[1][1];
                $seconds = $this->value[2][0] / $this->value[2][1];

                return sprintf('%s° %s\' %s" (%.2f°)', $degrees, $minutes, $seconds, $degrees + $minutes / 60 + $seconds / 3600);

            default:
                return parent::getText($brief);
        }
    }
}
