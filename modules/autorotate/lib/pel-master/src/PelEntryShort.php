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
 * Classes used to hold shorts, both signed and unsigned.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public
 *          License (GPL)
 * @package PEL
 */

/**
 * Class for holding signed shorts.
 *
 * This class can hold shorts, either just a single short or an array
 * of shorts. The class will be used to manipulate any of the Exif
 * tags which has format {@link PelFormat::SHORT} like in this
 * example:
 *
 * <code>
 * $w = $ifd->getEntry(PelTag::EXIF_IMAGE_WIDTH);
 * $w->setValue($w->getValue() / 2);
 * $h = $ifd->getEntry(PelTag::EXIF_IMAGE_HEIGHT);
 * $h->setValue($h->getValue() / 2);
 * </code>
 *
 * Here the width and height is updated to 50% of their original
 * values.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 */
class PelEntryShort extends PelEntryNumber
{

    /**
     * Make a new entry that can hold an unsigned short.
     *
     * The method accept several integer arguments. The {@link
     * getValue} method will always return an array except for when a
     * single integer argument is given here.
     *
     * This means that one can conveniently use objects like this:
     * <code>
     * $a = new PelEntryShort(PelTag::EXIF_IMAGE_HEIGHT, 42);
     * $b = $a->getValue() + 314;
     * </code>
     * where the call to {@link getValue} will return an integer
     * instead of an array with one integer element, which would then
     * have to be extracted.
     *
     * @param int $tag
     *            the tag which this entry represents. This should be
     *            one of the constants defined in {@link PelTag}, e.g., {@link
     *            PelTag::IMAGE_WIDTH}, {@link PelTag::ISO_SPEED_RATINGS},
     *            or any other tag with format {@link PelFormat::SHORT}.
     *
     * @param int $value...
     *            the short(s) that this entry will
     *            represent. The argument passed must obey the same rules as the
     *            argument to {@link setValue}, namely that it should be within
     *            range of an unsigned short, that is between 0 and 65535
     *            (inclusive). If not, then a {@link PelOverFlowException} will be
     *            thrown.
     */
    public function __construct($tag, $value = null)
    {
        $this->tag = $tag;
        $this->min = 0;
        $this->max = 65535;
        $this->format = PelFormat::SHORT;

        $value = func_get_args();
        array_shift($value);
        $this->setValueArray($value);
    }

    /**
     * Convert a number into bytes.
     *
     * @param int $number
     *            the number that should be converted.
     *
     * @param PelByteOrder $order
     *            one of {@link PelConvert::LITTLE_ENDIAN} and
     *            {@link PelConvert::BIG_ENDIAN}, specifying the target byte order.
     *
     * @return string bytes representing the number given.
     */
    public function numberToBytes($number, $order)
    {
        return PelConvert::shortToBytes($number, $order);
    }

