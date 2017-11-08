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
     * Namespace for functions operating on Exif tags.
     *
     * @author Martin Geisler <mgeisler@users.sourceforge.net>
     * @license http://www.gnu.org/licenses/gpl.html GNU General Public
     *          License (GPL)
     * @package PEL
     */

/**
 * Class with static methods for Exif tags.
 *
 * This class defines the constants that represents the Exif tags
 * known to PEL. They are supposed to be used whenever one needs to
 * specify an Exif tag, and they will be denoted by the pseudo-type
 * {@link PelTag} throughout the documentation.
 *
 * Please note that the constrains on the format and number of
 * components given here are advisory only. To follow the Exif
 * specification one should obey them, but there is nothing that
 * prevents you from creating an {@link IMAGE_LENGTH} entry with two
 * or more components, even though the standard says that there should
 * be exactly one component.
 *
 * All the methods in this class are static and should be called with
 * the Exif tag on which they should operate.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 */
class PelTag
{

    /**
     * Interoperability index.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: 4.
     */
    const INTEROPERABILITY_INDEX = 0x0001;

    /**
     * Interoperability version.
     *
     * Format: {@link PelFormat::UNDEFINED}.
     *
     * Components: 4.
     */
    const INTEROPERABILITY_VERSION = 0x0002;

    /**
     * Image width.
     *
     * Format: {@link PelFormat::SHORT} or {@link PelFormat::LONG}.
     *
     * Components: 1.
     */
    const IMAGE_WIDTH = 0x0100;

    /**
     * Image length.
     *
     * Format: {@link PelFormat::SHORT} or {@link PelFormat::LONG}.
     *
     * Components: 1.
     */
    const IMAGE_LENGTH = 0x0101;

    /**
     * Number of bits per component.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 3.
     */
    const BITS_PER_SAMPLE = 0x0102;

    /**
     * Compression scheme.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const COMPRESSION = 0x0103;

    /**
     * Pixel composition.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const PHOTOMETRIC_INTERPRETATION = 0x0106;

    /**
     * Fill Order
     *
     * Format: Unknown.
     *
     * Components: Unknown.
     */
    const FILL_ORDER = 0x010A;

    /**
     * Document Name
     *
     * Format: {@link PelEntryAscii}.
     *
     * Components: any number.
     */
    const DOCUMENT_NAME = 0x010D;

    /**
     * Image Description
     *
     * Format: {@link PelEntryAscii}.
     *
     * Components: any number.
     */
    const IMAGE_DESCRIPTION = 0x010E;

    /**
     * Manufacturer
     *
     * Format: {@link PelEntryAscii}.
     *
     * Components: any number.
     */
    const MAKE = 0x010F;

    /**
     * Model
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: any number.
     */
    const MODEL = 0x0110;

    /**
     * Strip Offsets
     *
     * Format: {@link PelFormat::SHORT} or {@link PelFormat::LONG}.
     *
     * Components: any number.
     */
    const STRIP_OFFSETS = 0x0111;

    /**
     * Orientation of image.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const ORIENTATION = 0x0112;

    /**
     * Number of components.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const SAMPLES_PER_PIXEL = 0x0115;

    /**
     * Rows per Strip
     *
     * Format: {@link PelFormat::SHORT} or {@link PelFormat::LONG}.
     *
     * Components: 1.
     */
    const ROWS_PER_STRIP = 0x0116;

    /**
     * Strip Byte Count
     *
     * Format: {@link PelFormat::SHORT} or {@link PelFormat::LONG}.
     *
     * Components: any number.
     */
    const STRIP_BYTE_COUNTS = 0x0117;

    /**
     * Image resolution in width direction.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const X_RESOLUTION = 0x011A;

    /**
     * Image resolution in height direction.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const Y_RESOLUTION = 0x011B;

    /**
     * Image data arrangement.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const PLANAR_CONFIGURATION = 0x011C;

    /**
     * Unit of X and Y resolution.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const RESOLUTION_UNIT = 0x0128;

    /**
     * Transfer function.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 3.
     */
    const TRANSFER_FUNCTION = 0x012D;

    /**
     * Software used.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: any number.
     */
    const SOFTWARE = 0x0131;

    /**
     * File change date and time.
     *
     * Format: {@link PelFormat::ASCII}, modelled by the {@link
     * PelEntryTime} class.
     *
     * Components: 20.
     */
    const DATE_TIME = 0x0132;

    /**
     * Person who created the image.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: any number.
     */
    const ARTIST = 0x013B;

    /**
     * White point chromaticity.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 2.
     */
    const WHITE_POINT = 0x013E;

    /**
     * Chromaticities of primaries.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 6.
     */
    const PRIMARY_CHROMATICITIES = 0x013F;

    /**
     * Transfer Range
     *
     * Format: Unknown.
     *
     * Components: Unknown.
     */
    const TRANSFER_RANGE = 0x0156;

    /**
     * JPEGProc
     *
     * Format: Unknown.
     *
     * Components: Unknown.
     */
    const JPEG_PROC = 0x0200;

    /**
     * Offset to JPEG SOI.
     *
     * Format: {@link PelFormat::LONG}.
     *
     * Components: 1.
     */
    const JPEG_INTERCHANGE_FORMAT = 0x0201;

    /**
     * Bytes of JPEG data.
     *
     * Format: {@link PelFormat::LONG}.
     *
     * Components: 1.
     */
    const JPEG_INTERCHANGE_FORMAT_LENGTH = 0x0202;

    /**
     * Color space transformation matrix coefficients.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 3.
     */
    const YCBCR_COEFFICIENTS = 0x0211;

    /**
     * Subsampling ratio of Y to C.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 2.
     */
    const YCBCR_SUB_SAMPLING = 0x0212;

