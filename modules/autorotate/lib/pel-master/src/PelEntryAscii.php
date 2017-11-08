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
 * Classes used to hold ASCII strings.
 *
 * The classes defined here are to be used for Exif entries holding
 * ASCII strings, such as {@link PelTag::MAKE}, {@link
 * PelTag::SOFTWARE}, and {@link PelTag::DATE_TIME}. For
 * entries holding normal textual ASCII strings the class {@link
 * PelEntryAscii} should be used, but for entries holding
 * timestamps the class {@link PelEntryTime} would be more
 * convenient instead. Copyright information is handled by the {@link
 * PelEntryCopyright} class.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public
 *          License (GPL)
 * @package PEL
 */

/**
 * Class for holding a plain ASCII string.
 *
 * This class can hold a single ASCII string, and it will be used as in
 * <code>
 * $entry = $ifd->getEntry(PelTag::IMAGE_DESCRIPTION);
 * print($entry->getValue());
 * $entry->setValue('This is my image. I like it.');
 * </code>
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 */
class PelEntryAscii extends PelEntry
{

    /**
     * The string hold by this entry.
     *
     * This is the string that was given to the {@link __construct
     * constructor} or later to {@link setValue}, without any final NULL
     * character.
     *
     * @var string
     */
    private $str;

    /**
     * Make a new PelEntry that can hold an ASCII string.
     *
     * @param int $tag
     *            the tag which this entry represents. This should be
     *            one of the constants defined in {@link PelTag}, e.g., {@link
     *            PelTag::IMAGE_DESCRIPTION}, {@link PelTag::MODEL}, or any other
     *            tag with format {@link PelFormat::ASCII}.
     *
     * @param string $str
     *            the string that this entry will represent. The
     *            string must obey the same rules as the string argument to {@link
     *            setValue}, namely that it should be given without any trailing
     *            NULL character and that it must be plain 7-bit ASCII.
     */
    public function __construct($tag, $str = '')
    {
        $this->tag = $tag;
        $this->format = PelFormat::ASCII;
        self::setValue($str);
    }

    /**
     * Give the entry a new ASCII value.
     *
     * This will overwrite the previous value. The value can be
     * retrieved later with the {@link getValue} method.
     *
     * @param
     *            string the new value of the entry. This should be given
     *            without any trailing NULL character. The string must be plain
     *            7-bit ASCII, the string should contain no high bytes.
     *
     * @todo Implement check for high bytes?
     */
    public function setValue($str)
    {
        $this->components = strlen($str) + 1;
        $this->str = $str;
        $this->bytes = $str . chr(0x00);
    }

    /**
     * Return the ASCII string of the entry.
     *
     * @return string the string held, without any final NULL character.
     *         The string will be the same as the one given to {@link setValue}
     *         or to the {@link __construct constructor}.
     */
    public function getValue()
    {
        return $this->str;
    }

    /**
     * Return the ASCII string of the entry.
     *
     * This methods returns the same as {@link getValue}.
     *
     * @param
     *            boolean not used with ASCII entries.
     *
     * @return string the string held, without any final NULL character.
     *         The string will be the same as the one given to {@link setValue}
     *         or to the {@link __construct constructor}.
     */
    public function getText($brief = false)
    {
        return $this->str;
    }
}
