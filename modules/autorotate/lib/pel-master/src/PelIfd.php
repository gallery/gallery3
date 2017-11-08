<?php

/**
 * PEL: PHP Exif Library.
 * A library with support for reading and
 * writing all Exif headers in JPEG and TIFF images using PHP.
 *
 * Copyright (C) 2004, 2005, 2006, 2007, 2008 Martin Geisler.
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
 * Classes for dealing with Exif IFDs.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public
 *          License (GPL)
 * @package PEL
 */

/**
 * Class representing an Image File Directory (IFD).
 *
 * {@link PelTiff TIFF data} is structured as a number of Image File
 * Directories, IFDs for short. Each IFD contains a number of {@link
 * PelEntry entries}, some data and finally a link to the next IFD.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 */
class PelIfd implements \IteratorAggregate, \ArrayAccess
{

    /**
     * Main image IFD.
     *
     * Pass this to the constructor when creating an IFD which will be
     * the IFD of the main image.
     */
    const IFD0 = 0;

    /**
     * Thumbnail image IFD.
     *
     * Pass this to the constructor when creating an IFD which will be
     * the IFD of the thumbnail image.
     */
    const IFD1 = 1;

    /**
     * Exif IFD.
     *
     * Pass this to the constructor when creating an IFD which will be
     * the Exif sub-IFD.
     */
    const EXIF = 2;

    /**
     * GPS IFD.
     *
     * Pass this to the constructor when creating an IFD which will be
     * the GPS sub-IFD.
     */
    const GPS = 3;

    /**
     * Interoperability IFD.
     *
     * Pass this to the constructor when creating an IFD which will be
     * the interoperability sub-IFD.
     */
    const INTEROPERABILITY = 4;

    /**
     * The entries held by this directory.
     *
     * Each tag in the directory is represented by a {@link PelEntry}
     * object in this array.
     *
     * @var array
     */
    private $entries = array();

    /**
     * The type of this directory.
     *
     * Initialized in the constructor. Must be one of {@link IFD0},
     * {@link IFD1}, {@link EXIF}, {@link GPS}, or {@link
     * INTEROPERABILITY}.
     *
     * @var int
     */
    private $type;

    /**
     * The next directory.
     *
     * This will be initialized in the constructor, or be left as null
     * if this is the last directory.
     *
     * @var PelIfd
     */
    private $next = null;

    /**
     * Sub-directories pointed to by this directory.
     *
     * This will be an array of ({@link PelTag}, {@link PelIfd}) pairs.
     *
     * @var array
     */
    private $sub = array();

    /**
     * The thumbnail data.
     *
     * This will be initialized in the constructor, or be left as null
     * if there are no thumbnail as part of this directory.
     *
     * @var PelDataWindow
     */
    private $thumb_data = null;
    // TODO: use this format to choose between the
    // JPEG_INTERCHANGE_FORMAT and STRIP_OFFSETS tags.
    // private $thumb_format;

    /**
     * Construct a new Image File Directory (IFD).
     *
     * The IFD will be empty, use the {@link addEntry()} method to add
     * an {@link PelEntry}. Use the {@link setNext()} method to link
     * this IFD to another.
     *
     * @param
     *            int type the type of this IFD. Must be one of {@link
     *            IFD0}, {@link IFD1}, {@link EXIF}, {@link GPS}, or {@link
     *            INTEROPERABILITY}. An {@link PelIfdException} will be thrown
     *            otherwise.
     */
    public function __construct($type)
    {
        if ($type != PelIfd::IFD0 && $type != PelIfd::IFD1 && $type != PelIfd::EXIF && $type != PelIfd::GPS &&
             $type != PelIfd::INTEROPERABILITY) {
            throw new PelIfdException('Unknown IFD type: %d', $type);
        }

        $this->type = $type;
    }

