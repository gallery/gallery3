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
 * Classes used to hold data for Exif tags of format undefined.
 *
 * This file contains the base class {@link PelEntryUndefined} and
 * the subclasses {@link PelEntryUserComment} which should be used
 * to manage the {@link PelTag::USER_COMMENT} tag, and {@link
 * PelEntryVersion} which is used to manage entries with version
 * information.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public
 *          License (GPL)
 * @package PEL
 */

/**
 * Class for holding data of any kind.
 *
 * This class can hold bytes of undefined format.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @package PEL
 */
class PelEntryUndefined extends PelEntry
{

    /**
     * Make a new PelEntry that can hold undefined data.
     *
     * @param integer $tag
     *            which this entry represents. This
     *            should be one of the constants defined in {@link PelTag},
     *            e.g., {@link PelTag::SCENE_TYPE}, {@link
     *            PelTag::MAKER_NOTE} or any other tag with format {@link
     *            PelFormat::UNDEFINED}.
     *
     * @param string $data
     *            the data that this entry will be holding. Since
     *            the format is undefined, no checking will be done on the data. If no data are given, a empty string will be stored
     */
    public function __construct($tag, $data = '')
    {
        $this->tag = $tag;
        $this->format = PelFormat::UNDEFINED;
        $this->setValue($data);
    }

    /**
     * Set the data of this undefined entry.
     *
     * @param string $data
     *            the data that this entry will be holding. Since
     *            the format is undefined, no checking will be done on the data.
     */
    public function setValue($data)
    {
        $this->components = strlen($data);
        $this->bytes = $data;
    }

    /**
     * Get the data of this undefined entry.
     *
     * @return string the data that this entry is holding.
     */
    public function getValue()
    {
        return $this->bytes;
    }

    /**
     * Get the value of this entry as text.
     *
     * The value will be returned in a format suitable for presentation.
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
            case PelTag::FILE_SOURCE:
                // CC (e->components, 1, v);
                switch (ord($this->bytes{0})) {
                    case 0x03:
                        return 'DSC';
                    default:
                        return sprintf('0x%02X', ord($this->bytes{0}));
                }
                break;
            case PelTag::SCENE_TYPE:
                // CC (e->components, 1, v);
                switch (ord($this->bytes{0})) {
                    case 0x01:
                        return 'Directly photographed';
                    default:
                        return sprintf('0x%02X', ord($this->bytes{0}));
                }
                break;
            case PelTag::COMPONENTS_CONFIGURATION:
                // CC (e->components, 4, v);
                $v = '';
                for ($i = 0; $i < 4; $i ++) {
                    switch (ord($this->bytes{$i})) {
                        case 0:
                            $v .= '-';
                            break;
                        case 1:
                            $v .= 'Y';
                            break;
                        case 2:
                            $v .= 'Cb';
                            break;
                        case 3:
                            $v .= 'Cr';
                            break;
                        case 4:
                            $v .= 'R';
                            break;
                        case 5:
                            $v .= 'G';
                            break;
                        case 6:
                            $v .= 'B';
                            break;
                        default:
                            $v .= 'reserved';
                            break;
                    }
                    if ($i < 3) {
                        $v .= ' ';
                    }
                }
                return $v;
                break;
            case PelTag::MAKER_NOTE:
                // TODO: handle maker notes.
                return $this->components . ' bytes unknown MakerNote data';
                break;
            default:
                return '(undefined)';
        }
    }
}