    /**
     * Y and C positioning.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const YCBCR_POSITIONING = 0x0213;

    /**
     * Pair of black and white reference values.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 6.
     */
    const REFERENCE_BLACK_WHITE = 0x0214;

    /**
     * Related Image File Format
     *
     * Format: Unknown.
     *
     * Components: Unknown.
     */
    const RELATED_IMAGE_FILE_FORMAT = 0x1000;

    /**
     * Related Image Width
     *
     * Format: Unknown, probably {@link PelFormat::SHORT}?
     *
     * Components: Unknown, probably 1.
     */
    const RELATED_IMAGE_WIDTH = 0x1001;

    /**
     * Related Image Length
     *
     * Format: Unknown, probably {@link PelFormat::SHORT}?
     *
     * Components: Unknown, probably 1.
     */
    const RELATED_IMAGE_LENGTH = 0x1002;

    /**
     * Rating
     *
     * Format: {@link PelFormat::SHORT}
     *
     * Components: 1.
    */
    const RATING = 0x4746;

    /**
     * Rating percent
     *
     * Format: {@link PelFormat::SHORT}
     *
     * Components: 1.
     */
    const RATING_PERCENT = 0x4749;

    /**
     * CFA Repeat Pattern Dim.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 2.
     */
    const CFA_REPEAT_PATTERN_DIM = 0x828D;

    /**
     * Battery level.
     *
     * Format: Unknown.
     *
     * Components: Unknown.
     */
    const BATTERY_LEVEL = 0x828F;

    /**
     * Copyright holder.
     *
     * Format: {@link PelFormat::ASCII}, modelled by the {@link
     * PelEntryCopyright} class.
     *
     * Components: any number.
     */
    const COPYRIGHT = 0x8298;

    /**
     * Exposure Time
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const EXPOSURE_TIME = 0x829A;

    /**
     * FNumber
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const FNUMBER = 0x829D;

    /**
     * IPTC/NAA
     *
     * Format: {@link PelFormat::LONG}.
     *
     * Components: any number.
     */
    const IPTC_NAA = 0x83BB;

    /**
     * Exif IFD Pointer
     *
     * Format: {@link PelFormat::LONG}.
     *
     * Components: 1.
     */
    const EXIF_IFD_POINTER = 0x8769;

    /**
     * Inter Color Profile
     *
     * Format: {@link PelFormat::UNDEFINED}.
     *
     * Components: any number.
     */
    const INTER_COLOR_PROFILE = 0x8773;

    /**
     * Exposure Program
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const EXPOSURE_PROGRAM = 0x8822;

    /**
     * Spectral Sensitivity
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: any number.
     */
    const SPECTRAL_SENSITIVITY = 0x8824;

    /**
     * GPS Info IFD Pointer
     *
     * Format: {@link PelFormat::LONG}.
     *
     * Components: 1.
     */
    const GPS_INFO_IFD_POINTER = 0x8825;

    /**
     * ISO Speed Ratings
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 2.
     */
    const ISO_SPEED_RATINGS = 0x8827;

    /**
     * OECF
     *
     * Format: {@link PelFormat::UNDEFINED}.
     *
     * Components: any number.
     */
    const OECF = 0x8828;

    /**
     * Exif version.
     *
     * Format: {@link PelFormat::UNDEFINED}, modelled by the {@link
     * PelEntryVersion} class.
     *
     * Components: 4.
     */
    const EXIF_VERSION = 0x9000;

    /**
     * Date and time of original data generation.
     *
     * Format: {@link PelFormat::ASCII}, modelled by the {@link
     * PelEntryTime} class.
     *
     * Components: 20.
     */
    const DATE_TIME_ORIGINAL = 0x9003;

    /**
     * Date and time of digital data generation.
     *
     * Format: {@link PelFormat::ASCII}, modelled by the {@link
     * PelEntryTime} class.
     *
     * Components: 20.
     */
    const DATE_TIME_DIGITIZED = 0x9004;

    /**
     * Offset time (timezone) of file change time.
     *
     * Format: {@link PelFormat::ASCII}
     *
     * Components: 7.
     */
    const OFFSET_TIME = 0x9010;

    /**
     * Offset time (timezone) of original data generation.
     *
     * Format: {@link PelFormat::ASCII}
     *
     * Components: 7.
     */
    const OFFSET_TIME_ORIGINAL = 0x9011;

    /**
     * Offset time (timezone) of digital data generation.
     *
     * Format: {@link PelFormat::ASCII}
     *
     * Components: 7.
     */
    const OFFSET_TIME_DIGITIZED = 0x9012;

    /**
     * Meaning of each component.
     *
     * Format: {@link PelFormat::UNDEFINED}.
     *
     * Components: 4.
     */
    const COMPONENTS_CONFIGURATION = 0x9101;

    /**
     * Image compression mode.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const COMPRESSED_BITS_PER_PIXEL = 0x9102;

    /**
     * Shutter speed
     *
     * Format: {@link PelFormat::SRATIONAL}.
     *
     * Components: 1.
     */
    const SHUTTER_SPEED_VALUE = 0x9201;

    /**
     * Aperture
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const APERTURE_VALUE = 0x9202;

    /**
     * Brightness
     *
     * Format: {@link PelFormat::SRATIONAL}.
     *
     * Components: 1.
     */
    const BRIGHTNESS_VALUE = 0x9203;

    /**
     * Exposure Bias
     *
     * Format: {@link PelFormat::SRATIONAL}.
     *
     * Components: 1.
     */
    const EXPOSURE_BIAS_VALUE = 0x9204;