    /**
     * Load data into a Image File Directory (IFD).
     *
     * @param PelDataWindow $d
     *            the data window that will provide the data.
     *
     * @param int $offset
     *            the offset within the window where the directory will
     *            be found.
     */
    public function load(PelDataWindow $d, $offset)
    {
        $thumb_offset = 0;
        $thumb_length = 0;

        Pel::debug('Constructing IFD at offset %d from %d bytes...', $offset, $d->getSize());

        /* Read the number of entries */
        $n = $d->getShort($offset);
        Pel::debug('Loading %d entries...', $n);

        $offset += 2;

        /* Check if we have enough data. */
        if ($offset + 12 * $n > $d->getSize()) {
            $n = floor(($offset - $d->getSize()) / 12);
            Pel::maybeThrow(new PelIfdException('Adjusted to: %d.', $n));
        }

        for ($i = 0; $i < $n; $i ++) {
            // TODO: increment window start instead of using offsets.
            $tag = $d->getShort($offset + 12 * $i);
            Pel::debug(
                'Loading entry with tag 0x%04X: %s (%d of %d)...',
                $tag,
                PelTag::getName($this->type, $tag),
                $i + 1,
                $n);

            switch ($tag) {
                case PelTag::EXIF_IFD_POINTER:
                case PelTag::GPS_INFO_IFD_POINTER:
                case PelTag::INTEROPERABILITY_IFD_POINTER:
                    $o = $d->getLong($offset + 12 * $i + 8);
                    Pel::debug('Found sub IFD at offset %d', $o);

                    /* Map tag to IFD type. */
                    if ($tag == PelTag::EXIF_IFD_POINTER) {
                        $type = PelIfd::EXIF;
                    } elseif ($tag == PelTag::GPS_INFO_IFD_POINTER) {
                        $type = PelIfd::GPS;
                    } elseif ($tag == PelTag::INTEROPERABILITY_IFD_POINTER) {
                        $type = PelIfd::INTEROPERABILITY;
                    }

                    $this->sub[$type] = new PelIfd($type);
                    $this->sub[$type]->load($d, $o);
                    break;
                case PelTag::JPEG_INTERCHANGE_FORMAT:
                    $thumb_offset = $d->getLong($offset + 12 * $i + 8);
                    $this->safeSetThumbnail($d, $thumb_offset, $thumb_length);
                    break;
                case PelTag::JPEG_INTERCHANGE_FORMAT_LENGTH:
                    $thumb_length = $d->getLong($offset + 12 * $i + 8);
                    $this->safeSetThumbnail($d, $thumb_offset, $thumb_length);
                    break;
                default:
                    $format = $d->getShort($offset + 12 * $i + 2);
                    $components = $d->getLong($offset + 12 * $i + 4);

                    /*
                     * The data size. If bigger than 4 bytes, the actual data is
                     * not in the entry but somewhere else, with the offset stored
                     * in the entry.
                     */
                    $s = PelFormat::getSize($format) * $components;
                    if ($s > 0) {
                        $doff = $offset + 12 * $i + 8;
                        if ($s > 4) {
                            $doff = $d->getLong($doff);
                        }
                        $data = $d->getClone($doff, $s);
                    } else {
                        $data = new PelDataWindow();
                    }

                    try {
                        $entry = $this->newEntryFromData($tag, $format, $components, $data);
                        $this->addEntry($entry);
                    } catch (PelException $e) {
                        /*
                         * Throw the exception when running in strict mode, store
                         * otherwise.
                         */
                        Pel::maybeThrow($e);
                    }

                    /* The format of the thumbnail is stored in this tag. */
                    // TODO: handle TIFF thumbnail.
                    // if ($tag == PelTag::COMPRESSION) {
                    // $this->thumb_format = $data->getShort();
                    // }
                    break;
            }
        }

        /* Offset to next IFD */
        $o = $d->getLong($offset + 12 * $n);
        Pel::debug('Current offset is %d, link at %d points to %d.', $offset, $offset + 12 * $n, $o);

        if ($o > 0) {
            /* Sanity check: we need 6 bytes */
            if ($o > $d->getSize() - 6) {
                Pel::maybeThrow(new PelIfdException('Bogus offset to next IFD: ' . '%d > %d!', $o, $d->getSize() - 6));
            } else {
                if ($this->type == PelIfd::IFD1) {
                    // IFD1 shouldn't link further...
                    Pel::maybeThrow(new PelIfdException('IFD1 links to another IFD!'));
                }
                $this->next = new PelIfd(PelIfd::IFD1);
                $this->next->load($d, $o);
            }
        } else {
            Pel::debug('Last IFD.');
        }
    }

