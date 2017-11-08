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
 * Classes for dealing with Exif data.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public
 *          License (GPL)
 * @package PEL
 */

/**
 * Class representing Exif data.
 *
 * Exif data resides as {@link PelJpegContent data} and consists of a
 * header followed by a number of {@link PelJpegIfd IFDs}.
 *
 * The interesting method in this class is {@link getTiff()} which
 * will return the {@link PelTiff} object which really holds the data
 * which one normally think of when talking about Exif data. This is
 * because Exif data is stored as an extension of the TIFF file
 * format.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 */
class PelExif extends PelJpegContent
{

    /**
     * Exif header.
     *
     * The Exif data must start with these six bytes to be considered
     * valid.
     */
    const EXIF_HEADER = "Exif\0\0";

    /**
     * The PelTiff object contained within.
     *
     * @var PelTiff
     */
    private $tiff = null;

    /**
     * Construct a new Exif object.
     *
     * The new object will be empty --- use the {@link load()} method to
     * load Exif data from a {@link PelDataWindow} object, or use the
     * {@link setTiff()} to change the {@link PelTiff} object, which is
     * the true holder of the Exif {@link PelEntry entries}.
     */
    public function __construct()
    {
        // nothing to be done
    }

    /**
     * Load and parse Exif data.
     *
     * This will populate the object with Exif data, contained as a
     * {@link PelTiff} object. This TIFF object can be accessed with
     * the {@link getTiff()} method.
     *
     * @param PelDataWindow $d
     */
    public function load(PelDataWindow $d)
    {
        Pel::debug('Parsing %d bytes of Exif data...', $d->getSize());

        /* There must be at least 6 bytes for the Exif header. */
        if ($d->getSize() < 6) {
            throw new PelInvalidDataException('Expected at least 6 bytes of Exif ' . 'data, found just %d bytes.', $d->getSize());
        }
        /* Verify the Exif header */
        if ($d->strcmp(0, self::EXIF_HEADER)) {
            $d->setWindowStart(strlen(self::EXIF_HEADER));
        } else {
            throw new PelInvalidDataException('Exif header not found.');
        }

        /* The rest of the data is TIFF data. */
        $this->tiff = new PelTiff();
        $this->tiff->load($d);
    }

    /**
     * Change the TIFF information.
     *
     * Exif data is really stored as TIFF data, and this method can be
     * used to change this data from one {@link PelTiff} object to
     * another.
     *
     * @param PelTiff $tiff
     *            the new TIFF object.
     */
    public function setTiff(PelTiff $tiff)
    {
        $this->tiff = $tiff;
    }

    /**
     * Get the underlying TIFF object.
     *
     * The actual Exif data is stored in a {@link PelTiff} object, and
     * this method provides access to it.
     *
     * @return PelTiff the TIFF object with the Exif data.
     */
    public function getTiff()
    {
        return $this->tiff;
    }

    /**
     * Produce bytes for the Exif data.
     *
     * @return string bytes representing this object.
     */
    public function getBytes()
    {
        return self::EXIF_HEADER . $this->tiff->getBytes();
    }

    /**
     * Return a string representation of this object.
     *
     * @return string a string describing this object. This is mostly
     *         useful for debugging.
     */
    public function __toString()
    {
        return Pel::tra("Dumping Exif data...\n") . $this->tiff->__toString();
    }
}