    /**
     * Max Aperture Value
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const MAX_APERTURE_VALUE = 0x9205;

    /**
     * Subject Distance
     *
     * Format: {@link PelFormat::SRATIONAL}.
     *
     * Components: 1.
     */
    const SUBJECT_DISTANCE = 0x9206;

    /**
     * Metering Mode
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const METERING_MODE = 0x9207;

    /**
     * Light Source
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const LIGHT_SOURCE = 0x9208;

    /**
     * Flash
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const FLASH = 0x9209;

    /**
     * Focal Length
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const FOCAL_LENGTH = 0x920A;

    /**
     * Subject Area
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 4.
     */
    const SUBJECT_AREA = 0x9214;

    /**
     * Maker Note
     *
     * Format: {@link PelFormat::UNDEFINED}.
     *
     * Components: any number.
     */
    const MAKER_NOTE = 0x927C;

    /**
     * User Comment
     *
     * Format: {@link PelFormat::UNDEFINED}, modelled by the {@link
     * PelEntryUserComment} class.
     *
     * Components: any number.
     */
    const USER_COMMENT = 0x9286;

    /**
     * SubSec Time
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: any number.
     */
    const SUB_SEC_TIME = 0x9290;

    /**
     * SubSec Time Original
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: any number.
     */
    const SUB_SEC_TIME_ORIGINAL = 0x9291;

    /**
     * SubSec Time Digitized
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: any number.
     */
    const SUB_SEC_TIME_DIGITIZED = 0x9292;

    /**
     * Windows XP Title
     *
     * Format: {@link PelFormat::BYTE}, modelled by the
     * {@link PelEntryWindowsString} class.
     *
     * Components: any number.
     */
    const XP_TITLE = 0x9C9B;

    /**
     * Windows XP Comment
     *
     * Format: {@link PelFormat::BYTE}, modelled by the
     * {@link PelEntryWindowsString} class.
     *
     * Components: any number.
     */
    const XP_COMMENT = 0x9C9C;

    /**
     * Windows XP Author
     *
     * Format: {@link PelFormat::BYTE}, modelled by the
     * {@link PelEntryWindowsString} class.
     *
     * Components: any number.
     */
    const XP_AUTHOR = 0x9C9D;

    /**
     * Windows XP Keywords
     *
     * Format: {@link PelFormat::BYTE}, modelled by the
     * {@link PelEntryWindowsString} class.
     *
     * Components: any number.
     */
    const XP_KEYWORDS = 0x9C9E;

    /**
     * Windows XP Subject
     *
     * Format: {@link PelFormat::BYTE}, modelled by the
     * {@link PelEntryWindowsString} class.
     *
     * Components: any number.
     */
    const XP_SUBJECT = 0x9C9F;

    /**
     * Supported Flashpix version
     *
     * Format: {@link PelFormat::UNDEFINED}, modelled by the {@link
     * PelEntryVersion} class.
     *
     * Components: 4.
     */
    const FLASH_PIX_VERSION = 0xA000;

    /**
     * Color space information.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const COLOR_SPACE = 0xA001;

    /**
     * Valid image width.
     *
     * Format: {@link PelFormat::SHORT} or {@link PelFormat::LONG}.
     *
     * Components: 1.
     */
    const PIXEL_X_DIMENSION = 0xA002;

    /**
     * Valid image height.
     *
     * Format: {@link PelFormat::SHORT} or {@link PelFormat::LONG}.
     *
     * Components: 1.
     */
    const PIXEL_Y_DIMENSION = 0xA003;

    /**
     * Related audio file.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: any number.
     */
    const RELATED_SOUND_FILE = 0xA004;

    /**
     * Interoperability IFD Pointer
     *
     * Format: {@link PelFormat::LONG}.
     *
     * Components: 1.
     */
    const INTEROPERABILITY_IFD_POINTER = 0xA005;

    /**
     * Flash energy.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const FLASH_ENERGY = 0xA20B;

    /**
     * Spatial frequency response.
     *
     * Format: {@link PelFormat::UNDEFINED}.
     *
     * Components: any number.
     */
    const SPATIAL_FREQUENCY_RESPONSE = 0xA20C;

    /**
     * Focal plane X resolution.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const FOCAL_PLANE_X_RESOLUTION = 0xA20E;

    /**
     * Focal plane Y resolution.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const FOCAL_PLANE_Y_RESOLUTION = 0xA20F;

    /**
     * Focal plane resolution unit.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const FOCAL_PLANE_RESOLUTION_UNIT = 0xA210;

    /**
     * Subject location.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const SUBJECT_LOCATION = 0xA214;

    /**
     * Exposure index.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const EXPOSURE_INDEX = 0xA215;

    /**
     * Sensing method.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const SENSING_METHOD = 0xA217;

    /**
     * File source.
     *
     * Format: {@link PelFormat::UNDEFINED}.
     *
     * Components: 1.
     */
    const FILE_SOURCE = 0xA300;

    /**
     * Scene type.
     *
     * Format: {@link PelFormat::UNDEFINED}.
     *
     * Components: 1.
     */
    const SCENE_TYPE = 0xA301;

    /**
     * CFA pattern.
     *
     * Format: {@link PelFormat::UNDEFINED}.
     *
     * Components: any number.
     */
    const CFA_PATTERN = 0xA302;