    /**
     * Make a new entry from a bunch of bytes.
     *
     * This method will create the proper subclass of {@link PelEntry}
     * corresponding to the {@link PelTag} and {@link PelFormat} given.
     * The entry will be initialized with the data given.
     *
     * Please note that the data you pass to this method should come
     * from an image, that is, it should be raw bytes. If instead you
     * want to create an entry for holding, say, an short integer, then
     * create a {@link PelEntryShort} object directly and load the data
     * into it.
     *
     * A {@link PelUnexpectedFormatException} is thrown if a mismatch is
     * discovered between the tag and format, and likewise a {@link
     * PelWrongComponentCountException} is thrown if the number of
     * components does not match the requirements of the tag. The
     * requirements for a given tag (if any) can be found in the
     * documentation for {@link PelTag}.
     *
     * @param integer $tag
     *            the tag of the entry as defined in {@link PelTag}.
     *
     * @param integer $format
     *            the format of the entry as defined in {@link PelFormat}.
     *
     * @param int $components
     *            the components in the entry.
     *
     * @param PelDataWindow $data
     *            the data which will be used to construct the
     *            entry.
     *
     * @return PelEntry a newly created entry, holding the data given.
     */
    public function newEntryFromData($tag, $format, $components, PelDataWindow $data)
    {

        /*
         * First handle tags for which we have a specific PelEntryXXX
         * class.
         */
        switch ($this->type) {
            case self::IFD0:
            case self::IFD1:
            case self::EXIF:
            case self::INTEROPERABILITY:
                switch ($tag) {
                    case PelTag::DATE_TIME:
                    case PelTag::DATE_TIME_ORIGINAL:
                    case PelTag::DATE_TIME_DIGITIZED:
                        if ($format != PelFormat::ASCII) {
                            throw new PelUnexpectedFormatException($this->type, $tag, $format, PelFormat::ASCII);
                        }
                        if ($components != 20) {
                            throw new PelWrongComponentCountException($this->type, $tag, $components, 20);
                        }
                        // TODO: handle timezones.
                        return new PelEntryTime($tag, $data->getBytes(0, - 1), PelEntryTime::EXIF_STRING);

                    case PelTag::COPYRIGHT:
                        if ($format != PelFormat::ASCII) {
                            throw new PelUnexpectedFormatException($this->type, $tag, $format, PelFormat::ASCII);
                        }
                        $v = explode("\0", trim($data->getBytes(), ' '));
                        if (! isset($v[1])) {
                            Pel::maybeThrow(new PelException('Invalid copyright: %s', $data->getBytes()));
                            // when not in strict mode, set empty copyright and continue
                            $v[1] = '';
                        }
                        return new PelEntryCopyright($v[0], $v[1]);

                    case PelTag::EXIF_VERSION:
                    case PelTag::FLASH_PIX_VERSION:
                    case PelTag::INTEROPERABILITY_VERSION:
                        if ($format != PelFormat::UNDEFINED) {
                            throw new PelUnexpectedFormatException($this->type, $tag, $format, PelFormat::UNDEFINED);
                        }
                        return new PelEntryVersion($tag, $data->getBytes() / 100);

                    case PelTag::USER_COMMENT:
                        if ($format != PelFormat::UNDEFINED) {
                            throw new PelUnexpectedFormatException($this->type, $tag, $format, PelFormat::UNDEFINED);
                        }
                        if ($data->getSize() < 8) {
                            return new PelEntryUserComment();
                        } else {
                            return new PelEntryUserComment($data->getBytes(8), rtrim($data->getBytes(0, 8)));
                        }
                    // this point can not be reached
                    case PelTag::XP_TITLE:
                    case PelTag::XP_COMMENT:
                    case PelTag::XP_AUTHOR:
                    case PelTag::XP_KEYWORDS:
                    case PelTag::XP_SUBJECT:
                        if ($format != PelFormat::BYTE) {
                            throw new PelUnexpectedFormatException($this->type, $tag, $format, PelFormat::BYTE);
                        }
                        $v = '';
                        for ($i = 0; $i < $components; $i ++) {
                            $b = $data->getByte($i);
                            /*
                             * Convert the byte to a character if it is non-null ---
                             * information about the character encoding of these entries
                             * would be very nice to have! So far my tests have shown
                             * that characters in the Latin-1 character set are stored in
                             * a single byte followed by a NULL byte.
                             */
                            if ($b != 0) {
                                $v .= chr($b);
                            }
                        }

                        return new PelEntryWindowsString($tag, $v);
                }
            // This point can be reached! Continue with default.
            case self::GPS:
            default:
                /* Then handle the basic formats. */
                switch ($format) {
                    case PelFormat::BYTE:
                        $v = new PelEntryByte($tag);
                        for ($i = 0; $i < $components; $i ++) {
                            $v->addNumber($data->getByte($i));
                        }
                        return $v;

                    case PelFormat::SBYTE:
                        $v = new PelEntrySByte($tag);
                        for ($i = 0; $i < $components; $i ++) {
                            $v->addNumber($data->getSByte($i));
                        }
                        return $v;

                    case PelFormat::ASCII:
                        return new PelEntryAscii($tag, rtrim($data->getBytes(0), "\0"));

                    case PelFormat::SHORT:
                        $v = new PelEntryShort($tag);
                        for ($i = 0; $i < $components; $i ++) {
                            $v->addNumber($data->getShort($i * 2));
                        }
                        return $v;

                    case PelFormat::SSHORT:
                        $v = new PelEntrySShort($tag);
                        for ($i = 0; $i < $components; $i ++) {
                            $v->addNumber($data->getSShort($i * 2));
                        }
                        return $v;

                    case PelFormat::LONG:
                        $v = new PelEntryLong($tag);
                        for ($i = 0; $i < $components; $i ++) {
                            $v->addNumber($data->getLong($i * 4));
                        }
                        return $v;

                    case PelFormat::SLONG:
                        $v = new PelEntrySLong($tag);
                        for ($i = 0; $i < $components; $i ++) {
                            $v->addNumber($data->getSLong($i * 4));
                        }
                        return $v;

                    case PelFormat::RATIONAL:
                        $v = new PelEntryRational($tag);
                        for ($i = 0; $i < $components; $i ++) {
                            $v->addNumber($data->getRational($i * 8));
                        }
                        return $v;

                    case PelFormat::SRATIONAL:
                        $v = new PelEntrySRational($tag);
                        for ($i = 0; $i < $components; $i ++) {
                            $v->addNumber($data->getSRational($i * 8));
                        }
                        return $v;

                    case PelFormat::UNDEFINED:
                        return new PelEntryUndefined($tag, $data->getBytes());

                    default:
                        throw new PelException('Unsupported format: %s', PelFormat::getName($format));
                }
        }
    }

