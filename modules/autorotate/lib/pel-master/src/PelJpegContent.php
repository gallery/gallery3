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

use \lsolesen\pel\PelDataWindow;

/**
 * Class representing content in a JPEG file.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public
 *          License (GPL)
 * @package PEL
 */

/**
 * Class representing content in a JPEG file.
 *
 * A JPEG file consists of a sequence of each of which has an
 * associated {@link PelJpegMarker marker} and some content. This
 * class represents the content, and this basic type is just a simple
 * holder of such content, represented by a {@link PelDataWindow}
 * object. The {@link PelExif} class is an example of more
 * specialized JPEG content.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 */
class PelJpegContent
{

    private $data = null;

    /**
     * Make a new piece of JPEG content.
     *
     * @param PelDataWindow $data
     *            the content.
     */
    public function __construct(PelDataWindow $data)
    {
        $this->data = $data;
    }

    /**
     * Return the bytes of the content.
     *
     * @return string bytes representing this JPEG content. These bytes
     *         will match the bytes given to {@link __construct the
     *         constructor}.
     */
    public function getBytes()
    {
        return $this->data->getBytes();
    }
}