    /**
     * Custom image processing.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const CUSTOM_RENDERED = 0xA401;

    /**
     * Exposure mode.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const EXPOSURE_MODE = 0xA402;

    /**
     * White balance.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const WHITE_BALANCE = 0xA403;

    /**
     * Digital zoom ratio.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const DIGITAL_ZOOM_RATIO = 0xA404;

    /**
     * Focal length in 35mm film.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const FOCAL_LENGTH_IN_35MM_FILM = 0xA405;

    /**
     * Scene capture type.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const SCENE_CAPTURE_TYPE = 0xA406;

    /**
     * Gain control.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const GAIN_CONTROL = 0xA407;

    /**
     * Contrast.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const CONTRAST = 0xA408;

    /**
     * Saturation.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const SATURATION = 0xA409;

    /**
     * Sharpness.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const SHARPNESS = 0xA40A;

    /**
     * Device settings description.
     *
     * This tag indicates information on the picture-taking conditions
     * of a particular camera model. The tag is used only to indicate
     * the picture-taking conditions in the reader.
     */
    const DEVICE_SETTING_DESCRIPTION = 0xA40B;

    /**
     * Subject distance range.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const SUBJECT_DISTANCE_RANGE = 0xA40C;

    /**
     * Image unique ID.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: 32.
     */
    const IMAGE_UNIQUE_ID = 0xA420;

    /**
     * Gamma.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const GAMMA = 0xA500;

    /**
     * PrintIM
     *
     * Format: {@link PelFormat::UNDEFINED}.
     *
     * Components: unknown.
     */
    const PRINT_IM = 0xC4A5;

    /**
     * GPS tag version.
     *
     * Format: {@link PelFormat::BYTE}.
     *
     * Components: 4.
     */
    const GPS_VERSION_ID = 0x0000;

    /**
     * North or South Latitude.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: 2.
     */
    const GPS_LATITUDE_REF = 0x0001;

    /**
     * Latitude.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 3.
     */
    const GPS_LATITUDE = 0x0002;

    /**
     * East or West Longitude.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: 2.
     */
    const GPS_LONGITUDE_REF = 0x0003;

    /**
     * Longitude.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 3.
     */
    const GPS_LONGITUDE = 0x0004;

    /**
     * Altitude reference.
     *
     * Format: {@link PelFormat::BYTE}.
     *
     * Components: 1.
     */
    const GPS_ALTITUDE_REF = 0x0005;

    /**
     * Altitude.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const GPS_ALTITUDE = 0x0006;

    /**
     * GPS time (atomic clock).
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 3.
     */
    const GPS_TIME_STAMP = 0x0007;

    /**
     * GPS satellites used for measurement.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: Any.
     */
    const GPS_SATELLITES = 0x0008;

    /**
     * GPS receiver status.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: 2.
     */
    const GPS_STATUS = 0x0009;

    /**
     * GPS measurement mode.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: 2.
     */
    const GPS_MEASURE_MODE = 0x000A;

    /**
     * Measurement precision.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const GPS_DOP = 0x000B;

    /**
     * Speed unit.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: 2.
     */
    const GPS_SPEED_REF = 0x000C;

    /**
     * Speed of GPS receiver.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const GPS_SPEED = 0x000D;

    /**
     * Reference for direction of movement.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: 2.
     */
    const GPS_TRACK_REF = 0x000E;

    /**
     * Direction of movement.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const GPS_TRACK = 0x000F;

    /**
     * Reference for direction of image.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: 2.
     */
    const GPS_IMG_DIRECTION_REF = 0x0010;

    /**
     * Direction of image.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const GPS_IMG_DIRECTION = 0x0011;

    /**
     * Geodetic survey data used.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: Any.
     */
    const GPS_MAP_DATUM = 0x0012;

    /**
     * Reference for latitude of destination.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: 2.
     */
    const GPS_DEST_LATITUDE_REF = 0x0013;

    /**
     * Latitude of destination.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 3.
     */
    const GPS_DEST_LATITUDE = 0x0014;

    /**
     * Reference for longitude of destination.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: 2.
     */
    const GPS_DEST_LONGITUDE_REF = 0x0015;

    /**
     * Longitude of destination.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 3.
     */
    const GPS_DEST_LONGITUDE = 0x0016;

    /**
     * Reference for bearing of destination.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: 2.
     */
    const GPS_DEST_BEARING_REF = 0x0017;

    /**
     * Bearing of destination.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const GPS_DEST_BEARING = 0x0018;

    /**
     * Reference for distance to destination.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: 2.
     */
    const GPS_DEST_DISTANCE_REF = 0x0019;

    /**
     * Distance to destination.
     *
     * Format: {@link PelFormat::RATIONAL}.
     *
     * Components: 1.
     */
    const GPS_DEST_DISTANCE = 0x001A;

    /**
     * Name of GPS processing method.
     *
     * Format: {@link PelFormat::UNDEFINED}.
     *
     * Components: Any.
     */
    const GPS_PROCESSING_METHOD = 0x001B;

    /**
     * Name of GPS area.
     *
     * Format: {@link PelFormat::UNDEFINED}.
     *
     * Components: Any.
     */
    const GPS_AREA_INFORMATION = 0x001C;

    /**
     * GPS date.
     *
     * Format: {@link PelFormat::ASCII}.
     *
     * Components: 11.
     */
    const GPS_DATE_STAMP = 0x001D;

    /**
     * GPS differential correction.
     *
     * Format: {@link PelFormat::SHORT}.
     *
     * Components: 1.
     */
    const GPS_DIFFERENTIAL = 0x001E;