    /**
     * Extract thumbnail data safely.
     *
     * It is safe to call this method repeatedly with either the offset
     * or the length set to zero, since it requires both of these
     * arguments to be positive before the thumbnail is extracted.
     *
     * When both parameters are set it will check the length against the
     * available data and adjust as necessary. Only then is the
     * thumbnail data loaded.
     *
     * @param PelDataWindow $d
     *            the data from which the thumbnail will be
     *            extracted.
     *
     * @param int $offset
     *            the offset into the data.
     *
     * @param int $length
     *            the length of the thumbnail.
     */
    private function safeSetThumbnail(PelDataWindow $d, $offset, $length)
    {
        /*
         * Load the thumbnail if both the offset and the length is
         * available.
         */
        if ($offset > 0 && $length > 0) {
            /*
             * Some images have a broken length, so we try to carefully
             * check the length before we store the thumbnail.
             */
            if ($offset + $length > $d->getSize()) {
                Pel::maybeThrow(
                    new PelIfdException(
                        'Thumbnail length %d bytes ' . 'adjusted to %d bytes.',
                        $length,
                        $d->getSize() - $offset));
                $length = $d->getSize() - $offset;
            }

            /* Now set the thumbnail normally. */
            $this->setThumbnail($d->getClone($offset, $length));
        }
    }

    /**
     * Set thumbnail data.
     *
     * Use this to embed an arbitrary JPEG image within this IFD. The
     * data will be checked to ensure that it has a proper {@link
     * PelJpegMarker::EOI} at the end. If not, then the length is
     * adjusted until one if found. An {@link PelIfdException} might be
     * thrown (depending on {@link Pel::$strict}) this case.
     *
     * @param PelDataWindow $d
     *            the thumbnail data.
     */
    public function setThumbnail(PelDataWindow $d)
    {
        $size = $d->getSize();
        /* Now move backwards until we find the EOI JPEG marker. */
        while ($d->getByte($size - 2) != 0xFF || $d->getByte($size - 1) != PelJpegMarker::EOI) {
            $size --;
        }

        if ($size != $d->getSize()) {
            Pel::maybeThrow(new PelIfdException('Decrementing thumbnail size ' . 'to %d bytes', $size));
        }
        $this->thumb_data = $d->getClone(0, $size);
    }

    /**
     * Get the type of this directory.
     *
     * @return int of {@link PelIfd::IFD0}, {@link PelIfd::IFD1}, {@link
     *         PelIfd::EXIF}, {@link PelIfd::GPS}, or {@link
     *         PelIfd::INTEROPERABILITY}.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Is a given tag valid for this IFD?
     *
     * Different types of IFDs can contain different kinds of tags ---
     * the {@link IFD0} type, for example, cannot contain a {@link
     * PelTag::GPS_LONGITUDE} tag.
     *
     * A special exception is tags with values above 0xF000. They are
     * treated as private tags and will be allowed everywhere (use this
     * for testing or for implementing your own types of tags).
     *
     * @param int $tag
     *            the tag.
     *
     * @return boolean true if the tag is considered valid in this IFD,
     *         false otherwise.
     *
     * @see getValidTags()
     */
    public function isValidTag($tag)
    {
        return $tag > 0xF000 || in_array($tag, $this->getValidTags());
    }

