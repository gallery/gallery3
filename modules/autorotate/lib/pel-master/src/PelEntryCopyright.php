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
 * Class for holding copyright information.
 *
 * The Exif standard specifies a certain format for copyright
 * information where the one {@link PelTag::COPYRIGHT copyright
 * tag} holds both the photographer and editor copyrights, separated
 * by a NULL character.
 *
 * This class is used to manipulate that tag so that the format is
 * kept to the standard. A common use would be to add a new copyright
 * tag to an image, since most cameras do not add this tag themselves.
 * This would be done like this:
 *
 * <code>
 * $entry = new PelEntryCopyright('Copyright, Martin Geisler, 2004');
 * $ifd0->addEntry($entry);
 * </code>
 *
 * Here we only set the photographer copyright, use the optional
 * second argument to specify the editor copyright. If there is only
 * an editor copyright, then let the first argument be the empty
 * string.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 */
class PelEntryCopyright extends PelEntryAscii
{

    /**
     * The photographer copyright.
     *
     * @var string
     */
    private $photographer;

    /**
     * The editor copyright.
     *
     * @var string
     */
    private $editor;

    /**
     * Make a new entry for holding copyright information.
     *
     * @param
     *            string the photographer copyright. Use the empty string
     *            if there is no photographer copyright.
     *
     * @param
     *            string the editor copyright. Use the empty string if
     *            there is no editor copyright.
     */
    public function __construct($photographer = '', $editor = '')
    {
        parent::__construct(PelTag::COPYRIGHT);
        $this->setValue($photographer, $editor);
    }

    /**
     * Update the copyright information.
     *
     * @param
     *            string the photographer copyright. Use the empty string
     *            if there is no photographer copyright.
     *
     * @param
     *            string the editor copyright. Use the empty string if
     *            there is no editor copyright.
     */
    public function setValue($photographer = '', $editor = '')
    {
        $this->photographer = $photographer;
        $this->editor = $editor;

        if ($photographer == '' && $editor != '') {
            $photographer = ' ';
        }

        if ($editor == '') {
            parent::setValue($photographer);
        } else {
            parent::setValue($photographer . chr(0x00) . $editor);
        }
    }

    /**
     * Retrive the copyright information.
     *
     * The strings returned will be the same as the one used previously
     * with either {@link __construct the constructor} or with {@link
     * setValue}.
     *
     * @return array an array with two strings, the photographer and
     *         editor copyrights. The two fields will be returned in that
     *         order, so that the first array index will be the photographer
     *         copyright, and the second will be the editor copyright.
     */
    public function getValue()
    {
        return array(
            $this->photographer,
            $this->editor
        );
    }

    /**
     * Return a text string with the copyright information.
     *
     * The photographer and editor copyright fields will be returned
     * with a '-' in between if both copyright fields are present,
     * otherwise only one of them will be returned.
     *
     * @param
     *            boolean if false, then the strings '(Photographer)' and
     *            '(Editor)' will be appended to the photographer and editor
     *            copyright fields (if present), otherwise the fields will be
     *            returned as is.
     *
     * @return string the copyright information in a string.
     */
    public function getText($brief = false)
    {
        if ($brief) {
            $p = '';
            $e = '';
        } else {
            $p = ' ' . Pel::tra('(Photographer)');
            $e = ' ' . Pel::tra('(Editor)');
        }

        if ($this->photographer != '' && $this->editor != '') {
            return $this->photographer . $p . ' - ' . $this->editor . $e;
        }

        if ($this->photographer != '') {
            return $this->photographer . $p;
        }

        if ($this->editor != '') {
            return $this->editor . $e;
        }

        return '';
    }
}