    /**
     * Values for tags short names.
     */
    protected static $exifTagsShort= array(
        self::INTEROPERABILITY_INDEX         => 'InteroperabilityIndex',
        self::INTEROPERABILITY_VERSION       => 'InteroperabilityVersion',
        self::IMAGE_WIDTH                    => 'ImageWidth',
        self::IMAGE_LENGTH                   => 'ImageLength',
        self::BITS_PER_SAMPLE                => 'BitsPerSample',
        self::COMPRESSION                    => 'Compression',
        self::PHOTOMETRIC_INTERPRETATION     => 'PhotometricInterpretation',
        self::FILL_ORDER                     => 'FillOrder',
        self::DOCUMENT_NAME                  => 'DocumentName',
        self::IMAGE_DESCRIPTION              => 'ImageDescription',
        self::MAKE                           => 'Make',
        self::MODEL                          => 'Model',
        self::STRIP_OFFSETS                  => 'StripOffsets',
        self::ORIENTATION                    => 'Orientation',
        self::SAMPLES_PER_PIXEL              => 'SamplesPerPixel',
        self::ROWS_PER_STRIP                 => 'RowsPerStrip',
        self::STRIP_BYTE_COUNTS              => 'StripByteCounts',
        self::X_RESOLUTION                   => 'XResolution',
        self::Y_RESOLUTION                   => 'YResolution',
        self::PLANAR_CONFIGURATION           => 'PlanarConfiguration',
        self::RESOLUTION_UNIT                => 'ResolutionUnit',
        self::TRANSFER_FUNCTION              => 'TransferFunction',
        self::SOFTWARE                       => 'Software',
        self::DATE_TIME                      => 'DateTime',
        self::ARTIST                         => 'Artist',
        self::WHITE_POINT                    => 'WhitePoint',
        self::PRIMARY_CHROMATICITIES         => 'PrimaryChromaticities',
        self::TRANSFER_RANGE                 => 'TransferRange',
        self::JPEG_PROC                      => 'JPEGProc',
        self::JPEG_INTERCHANGE_FORMAT        => 'JPEGInterchangeFormat',
        self::JPEG_INTERCHANGE_FORMAT_LENGTH => 'JPEGInterchangeFormatLength',
        self::YCBCR_COEFFICIENTS             => 'YCbCrCoefficients',
        self::YCBCR_SUB_SAMPLING             => 'YCbCrSubSampling',
        self::YCBCR_POSITIONING              => 'YCbCrPositioning',
        self::REFERENCE_BLACK_WHITE          => 'ReferenceBlackWhite',
        self::RELATED_IMAGE_FILE_FORMAT      => 'RelatedImageFileFormat',
        self::RELATED_IMAGE_WIDTH            => 'RelatedImageWidth',
        self::RELATED_IMAGE_LENGTH           => 'RelatedImageLength',
        self::RATING                         => 'Rating',
        self::RATING_PERCENT                 => 'RatingPercent',
        self::CFA_REPEAT_PATTERN_DIM         => 'CFARepeatPatternDim',
        self::CFA_PATTERN                    => 'CFAPattern',
        self::BATTERY_LEVEL                  => 'BatteryLevel',
        self::COPYRIGHT                      => 'Copyright',
        self::EXPOSURE_TIME                  => 'ExposureTime',
        self::FNUMBER                        => 'FNumber',
        self::IPTC_NAA                       => 'IPTC/NAA',
        self::EXIF_IFD_POINTER               => 'ExifIFDPointer',
        self::INTER_COLOR_PROFILE            => 'InterColorProfile',
        self::EXPOSURE_PROGRAM               => 'ExposureProgram',
        self::SPECTRAL_SENSITIVITY           => 'SpectralSensitivity',
        self::GPS_INFO_IFD_POINTER           => 'GPSInfoIFDPointer',
        self::ISO_SPEED_RATINGS              => 'ISOSpeedRatings',
        self::OECF                           => 'OECF',
        self::EXIF_VERSION                   => 'ExifVersion',
        self::DATE_TIME_ORIGINAL             => 'DateTimeOriginal',
        self::DATE_TIME_DIGITIZED            => 'DateTimeDigitized',
        self::OFFSET_TIME                    => 'OffsetTime',
        self::OFFSET_TIME_ORIGINAL           => 'OffsetTimeOriginal',
        self::OFFSET_TIME_DIGITIZED          => 'OffsetTimeDigitized',
        self::COMPONENTS_CONFIGURATION       => 'ComponentsConfiguration',
        self::COMPRESSED_BITS_PER_PIXEL      => 'CompressedBitsPerPixel',
        self::SHUTTER_SPEED_VALUE            => 'ShutterSpeedValue',
        self::APERTURE_VALUE                 => 'ApertureValue',
        self::BRIGHTNESS_VALUE               => 'BrightnessValue',
        self::EXPOSURE_BIAS_VALUE            => 'ExposureBiasValue',
        self::MAX_APERTURE_VALUE             => 'MaxApertureValue',
        self::SUBJECT_DISTANCE               => 'SubjectDistance',
        self::METERING_MODE                  => 'MeteringMode',
        self::LIGHT_SOURCE                   => 'LightSource',
        self::FLASH                          => 'Flash',
        self::FOCAL_LENGTH                   => 'FocalLength',
        self::MAKER_NOTE                     => 'MakerNote',
        self::USER_COMMENT                   => 'UserComment',
        self::SUB_SEC_TIME                   => 'SubSecTime',
        self::SUB_SEC_TIME_ORIGINAL          => 'SubSecTimeOriginal',
        self::SUB_SEC_TIME_DIGITIZED         => 'SubSecTimeDigitized',
        self::XP_TITLE                       => 'WindowsXPTitle',
        self::XP_COMMENT                     => 'WindowsXPComment',
        self::XP_AUTHOR                      => 'WindowsXPAuthor',
        self::XP_KEYWORDS                    => 'WindowsXPKeywords',
        self::XP_SUBJECT                     => 'WindowsXPSubject',
        self::FLASH_PIX_VERSION              => 'FlashPixVersion',
        self::COLOR_SPACE                    => 'ColorSpace',
        self::PIXEL_X_DIMENSION              => 'PixelXDimension',
        self::PIXEL_Y_DIMENSION              => 'PixelYDimension',
        self::RELATED_SOUND_FILE             => 'RelatedSoundFile',
        self::INTEROPERABILITY_IFD_POINTER   => 'InteroperabilityIFDPointer',
        self::FLASH_ENERGY                   => 'FlashEnergy',
        self::SPATIAL_FREQUENCY_RESPONSE     => 'SpatialFrequencyResponse',
        self::FOCAL_PLANE_X_RESOLUTION       => 'FocalPlaneXResolution',
        self::FOCAL_PLANE_Y_RESOLUTION       => 'FocalPlaneYResolution',
        self::FOCAL_PLANE_RESOLUTION_UNIT    => 'FocalPlaneResolutionUnit',
        self::SUBJECT_LOCATION               => 'SubjectLocation',
        self::EXPOSURE_INDEX                 => 'ExposureIndex',
        self::SENSING_METHOD                 => 'SensingMethod',
        self::FILE_SOURCE                    => 'FileSource',
        self::SCENE_TYPE                     => 'SceneType',
        self::SUBJECT_AREA                   => 'SubjectArea',
        self::CUSTOM_RENDERED                => 'CustomRendered',
        self::EXPOSURE_MODE                  => 'ExposureMode',
        self::WHITE_BALANCE                  => 'WhiteBalance',
        self::DIGITAL_ZOOM_RATIO             => 'DigitalZoomRatio',
        self::FOCAL_LENGTH_IN_35MM_FILM      => 'FocalLengthIn35mmFilm',
        self::SCENE_CAPTURE_TYPE             => 'SceneCaptureType',
        self::GAIN_CONTROL                   => 'GainControl',
        self::CONTRAST                       => 'Contrast',
        self::SATURATION                     => 'Saturation',
        self::SHARPNESS                      => 'Sharpness',
        self::DEVICE_SETTING_DESCRIPTION     => 'DeviceSettingDescription',
        self::SUBJECT_DISTANCE_RANGE         => 'SubjectDistanceRange',
        self::IMAGE_UNIQUE_ID                => 'ImageUniqueID',
        self::GAMMA                          => 'Gamma',
        self::PRINT_IM                       => 'PrintIM',
    );