    /**
     * Get the value of an entry as text.
     *
     * The value will be returned in a format suitable for presentation,
     * e.g., instead of returning '2' for a {@link
     * PelTag::METERING_MODE} tag, 'Center-Weighted Average' is
     * returned.
     *
     * @param
     *            boolean some values can be returned in a long or more
     *            brief form, and this parameter controls that.
     *
     * @return string the value as text.
     */
    public function getText($brief = false)
    {
        switch ($this->tag) {
            case PelTag::METERING_MODE:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 0:
                        return Pel::tra('Unknown');
                    case 1:
                        return Pel::tra('Average');
                    case 2:
                        return Pel::tra('Center-Weighted Average');
                    case 3:
                        return Pel::tra('Spot');
                    case 4:
                        return Pel::tra('Multi Spot');
                    case 5:
                        return Pel::tra('Pattern');
                    case 6:
                        return Pel::tra('Partial');
                    case 255:
                        return Pel::tra('Other');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::COMPRESSION:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 1:
                        return Pel::tra('Uncompressed');
                    case 6:
                        return Pel::tra('JPEG compression');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::PLANAR_CONFIGURATION:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 1:
                        return Pel::tra('chunky format');
                    case 2:
                        return Pel::tra('planar format');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::SENSING_METHOD:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 1:
                        return Pel::tra('Not defined');
                    case 2:
                        return Pel::tra('One-chip color area sensor');
                    case 3:
                        return Pel::tra('Two-chip color area sensor');
                    case 4:
                        return Pel::tra('Three-chip color area sensor');
                    case 5:
                        return Pel::tra('Color sequential area sensor');
                    case 7:
                        return Pel::tra('Trilinear sensor');
                    case 8:
                        return Pel::tra('Color sequential linear sensor');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::LIGHT_SOURCE:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 0:
                        return Pel::tra('Unknown');
                    case 1:
                        return Pel::tra('Daylight');
                    case 2:
                        return Pel::tra('Fluorescent');
                    case 3:
                        return Pel::tra('Tungsten (incandescent light)');
                    case 4:
                        return Pel::tra('Flash');
                    case 9:
                        return Pel::tra('Fine weather');
                    case 10:
                        return Pel::tra('Cloudy weather');
                    case 11:
                        return Pel::tra('Shade');
                    case 12:
                        return Pel::tra('Daylight fluorescent');
                    case 13:
                        return Pel::tra('Day white fluorescent');
                    case 14:
                        return Pel::tra('Cool white fluorescent');
                    case 15:
                        return Pel::tra('White fluorescent');
                    case 17:
                        return Pel::tra('Standard light A');
                    case 18:
                        return Pel::tra('Standard light B');
                    case 19:
                        return Pel::tra('Standard light C');
                    case 20:
                        return Pel::tra('D55');
                    case 21:
                        return Pel::tra('D65');
                    case 22:
                        return Pel::tra('D75');
                    case 24:
                        return Pel::tra('ISO studio tungsten');
                    case 255:
                        return Pel::tra('Other');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::FOCAL_PLANE_RESOLUTION_UNIT:
            case PelTag::RESOLUTION_UNIT:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 2:
                        return Pel::tra('Inch');
                    case 3:
                        return Pel::tra('Centimeter');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::EXPOSURE_PROGRAM:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 0:
                        return Pel::tra('Not defined');
                    case 1:
                        return Pel::tra('Manual');
                    case 2:
                        return Pel::tra('Normal program');
                    case 3:
                        return Pel::tra('Aperture priority');
                    case 4:
                        return Pel::tra('Shutter priority');
                    case 5:
                        return Pel::tra('Creative program (biased toward depth of field)');
                    case 6:
                        return Pel::tra('Action program (biased toward fast shutter speed)');
                    case 7:
                        return Pel::tra('Portrait mode (for closeup photos with the background out of focus');
                    case 8:
                        return Pel::tra('Landscape mode (for landscape photos with the background in focus');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::ORIENTATION:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 1:
                        return Pel::tra('top - left');
                    case 2:
                        return Pel::tra('top - right');
                    case 3:
                        return Pel::tra('bottom - right');
                    case 4:
                        return Pel::tra('bottom - left');
                    case 5:
                        return Pel::tra('left - top');
                    case 6:
                        return Pel::tra('right - top');
                    case 7:
                        return Pel::tra('right - bottom');
                    case 8:
                        return Pel::tra('left - bottom');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::YCBCR_POSITIONING:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 1:
                        return Pel::tra('centered');
                    case 2:
                        return Pel::tra('co-sited');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::YCBCR_SUB_SAMPLING:
                // CC (e->components, 2, v);
                if ($this->value[0] == 2 && $this->value[1] == 1) {
                    return 'YCbCr4:2:2';
                }
                if ($this->value[0] == 2 && $this->value[1] == 2) {
                    return 'YCbCr4:2:0';
                }

                return $this->value[0] . ', ' . $this->value[1];
                break;
            case PelTag::PHOTOMETRIC_INTERPRETATION:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 2:
                        return 'RGB';
                    case 6:
                        return 'YCbCr';
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::COLOR_SPACE:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 1:
                        return 'sRGB';
                    case 2:
                        return 'Adobe RGB';
                    case 0xffff:
                        return Pel::tra('Uncalibrated');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::FLASH:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 0x0000:
                        return Pel::tra('Flash did not fire.');
                    case 0x0001:
                        return Pel::tra('Flash fired.');
                    case 0x0005:
                        return Pel::tra('Strobe return light not detected.');
                    case 0x0007:
                        return Pel::tra('Strobe return light detected.');
                    case 0x0009:
                        return Pel::tra('Flash fired, compulsory flash mode.');
                    case 0x000d:
                        return Pel::tra('Flash fired, compulsory flash mode, return light not detected.');
                    case 0x000f:
                        return Pel::tra('Flash fired, compulsory flash mode, return light detected.');
                    case 0x0010:
                        return Pel::tra('Flash did not fire, compulsory flash mode.');
                    case 0x0018:
                        return Pel::tra('Flash did not fire, auto mode.');
                    case 0x0019:
                        return Pel::tra('Flash fired, auto mode.');
                    case 0x001d:
                        return Pel::tra('Flash fired, auto mode, return light not detected.');
                    case 0x001f:
                        return Pel::tra('Flash fired, auto mode, return light detected.');
                    case 0x0020:
                        return Pel::tra('No flash function.');
                    case 0x0041:
                        return Pel::tra('Flash fired, red-eye reduction mode.');
                    case 0x0045:
                        return Pel::tra('Flash fired, red-eye reduction mode, return light not detected.');
                    case 0x0047:
                        return Pel::tra('Flash fired, red-eye reduction mode, return light detected.');
                    case 0x0049:
                        return Pel::tra('Flash fired, compulsory flash mode, red-eye reduction mode.');
                    case 0x004d:
                        return Pel::tra('Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected.');
                    case 0x004f:
                        return Pel::tra('Flash fired, compulsory flash mode, red-eye reduction mode, return light detected.');
                    case 0x0058:
                        return Pel::tra('Flash did not fire, auto mode, red-eye reduction mode.');
                    case 0x0059:
                        return Pel::tra('Flash fired, auto mode, red-eye reduction mode.');
                    case 0x005d:
                        return Pel::tra('Flash fired, auto mode, return light not detected, red-eye reduction mode.');
                    case 0x005f:
                        return Pel::tra('Flash fired, auto mode, return light detected, red-eye reduction mode.');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::CUSTOM_RENDERED:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 0:
                        return Pel::tra('Normal process');
                    case 1:
                        return Pel::tra('Custom process');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::EXPOSURE_MODE:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 0:
                        return Pel::tra('Auto exposure');
                    case 1:
                        return Pel::tra('Manual exposure');
                    case 2:
                        return Pel::tra('Auto bracket');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::WHITE_BALANCE:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 0:
                        return Pel::tra('Auto white balance');
                    case 1:
                        return Pel::tra('Manual white balance');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::SCENE_CAPTURE_TYPE:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 0:
                        return Pel::tra('Standard');
                    case 1:
                        return Pel::tra('Landscape');
                    case 2:
                        return Pel::tra('Portrait');
                    case 3:
                        return Pel::tra('Night scene');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::GAIN_CONTROL:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 0:
                        return Pel::tra('Normal');
                    case 1:
                        return Pel::tra('Low gain up');
                    case 2:
                        return Pel::tra('High gain up');
                    case 3:
                        return Pel::tra('Low gain down');
                    case 4:
                        return Pel::tra('High gain down');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::SATURATION:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 0:
                        return Pel::tra('Normal');
                    case 1:
                        return Pel::tra('Low saturation');
                    case 2:
                        return Pel::tra('High saturation');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::CONTRAST:
            case PelTag::SHARPNESS:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 0:
                        return Pel::tra('Normal');
                    case 1:
                        return Pel::tra('Soft');
                    case 2:
                        return Pel::tra('Hard');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::SUBJECT_DISTANCE_RANGE:
                // CC (e->components, 1, v);
                switch ($this->value[0]) {
                    case 0:
                        return Pel::tra('Unknown');
                    case 1:
                        return Pel::tra('Macro');
                    case 2:
                        return Pel::tra('Close view');
                    case 3:
                        return Pel::tra('Distant view');
                    default:
                        return $this->value[0];
                }
                break;
            case PelTag::SUBJECT_AREA:
                switch ($this->components) {
                    case 2:
                        return Pel::fmt('(x,y) = (%d,%d)', $this->value[0], $this->value[1]);
                    case 3:
                        return Pel::fmt('Within distance %d of (x,y) = (%d,%d)', $this->value[0], $this->value[1], $this->value[2]);
                    case 4:
                        return Pel::fmt('Within rectangle (width %d, height %d) around (x,y) = (%d,%d)', $this->value[0], $this->value[1], $this->value[2], $this->value[3]);

                    default:
                        return Pel::fmt('Unexpected number of components (%d, expected 2, 3, or 4).', $this->components);
                }
                break;
            default:
                return parent::getText($brief);
        }
    }
}
