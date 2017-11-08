<?php
/*
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
 * Exception thrown when an invalid marker is found.
 *
 * This exception is thrown when PEL expects to find a {@link
 * PelJpegMarker} and instead finds a byte that isn't a known marker.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @package PEL
 * @subpackage Exception
 */
class PelJpegInvalidMarkerException extends PelException
{

    /**
     * Construct a new invalid marker exception.
     * The exception will contain a message describing the error,
     * including the byte found and the offset of the offending byte.
     *
     * @param int $marker
     *            the byte found.
     *
     * @param int $offset
     *            the offset in the data.
     */
    public function __construct($marker, $offset)
    {
        parent::__construct('Invalid marker found at offset %d: 0x%2X', $offset, $marker);
    }
}