    /**
     * Values for tags titles.
     */
    protected static $exifTagsTitle = array(
        self::INTEROPERABILITY_INDEX         => 'Interoperability Index',
        self::INTEROPERABILITY_VERSION       => 'Interoperability Version',
        self::IMAGE_WIDTH                    => 'Image Width',
        self::IMAGE_LENGTH                   => 'Image Length',
        self::BITS_PER_SAMPLE                => 'Bits per Sample',
        self::COMPRESSION                    => 'Compression',
        self::PHOTOMETRIC_INTERPRETATION     => 'Photometric Interpretation',
        self::FILL_ORDER                     => 'Fill Order',
        self::DOCUMENT_NAME                  => 'Document Name',
        self::IMAGE_DESCRIPTION              => 'Image Description',
        self::MAKE                           => 'Manufacturer',
        self::MODEL                          => 'Model',
        self::STRIP_OFFSETS                  => 'Strip Offsets',
        self::ORIENTATION                    => 'Orientation',
        self::SAMPLES_PER_PIXEL              => 'Samples per Pixel',
        self::ROWS_PER_STRIP                 => 'Rows per Strip',
        self::STRIP_BYTE_COUNTS              => 'Strip Byte Count',
        self::X_RESOLUTION                   => 'x-Resolution',
        self::Y_RESOLUTION                   => 'y-Resolution',
        self::PLANAR_CONFIGURATION           => 'Planar Configuration',
        self::RESOLUTION_UNIT                => 'Resolution Unit',
        self::TRANSFER_FUNCTION              => 'Transfer Function',
        self::SOFTWARE                       => 'Software',
        self::DATE_TIME                      => 'Date and Time',
        self::ARTIST                         => 'Artist',
        self::WHITE_POINT                    => 'White Point',
        self::PRIMARY_CHROMATICITIES         => 'Primary Chromaticities',
        self::TRANSFER_RANGE                 => 'Transfer Range',
        self::JPEG_PROC                      => 'JPEG Process',
        self::JPEG_INTERCHANGE_FORMAT        => 'JPEG Interchange Format',
        self::JPEG_INTERCHANGE_FORMAT_LENGTH => 'JPEG Interchange Format Length',
        self::YCBCR_COEFFICIENTS             => 'YCbCr Coefficients',
        self::YCBCR_SUB_SAMPLING             => 'YCbCr Sub-Sampling',
        self::YCBCR_POSITIONING              => 'YCbCr Positioning',
        self::REFERENCE_BLACK_WHITE          => 'Reference Black/White',
        self::RELATED_IMAGE_FILE_FORMAT      => 'Related Image File Format',
        self::RELATED_IMAGE_WIDTH            => 'Related Image Width',
        self::RELATED_IMAGE_LENGTH           => 'Related Image Length',
        self::RATING                         => 'Star Rating',
        self::RATING_PERCENT                 => 'Percent Rating',
        self::CFA_REPEAT_PATTERN_DIM         => 'CFA Repeat Pattern Dim',
        self::CFA_PATTERN                    => 'CFA Pattern',
        self::BATTERY_LEVEL                  => 'Battery Level',
        self::COPYRIGHT                      => 'Copyright',
        self::EXPOSURE_TIME                  => 'Exposure Time',
        self::FNUMBER                        => 'FNumber',
        self::IPTC_NAA                       => 'IPTC/NAA',
        self::EXIF_IFD_POINTER               => 'Exif IFD Pointer',
        self::INTER_COLOR_PROFILE            => 'Inter Color Profile',
        self::EXPOSURE_PROGRAM               => 'Exposure Program',
        self::SPECTRAL_SENSITIVITY           => 'Spectral Sensitivity',
        self::GPS_INFO_IFD_POINTER           => 'GPS Info IFD Pointer',
        self::ISO_SPEED_RATINGS              => 'ISO Speed Ratings',
        self::OECF                           => 'OECF',
        self::EXIF_VERSION                   => 'Exif Version',
        self::DATE_TIME_ORIGINAL             => 'Date and Time (original)',
        self::DATE_TIME_DIGITIZED            => 'Date and Time (digitized)',
        self::OFFSET_TIME                    => 'Timezone',
        self::OFFSET_TIME_ORIGINAL           => 'Timezone (original)',
        self::OFFSET_TIME_DIGITIZED          => 'Timezone (digitized)',
        self::COMPONENTS_CONFIGURATION       => 'Components Configuration',
        self::COMPRESSED_BITS_PER_PIXEL      => 'Compressed Bits per Pixel',
        self::SHUTTER_SPEED_VALUE            => 'Shutter speed',
        self::APERTURE_VALUE                 => 'Aperture',
        self::BRIGHTNESS_VALUE               => 'Brightness',
        self::EXPOSURE_BIAS_VALUE            => 'Exposure Bias',
        self::MAX_APERTURE_VALUE             => 'Max Aperture Value',
        self::SUBJECT_DISTANCE               => 'Subject Distance',
        self::METERING_MODE                  => 'Metering Mode',
        self::LIGHT_SOURCE                   => 'Light Source',
        self::FLASH                          => 'Flash',
        self::FOCAL_LENGTH                   => 'Focal Length',
        self::MAKER_NOTE                     => 'Maker Note',
        self::USER_COMMENT                   => 'User Comment',
        self::SUB_SEC_TIME                   => 'SubSec Time',
        self::SUB_SEC_TIME_ORIGINAL          => 'SubSec Time Original',
        self::SUB_SEC_TIME_DIGITIZED         => 'SubSec Time Digitized',
        self::XP_TITLE                       => 'Windows XP Title',
        self::XP_COMMENT                     => 'Windows XP Comment',
        self::XP_AUTHOR                      => 'Windows XP Author',
        self::XP_KEYWORDS                    => 'Windows XP Keywords',
        self::XP_SUBJECT                     => 'Windows XP Subject',
        self::FLASH_PIX_VERSION              => 'FlashPix Version',
        self::COLOR_SPACE                    => 'Color Space',
        self::PIXEL_X_DIMENSION              => 'Pixel x-Dimension',
        self::PIXEL_Y_DIMENSION              => 'Pixel y-Dimension',
        self::RELATED_SOUND_FILE             => 'Related Sound File',
        self::INTEROPERABILITY_IFD_POINTER   => 'Interoperability IFD Pointer',
        self::FLASH_ENERGY                   => 'Flash Energy',
        self::SPATIAL_FREQUENCY_RESPONSE     => 'Spatial Frequency Response',
        self::FOCAL_PLANE_X_RESOLUTION       => 'Focal Plane x-Resolution',
        self::FOCAL_PLANE_Y_RESOLUTION       => 'Focal Plane y-Resolution',
        self::FOCAL_PLANE_RESOLUTION_UNIT    => 'Focal Plane Resolution Unit',
        self::SUBJECT_LOCATION               => 'Subject Location',
        self::EXPOSURE_INDEX                 => 'Exposure index',
        self::SENSING_METHOD                 => 'Sensing Method',
        self::FILE_SOURCE                    => 'File Source',
        self::SCENE_TYPE                     => 'Scene Type',
        self::SUBJECT_AREA                   => 'Subject Area',
        self::CUSTOM_RENDERED                => 'Custom Rendered',
        self::EXPOSURE_MODE                  => 'Exposure Mode',
        self::WHITE_BALANCE                  => 'White Balance',
        self::DIGITAL_ZOOM_RATIO             => 'Digital Zoom Ratio',
        self::FOCAL_LENGTH_IN_35MM_FILM      => 'Focal Length In 35mm Film',
        self::SCENE_CAPTURE_TYPE             => 'Scene Capture Type',
        self::GAIN_CONTROL                   => 'Gain Control',
        self::CONTRAST                       => 'Contrast',
        self::SATURATION                     => 'Saturation',
        self::SHARPNESS                      => 'Sharpness',
        self::DEVICE_SETTING_DESCRIPTION     => 'Device Setting Description',
        self::SUBJECT_DISTANCE_RANGE         => 'Subject Distance Range',
        self::IMAGE_UNIQUE_ID                => 'Image Unique ID',
        self::GAMMA                          => 'Gamma',
        self::PRINT_IM                       => 'Print IM',
    );