    /**
     * Returns a list of valid tags for this IFD.
     *
     * @return array an array of {@link PelTag}s which are valid for
     *         this IFD.
     */
    public function getValidTags()
    {
        switch ($this->type) {
            case PelIfd::IFD0:
            case PelIfd::IFD1:
                return array(
                    PelTag::IMAGE_WIDTH,
                    PelTag::IMAGE_LENGTH,
                    PelTag::BITS_PER_SAMPLE,
                    PelTag::COMPRESSION,
                    PelTag::PHOTOMETRIC_INTERPRETATION,
                    PelTag::DOCUMENT_NAME,
                    PelTag::IMAGE_DESCRIPTION,
                    PelTag::MAKE,
                    PelTag::MODEL,
                    PelTag::STRIP_OFFSETS,
                    PelTag::ORIENTATION,
                    PelTag::SAMPLES_PER_PIXEL,
                    PelTag::ROWS_PER_STRIP,
                    PelTag::STRIP_BYTE_COUNTS,
                    PelTag::X_RESOLUTION,
                    PelTag::Y_RESOLUTION,
                    PelTag::PLANAR_CONFIGURATION,
                    PelTag::RESOLUTION_UNIT,
                    PelTag::TRANSFER_FUNCTION,
                    PelTag::SOFTWARE,
                    PelTag::DATE_TIME,
                    PelTag::ARTIST,
                    PelTag::WHITE_POINT,
                    PelTag::PRIMARY_CHROMATICITIES,
                    PelTag::JPEG_INTERCHANGE_FORMAT,
                    PelTag::JPEG_INTERCHANGE_FORMAT_LENGTH,
                    PelTag::YCBCR_COEFFICIENTS,
                    PelTag::YCBCR_SUB_SAMPLING,
                    PelTag::YCBCR_POSITIONING,
                    PelTag::REFERENCE_BLACK_WHITE,
                    PelTag::COPYRIGHT,
                    PelTag::EXIF_IFD_POINTER,
                    PelTag::GPS_INFO_IFD_POINTER,
                    PelTag::PRINT_IM,
                    PelTag::XP_TITLE,
                    PelTag::XP_COMMENT,
                    PelTag::XP_AUTHOR,
                    PelTag::XP_KEYWORDS,
                    PelTag::XP_SUBJECT,
                    PelTag::RATING,
                    PelTag::RATING_PERCENT
                );

            case PelIfd::EXIF:
                return array(
                    PelTag::EXPOSURE_TIME,
                    PelTag::FNUMBER,
                    PelTag::EXPOSURE_PROGRAM,
                    PelTag::SPECTRAL_SENSITIVITY,
                    PelTag::ISO_SPEED_RATINGS,
                    PelTag::OECF,
                    PelTag::EXIF_VERSION,
                    PelTag::DATE_TIME_ORIGINAL,
                    PelTag::DATE_TIME_DIGITIZED,
                    PelTag::OFFSET_TIME,
                    PelTag::OFFSET_TIME_ORIGINAL,
                    PelTag::OFFSET_TIME_DIGITIZED,
                    PelTag::COMPONENTS_CONFIGURATION,
                    PelTag::COMPRESSED_BITS_PER_PIXEL,
                    PelTag::SHUTTER_SPEED_VALUE,
                    PelTag::APERTURE_VALUE,
                    PelTag::BRIGHTNESS_VALUE,
                    PelTag::EXPOSURE_BIAS_VALUE,
                    PelTag::MAX_APERTURE_VALUE,
                    PelTag::SUBJECT_DISTANCE,
                    PelTag::METERING_MODE,
                    PelTag::LIGHT_SOURCE,
                    PelTag::FLASH,
                    PelTag::FOCAL_LENGTH,
                    PelTag::MAKER_NOTE,
                    PelTag::USER_COMMENT,
                    PelTag::SUB_SEC_TIME,
                    PelTag::SUB_SEC_TIME_ORIGINAL,
                    PelTag::SUB_SEC_TIME_DIGITIZED,
                    PelTag::FLASH_PIX_VERSION,
                    PelTag::COLOR_SPACE,
                    PelTag::PIXEL_X_DIMENSION,
                    PelTag::PIXEL_Y_DIMENSION,
                    PelTag::RELATED_SOUND_FILE,
                    PelTag::FLASH_ENERGY,
                    PelTag::SPATIAL_FREQUENCY_RESPONSE,
                    PelTag::FOCAL_PLANE_X_RESOLUTION,
                    PelTag::FOCAL_PLANE_Y_RESOLUTION,
                    PelTag::FOCAL_PLANE_RESOLUTION_UNIT,
                    PelTag::SUBJECT_LOCATION,
                    PelTag::EXPOSURE_INDEX,
                    PelTag::SENSING_METHOD,
                    PelTag::FILE_SOURCE,
                    PelTag::SCENE_TYPE,
                    PelTag::CFA_PATTERN,
                    PelTag::CUSTOM_RENDERED,
                    PelTag::EXPOSURE_MODE,
                    PelTag::WHITE_BALANCE,
                    PelTag::DIGITAL_ZOOM_RATIO,
                    PelTag::FOCAL_LENGTH_IN_35MM_FILM,
                    PelTag::SCENE_CAPTURE_TYPE,
                    PelTag::GAIN_CONTROL,
                    PelTag::CONTRAST,
                    PelTag::SATURATION,
                    PelTag::SHARPNESS,
                    PelTag::DEVICE_SETTING_DESCRIPTION,
                    PelTag::SUBJECT_DISTANCE_RANGE,
                    PelTag::IMAGE_UNIQUE_ID,
                    PelTag::INTEROPERABILITY_IFD_POINTER,
                    PelTag::GAMMA
                );

            case PelIfd::GPS:
                return array(
                    PelTag::GPS_VERSION_ID,
                    PelTag::GPS_LATITUDE_REF,
                    PelTag::GPS_LATITUDE,
                    PelTag::GPS_LONGITUDE_REF,
                    PelTag::GPS_LONGITUDE,
                    PelTag::GPS_ALTITUDE_REF,
                    PelTag::GPS_ALTITUDE,
                    PelTag::GPS_TIME_STAMP,
                    PelTag::GPS_SATELLITES,
                    PelTag::GPS_STATUS,
                    PelTag::GPS_MEASURE_MODE,
                    PelTag::GPS_DOP,
                    PelTag::GPS_SPEED_REF,
                    PelTag::GPS_SPEED,
                    PelTag::GPS_TRACK_REF,
                    PelTag::GPS_TRACK,
                    PelTag::GPS_IMG_DIRECTION_REF,
                    PelTag::GPS_IMG_DIRECTION,
                    PelTag::GPS_MAP_DATUM,
                    PelTag::GPS_DEST_LATITUDE_REF,
                    PelTag::GPS_DEST_LATITUDE,
                    PelTag::GPS_DEST_LONGITUDE_REF,
                    PelTag::GPS_DEST_LONGITUDE,
                    PelTag::GPS_DEST_BEARING_REF,
                    PelTag::GPS_DEST_BEARING,
                    PelTag::GPS_DEST_DISTANCE_REF,
                    PelTag::GPS_DEST_DISTANCE,
                    PelTag::GPS_PROCESSING_METHOD,
                    PelTag::GPS_AREA_INFORMATION,
                    PelTag::GPS_DATE_STAMP,
                    PelTag::GPS_DIFFERENTIAL
                );

            case PelIfd::INTEROPERABILITY:
                return array(
                    PelTag::INTEROPERABILITY_INDEX,
                    PelTag::INTEROPERABILITY_VERSION,
                    PelTag::RELATED_IMAGE_FILE_FORMAT,
                    PelTag::RELATED_IMAGE_WIDTH,
                    PelTag::RELATED_IMAGE_LENGTH
                );

            /*
             * TODO: Where do these tags belong?
             * PelTag::FILL_ORDER,
             * PelTag::TRANSFER_RANGE,
             * PelTag::JPEG_PROC,
             * PelTag::BATTERY_LEVEL,
             * PelTag::IPTC_NAA,
             * PelTag::INTER_COLOR_PROFILE,
             * PelTag::CFA_REPEAT_PATTERN_DIM,
             */
        }
    }

