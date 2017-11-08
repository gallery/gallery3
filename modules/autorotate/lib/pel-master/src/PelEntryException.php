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
 * Classes for dealing with Exif entries.
 *
 * This file defines two exception classes and the abstract class
 * {@link PelEntry} which provides the basic methods that all Exif
 * entries will have. All Exif entries will be represented by
 * descendants of the {@link PelEntry} class --- the class itself is
 * abstract and so it cannot be instantiated.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public
 *          License (GPL)
 * @package PEL
 */

/**
 * Exception indicating a problem with an entry.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 * @subpackage Exception
 */
class PelEntryException extends PelException
{

    /**
     * The IFD type (if known).
     *
     * @var int
     */
    protected $type;

    /**
     * The tag of the entry (if known).
     *
     * @var int
     */
    protected $tag;

    /**
     * Get the IFD type associated with the exception.
     *
     * @return int one of {@link PelIfd::IFD0}, {@link PelIfd::IFD1},
     *         {@link PelIfd::EXIF}, {@link PelIfd::GPS}, or {@link
     *         PelIfd::INTEROPERABILITY}. If no type is set, null is returned.
     */
    public function getIfdType()
    {
        return $this->type;
    }

    /**
     * Get the tag associated with the exception.
     *
     * @return int the tag. If no tag is set, null is returned.
     */
    public function getTag()
    {
        return $this->tag;
    }
}