    /**
     * Values for gps tags short names.
     */
    protected static $gpsTagsShort = array(
        self::GPS_VERSION_ID         => 'GPSVersionID',
        self::GPS_LATITUDE_REF       => 'GPSLatitudeRef',
        self::GPS_LATITUDE           => 'GPSLatitude',
        self::GPS_LONGITUDE_REF      => 'GPSLongitudeRef',
        self::GPS_LONGITUDE          => 'GPSLongitude',
        self::GPS_ALTITUDE_REF       => 'GPSAltitudeRef',
        self::GPS_ALTITUDE           => 'GPSAltitude',
        self::GPS_TIME_STAMP         => 'GPSTimeStamp',
        self::GPS_SATELLITES         => 'GPSSatellites',
        self::GPS_STATUS             => 'GPSStatus',
        self::GPS_MEASURE_MODE       => 'GPSMeasureMode',
        self::GPS_DOP                => 'GPSDOP',
        self::GPS_SPEED_REF          => 'GPSSpeedRef',
        self::GPS_SPEED              => 'GPSSpeed',
        self::GPS_TRACK_REF          => 'GPSTrackRef',
        self::GPS_TRACK              => 'GPSTrack',
        self::GPS_IMG_DIRECTION_REF  => 'GPSImgDirectionRef',
        self::GPS_IMG_DIRECTION      => 'GPSImgDirection',
        self::GPS_MAP_DATUM          => 'GPSMapDatum',
        self::GPS_DEST_LATITUDE_REF  => 'GPSDestLatitudeRef',
        self::GPS_DEST_LATITUDE      => 'GPSDestLatitude',
        self::GPS_DEST_LONGITUDE_REF => 'GPSDestLongitudeRef',
        self::GPS_DEST_LONGITUDE     => 'GPSDestLongitude',
        self::GPS_DEST_BEARING_REF   => 'GPSDestBearingRef',
        self::GPS_DEST_BEARING       => 'GPSDestBearing',
        self::GPS_DEST_DISTANCE_REF  => 'GPSDestDistanceRef',
        self::GPS_DEST_DISTANCE      => 'GPSDestDistance',
        self::GPS_PROCESSING_METHOD  => 'GPSProcessingMethod',
        self::GPS_AREA_INFORMATION   => 'GPSAreaInformation',
        self::GPS_DATE_STAMP         => 'GPSDateStamp',
        self::GPS_DIFFERENTIAL       => 'GPSDifferential',
    );