    /**
     * Get the name of an IFD type.
     *
     * @param int $type
     *            one of {@link PelIfd::IFD0}, {@link PelIfd::IFD1},
     *            {@link PelIfd::EXIF}, {@link PelIfd::GPS}, or {@link
     *            PelIfd::INTEROPERABILITY}.
     *
     * @return string the name of type.
     */
    public static function getTypeName($type)
    {
        switch ($type) {
            case self::IFD0:
                return '0';
            case self::IFD1:
                return '1';
            case self::EXIF:
                return 'Exif';
            case self::GPS:
                return 'GPS';
            case self::INTEROPERABILITY:
                return 'Interoperability';
            default:
                throw new PelIfdException('Unknown IFD type: %d', $type);
        }
    }

    /**
     * Get the name of this directory.
     *
     * @return string the name of this directory.
     */
    public function getName()
    {
        return $this->getTypeName($this->type);
    }

    /**
     * Adds an entry to the directory.
     *
     * @param PelEntry $e
     *            the entry that will be added. If the entry is not
     *            valid in this IFD (as per {@link isValidTag()}) an
     *            {@link PelInvalidDataException} is thrown.
     *
     * @todo The entry will be identified with its tag, so each
     *       directory can only contain one entry with each tag. Is this a
     *       bug?
     */
    public function addEntry(PelEntry $e)
    {
        if ($this->isValidTag($e->getTag())) {
            $e->setIfdType($this->type);
            $this->entries[$e->getTag()] = $e;
        } else {
            throw new PelInvalidDataException("IFD %s cannot hold\n%s", $this->getName(), $e->__toString());
        }
    }

    /**
     * Does a given tag exist in this IFD?
     *
     * This methods is part of the ArrayAccess SPL interface for
     * overriding array access of objects, it allows you to check for
     * existance of an entry in the IFD:
     *
     * <code>
     * if (isset($ifd[PelTag::FNUMBER]))
     * // ... do something with the F-number.
     * </code>
     *
     * @param int $tag
     *            the offset to check.
     *
     * @return boolean whether the tag exists.
     */
    public function offsetExists($tag)
    {
        return isset($this->entries[$tag]);
    }

    /**
     * Retrieve a given tag from this IFD.
     *
     * This methods is part of the ArrayAccess SPL interface for
     * overriding array access of objects, it allows you to read entries
     * from the IFD the same was as for an array:
     *
     * <code>
     * $entry = $ifd[PelTag::FNUMBER];
     * </code>
     *
     * @param int $tag
     *            the tag to return. It is an error to ask for a tag
     *            which is not in the IFD, just like asking for a non-existant
     *            array entry.
     *
     * @return PelEntry the entry.
     */
    public function offsetGet($tag)
    {
        return $this->entries[$tag];
    }

    /**
     * Set or update a given tag in this IFD.
     *
     * This methods is part of the ArrayAccess SPL interface for
     * overriding array access of objects, it allows you to add new
     * entries or replace esisting entries by doing:
     *
     * <code>
     * $ifd[PelTag::EXPOSURE_BIAS_VALUE] = $entry;
     * </code>
     *
     * Note that the actual array index passed is ignored! Instead the
     * {@link PelTag} from the entry is used.
     *
     * @param int $tag
     *            unused.
     *
     * @param PelEntry $e
     *            the new value.
     */
    public function offsetSet($tag, $e)
    {
        if ($e instanceof PelEntry) {
            $tag = $e->getTag();
            $this->entries[$tag] = $e;
        } else {
            throw new PelInvalidArgumentException('Argument "%s" must be a PelEntry.', $e);
        }
    }

    /**
     * Unset a given tag in this IFD.
     *
     * This methods is part of the ArrayAccess SPL interface for
     * overriding array access of objects, it allows you to delete
     * entries in the IFD by doing:
     *
     * <code>
     * unset($ifd[PelTag::EXPOSURE_BIAS_VALUE])
     * </code>
     *
     * @param int $tag
     *            the offset to delete.
     */
    public function offsetUnset($tag)
    {
        unset($this->entries[$tag]);
    }

    /**
     * Retrieve an entry.
     *
     * @param int $tag
     *            the tag identifying the entry.
     *
     * @return PelEntry the entry associated with the tag, or null if no
     *         such entry exists.
     */
    public function getEntry($tag)
    {
        if (isset($this->entries[$tag])) {
            return $this->entries[$tag];
        } else {
            return null;
        }
    }

    /**
     * Returns all entries contained in this IFD.
     *
     * @return array an array of {@link PelEntry} objects, or rather
     *         descendant classes. The array has {@link PelTag}s as keys
     *         and the entries as values.
     *
     * @see getEntry
     * @see getIterator
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Return an iterator for all entries contained in this IFD.
     *
     * Used with foreach as in
     *
     * <code>
     * foreach ($ifd as $tag => $entry) {
     * // $tag is now a PelTag and $entry is a PelEntry object.
     * }
     * </code>
     *
     * @return Iterator an iterator using the {@link PelTag tags} as
     *         keys and the entries as values.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->entries);
    }

    /**
     * Returns available thumbnail data.
     *
     * @return string the bytes in the thumbnail, if any. If the IFD
     *         does not contain any thumbnail data, the empty string is
     *         returned.
     *
     * @todo Throw an exception instead when no data is available?
     *
     * @todo Return the $this->thumb_data object instead of the bytes?
     */
    public function getThumbnailData()
    {
        if ($this->thumb_data !== null) {
            return $this->thumb_data->getBytes();
        } else {
            return '';
        }
    }

    /**
     * Make this directory point to a new directory.
     *
     * @param PelIfd $i
     *            the IFD that this directory will point to.
     */
    public function setNextIfd(PelIfd $i)
    {
        $this->next = $i;
    }

    /**
     * Return the IFD pointed to by this directory.
     *
     * @return PelIfd the next IFD, following this IFD. If this is the
     *         last IFD, null is returned.
     */
    public function getNextIfd()
    {
        return $this->next;
    }

    /**
     * Check if this is the last IFD.
     *
     * @return boolean true if there are no following IFD, false
     *         otherwise.
     */
    public function isLastIfd()
    {
        return $this->next === null;
    }

    /**
     * Add a sub-IFD.
     *
     * Any previous sub-IFD of the same type will be overwritten.
     *
     * @param PelIfd $sub
     *            the sub IFD. The type of must be one of {@link
     *            PelIfd::EXIF}, {@link PelIfd::GPS}, or {@link
     *            PelIfd::INTEROPERABILITY}.
     */
    public function addSubIfd(PelIfd $sub)
    {
        $this->sub[$sub->type] = $sub;
    }

    /**
     * Return a sub IFD.
     *
     * @param int $type
     *            the type of the sub IFD. This must be one of {@link
     *            PelIfd::EXIF}, {@link PelIfd::GPS}, or {@link
     *            PelIfd::INTEROPERABILITY}.
     *
     * @return PelIfd the IFD associated with the type, or null if that
     *         sub IFD does not exist.
     */
    public function getSubIfd($type)
    {
        if (isset($this->sub[$type])) {
            return $this->sub[$type];
        } else {
            return null;
        }
    }

    /**
     * Get all sub IFDs.
     *
     * @return array an associative array with (IFD-type, {@link
     *         PelIfd}) pairs.
     */
    public function getSubIfds()
    {
        return $this->sub;
    }