    /**
     * Returns a string from container with key $tag and subcontainer index of $idx
     *
     * @param array $container
     *            {@link PelTag::EXIF_TAGS_SHORT}, {@link PelTag::EXIF_TAGS_TITLE},
     *            {@link PelTag::GPS_TAGS_SHORT} or {@link PelTag::GPS_TAGS_TITLE} container.
     * @param int $tag
     *            the tag.
     *
     * @return string short name or long name of the tag.
     */
    public static function getValue($container, $tag)
    {
        if (isset($container[ $tag ])) {
            return $container[ $tag ];
        }

        return self::unknownTag($tag);
    }

    /**
     * Reverse lookup of a tag id by its short name. Return false for the unknown tag name.
     *
     * @deprecated Use getExifTagByName() and getGpsTagByName() to distinct the type of tag.
     *
     * @param string $name
     *            tag short name.
     *
     * @return mixed (bool|int)
     *            the tag.
     */
    public static function getTagByName($name)
    {
        $k = array_search($name, self::$exifTagsShort);
        if ($k !== false) {
            return $k;
        }

        return array_search($name, static::$gpsTagsShort);
    }

    /**
     * Reverse lookup of a EXIF related tag id by its short name. Return false for the unknown tag name.
     *
     * @param string $name
     *            tag short name.
     *
     * @return mixed (bool|int)
     *            the tag.
     */
    public static function getExifTagByName($name)
    {
        return array_search($name, static::$exifTagsShort);
    }

    /**
     * Reverse lookup of a GPS related tag id by its short name. Return false for the unknown tag name.
     *
     * @param string $name
     *            tag short name.
     *
     * @return mixed (bool|int)
     *            the tag.
     */
    public static function getGpsTagByName($name)
    {
        return array_search($name, static::$gpsTagsShort);
    }

    /**
     * Returns string defining unknown tag.
     *
     * @param int $tag
     *            the tag.
     *
     * @return string
     *            description string.
     */
    protected static function unknownTag($tag)
    {
        return Pel::fmt('Unknown: 0x%04X', $tag);
    }

    /**
     * Returns a short name for an Exif tag.
     *
     * @param int $type
     *            the IFD type of the tag, one of {@link PelIfd::IFD0},
     *            {@link PelIfd::IFD1}, {@link PelIfd::EXIF}, {@link PelIfd::GPS},
     *            or {@link PelIfd::INTEROPERABILITY}.
     *
     * @param int $tag
     *            the tag.
     *
     * @return string the short name of the tag, e.g., 'ImageWidth' for
     *         the {@link IMAGE_WIDTH} tag. If the tag is not known, the string
     *         'Unknown:0xTTTT' will be returned where 'TTTT' is the hexadecimal
     *         representation of the tag.
     */
    public static function getName($type, $tag)
    {
        switch ($type) {
            case PelIfd::IFD0:
            case PelIfd::IFD1:
            case PelIfd::EXIF:
            case PelIfd::INTEROPERABILITY:
                return self::getValue(self::$exifTagsShort, $tag);
            case PelIfd::GPS:
                return self::getValue(self::$gpsTagsShort, $tag);
        }

        return self::unknownTag($tag);
    }

    /**
     * Returns a title for an Exif tag.
     *
     * @param int $type
     *            the IFD type of the tag, one of {@link PelIfd::IFD0},
     *            {@link PelIfd::IFD1}, {@link PelIfd::EXIF}, {@link PelIfd::GPS},
     *            or {@link PelIfd::INTEROPERABILITY}.
     *
     * @param int $tag
     *            the tag.
     *
     * @return string the title of the tag, e.g., 'Image Width' for the
     *         {@link IMAGE_WIDTH} tag. If the tag isn't known, the string
     *         'Unknown Tag: 0xTT' will be returned where 'TT' is the
     *         hexadecimal representation of the tag.
     */
    public function getTitle($type, $tag)
    {
        switch ($type) {
            case PelIfd::IFD0:
            case PelIfd::IFD1:
            case PelIfd::EXIF:
            case PelIfd::INTEROPERABILITY:
                return Pel::tra(self::getValue(self::$exifTagsTitle, $tag));
            case PelIfd::GPS:
                return Pel::tra(self::getValue(self::$gpsTagsShort, $tag));
        }

        return self::unknownTag($tag);
    }
}