    /**
     * Turn this directory into bytes.
     *
     * This directory will be turned into a byte string, with the
     * specified byte order. The offsets will be calculated from the
     * offset given.
     *
     * @param int $offset
     *            the offset of the first byte of this directory.
     *
     * @param PelByteOrder $order
     *            the byte order that should be used when
     *            turning integers into bytes. This should be one of {@link
     *            PelConvert::LITTLE_ENDIAN} and {@link PelConvert::BIG_ENDIAN}.
     */
    public function getBytes($offset, $order)
    {
        $bytes = '';
        $extra_bytes = '';

        Pel::debug('Bytes from IDF will start at offset %d within Exif data', $offset);

        $n = count($this->entries) + count($this->sub);
        if ($this->thumb_data !== null) {
            /*
             * We need two extra entries for the thumbnail offset and
             * length.
             */
            $n += 2;
        }

        $bytes .= PelConvert::shortToBytes($n, $order);

        /*
         * Initialize offset of extra data. This included the bytes
         * preceding this IFD, the bytes needed for the count of entries,
         * the entries themselves (and sub entries), the extra data in the
         * entries, and the IFD link.
         */
        $end = $offset + 2 + 12 * $n + 4;

        foreach ($this->entries as $tag => $entry) {
            /* Each entry is 12 bytes long. */
            $bytes .= PelConvert::shortToBytes($entry->getTag(), $order);
            $bytes .= PelConvert::shortToBytes($entry->getFormat(), $order);
            $bytes .= PelConvert::longToBytes($entry->getComponents(), $order);

            /*
             * Size? If bigger than 4 bytes, the actual data is not in
             * the entry but somewhere else.
             */
            $data = $entry->getBytes($order);
            $s = strlen($data);
            if ($s > 4) {
                Pel::debug('Data size %d too big, storing at offset %d instead.', $s, $end);
                $bytes .= PelConvert::longToBytes($end, $order);
                $extra_bytes .= $data;
                $end += $s;
            } else {
                Pel::debug('Data size %d fits.', $s);
                /*
                 * Copy data directly, pad with NULL bytes as necessary to
                 * fill out the four bytes available.
                 */
                $bytes .= $data . str_repeat(chr(0), 4 - $s);
            }
        }

        if ($this->thumb_data !== null) {
            Pel::debug('Appending %d bytes of thumbnail data at %d', $this->thumb_data->getSize(), $end);
            // TODO: make PelEntry a class that can be constructed with
            // arguments corresponding to the newt four lines.
            $bytes .= PelConvert::shortToBytes(PelTag::JPEG_INTERCHANGE_FORMAT_LENGTH, $order);
            $bytes .= PelConvert::shortToBytes(PelFormat::LONG, $order);
            $bytes .= PelConvert::longToBytes(1, $order);
            $bytes .= PelConvert::longToBytes($this->thumb_data->getSize(), $order);

            $bytes .= PelConvert::shortToBytes(PelTag::JPEG_INTERCHANGE_FORMAT, $order);
            $bytes .= PelConvert::shortToBytes(PelFormat::LONG, $order);
            $bytes .= PelConvert::longToBytes(1, $order);
            $bytes .= PelConvert::longToBytes($end, $order);

            $extra_bytes .= $this->thumb_data->getBytes();
            $end += $this->thumb_data->getSize();
        }

        /* Find bytes from sub IFDs. */
        $sub_bytes = '';
        foreach ($this->sub as $type => $sub) {
            if ($type == PelIfd::EXIF) {
                $tag = PelTag::EXIF_IFD_POINTER;
            } elseif ($type == PelIfd::GPS) {
                $tag = PelTag::GPS_INFO_IFD_POINTER;
            } elseif ($type == PelIfd::INTEROPERABILITY) {
                $tag = PelTag::INTEROPERABILITY_IFD_POINTER;
            } else {
                // PelConvert::BIG_ENDIAN is the default used by PelConvert
                $tag = PelConvert::BIG_ENDIAN;
            }
            /* Make an aditional entry with the pointer. */
            $bytes .= PelConvert::shortToBytes($tag, $order);
            /* Next the format, which is always unsigned long. */
            $bytes .= PelConvert::shortToBytes(PelFormat::LONG, $order);
            /* There is only one component. */
            $bytes .= PelConvert::longToBytes(1, $order);

            $data = $sub->getBytes($end, $order);
            $s = strlen($data);
            $sub_bytes .= $data;

            $bytes .= PelConvert::longToBytes($end, $order);
            $end += $s;
        }

        /* Make link to next IFD, if any */
        if ($this->isLastIFD()) {
            $link = 0;
        } else {
            $link = $end;
        }

        Pel::debug('Link to next IFD: %d', $link);

        $bytes .= PelConvert::longtoBytes($link, $order);

        $bytes .= $extra_bytes . $sub_bytes;

        if (! $this->isLastIfd()) {
            $bytes .= $this->next->getBytes($end, $order);
        }
        return $bytes;
    }

    /**
     * Turn this directory into text.
     *
     * @return string information about the directory, mainly for
     *         debugging.
     */
    public function __toString()
    {
        $str = Pel::fmt("Dumping IFD %s with %d entries...\n", $this->getName(), count($this->entries));

        foreach ($this->entries as $entry) {
            $str .= $entry->__toString();
        }
        $str .= Pel::fmt("Dumping %d sub IFDs...\n", count($this->sub));

        foreach ($this->sub as $type => $ifd) {
            $str .= $ifd->__toString();
        }
        if ($this->next !== null) {
            $str .= $this->next->__toString();
        }
        return $str;
    }
}
