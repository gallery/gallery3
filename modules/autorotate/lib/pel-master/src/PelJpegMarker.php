<?php

/*
 * PEL: PHP Exif Library.
 * A library with support for reading and
 * writing all Exif headers in JPEG and TIFF images using PHP.
 *
 * Copyright (C) 2004, 2006 Martin Geisler.
 * Copyright (C) 2017 Johannes Weberhofer.
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
 * Classes for dealing with JPEG markers.
 *
 * This class defines the constants to be used whenever one refers to
 * a JPEG marker. All the methods defined are static, and they all
 * operate on one argument which should be one of the class constants.
 * They will all be denoted by PelJpegMarker in the documentation.
 *
 * @author Martin Geisler <mgeisler@users.sourceforge.net>
 * @author Johannes Weberhofer <jweberhofer@weberhofer.at>
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public
 *          License (GPL)
 * @package PEL
 */
class PelJpegMarker
{

    /**
     * Encoding (baseline)
     */
    const SOF0 = 0xC0;

    /**
     * Encoding (extended sequential)
     */
    const SOF1 = 0xC1;

    /**
     * Encoding (progressive)
     */
    const SOF2 = 0xC2;

    /**
     * Encoding (lossless)
     */
    const SOF3 = 0xC3;

    /**
     * Define Huffman table
     */
    const DHT = 0xC4;

    /**
     * Encoding (differential sequential)
     */
    const SOF5 = 0xC5;

    /**
     * Encoding (differential progressive)
     */
    const SOF6 = 0xC6;

    /**
     * Encoding (differential lossless)
     */
    const SOF7 = 0xC7;

    /**
     * Extension
     */
    const JPG = 0xC8;

    /**
     * Encoding (extended sequential, arithmetic)
     */
    const SOF9 = 0xC9;

    /**
     * Encoding (progressive, arithmetic)
     */
    const SOF10 = 0xCA;

    /**
     * Encoding (lossless, arithmetic)
     */
    const SOF11 = 0xCB;

    /**
     * Define arithmetic coding conditioning
     */
    const DAC = 0xCC;

    /**
     * Encoding (differential sequential, arithmetic)
     */
    const SOF13 = 0xCD;

    /**
     * Encoding (differential progressive, arithmetic)
     */
    const SOF14 = 0xCE;

    /**
     * Encoding (differential lossless, arithmetic)
     */
    const SOF15 = 0xCF;

    /**
     * Restart 0
     */
    const RST0 = 0xD0;

    /**
     * Restart 1
     */
    const RST1 = 0xD1;

    /**
     * Restart 2
     */
    const RST2 = 0xD2;

    /**
     * Restart 3
     */
    const RST3 = 0xD3;

    /**
     * Restart 4
     */
    const RST4 = 0xD4;

    /**
     * Restart 5
     */
    const RST5 = 0xD5;

    /**
     * Restart 6
     */
    const RST6 = 0xD6;

    /**
     * Restart 7
     */
    const RST7 = 0xD7;

    /**
     * Start of image
     */
    const SOI = 0xD8;

    /**
     * End of image
     */
    const EOI = 0xD9;

    /**
     * Start of scan
     */
    const SOS = 0xDA;

    /**
     * Define quantization table
     */
    const DQT = 0xDB;

    /**
     * Define number of lines
     */
    const DNL = 0xDC;

    /**
     * Define restart interval
     */
    const DRI = 0xDD;

    /**
     * Define hierarchical progression
     */
    const DHP = 0xDE;

    /**
     * Expand reference component
     */
    const EXP = 0xDF;

    /**
     * Application segment 0
     */
    const APP0 = 0xE0;

    /**
     * Application segment 1
     *
     * When a JPEG image contains Exif data, the data will normally be
     * stored in this section and a call to {@link PelJpeg::getExif()}
     * will return a {@link PelExif} object representing it.
     */
    const APP1 = 0xE1;

    /**
     * Application segment 2
     */
    const APP2 = 0xE2;

    /**
     * Application segment 3
     */
    const APP3 = 0xE3;

    /**
     * Application segment 4
     */
    const APP4 = 0xE4;

    /**
     * Application segment 5
     */
    const APP5 = 0xE5;

    /**
     * Application segment 6
     */
    const APP6 = 0xE6;

    /**
     * Application segment 7
     */
    const APP7 = 0xE7;

    /**
     * Application segment 8
     */
    const APP8 = 0xE8;

    /**
     * Application segment 9
     */
    const APP9 = 0xE9;

    /**
     * Application segment 10
     */
    const APP10 = 0xEA;

    /**
     * Application segment 11
     */
    const APP11 = 0xEB;

    /**
     * Application segment 12
     */
    const APP12 = 0xEC;

    /**
     * Application segment 13
     */
    const APP13 = 0xED;

    /**
     * Application segment 14
     */
    const APP14 = 0xEE;

    /**
     * Application segment 15
     */
    const APP15 = 0xEF;

    /**
     * Extension 0
     */
    const JPG0 = 0xF0;

    /**
     * Extension 1
     */
    const JPG1 = 0xF1;

    /**
     * Extension 2
     */
    const JPG2 = 0xF2;

    /**
     * Extension 3
     */
    const JPG3 = 0xF3;

    /**
     * Extension 4
     */
    const JPG4 = 0xF4;

    /**
     * Extension 5
     */
    const JPG5 = 0xF5;

    /**
     * Extension 6
     */
    const JPG6 = 0xF6;

    /**
     * Extension 7
     */
    const JPG7 = 0xF7;

    /**
     * Extension 8
     */
    const JPG8 = 0xF8;

    /**
     * Extension 9
     */
    const JPG9 = 0xF9;

    /**
     * Extension 10
     */
    const JPG10 = 0xFA;

    /**
     * Extension 11
     */
    const JPG11 = 0xFB;

    /**
     * Extension 12
     */
    const JPG12 = 0xFC;

    /**
     * Extension 13
     */
    const JPG13 = 0xFD;

    /**
     * Comment
     */
    const COM = 0xFE;

    /**
     * Values for marker's short names
     */
    protected static $jpegMarkerShort = array(
        self::SOF0 => 'SOF0',
        self::SOF1 => 'SOF1',
        self::SOF2 => 'SOF2',
        self::SOF3 => 'SOF3',
        self::SOF5 => 'SOF5',
        self::SOF6 => 'SOF6',
        self::SOF7 => 'SOF7',
        self::SOF9 => 'SOF9',
        self::SOF10 => 'SOF10',
        self::SOF11 => 'SOF11',
        self::SOF13 => 'SOF13',
        self::SOF14 => 'SOF14',
        self::SOF15 => 'SOF15',
        self::SOI => 'SOI',
        self::EOI => 'EOI',
        self::SOS => 'SOS',
        self::COM => 'COM',
        self::DHT => 'DHT',
        self::JPG => 'JPG',
        self::DAC => 'DAC',
        self::RST0 => 'RST0',
        self::RST1 => 'RST1',
        self::RST2 => 'RST2',
        self::RST3 => 'RST3',
        self::RST4 => 'RST4',
        self::RST5 => 'RST5',
        self::RST6 => 'RST6',
        self::RST7 => 'RST7',
        self::DQT => 'DQT',
        self::DNL => 'DNL',
        self::DRI => 'DRI',
        self::DHP => 'DHP',
        self::EXP => 'EXP',
        self::APP0 => 'APP0',
        self::APP1 => 'APP1',
        self::APP2 => 'APP2',
        self::APP3 => 'APP3',
        self::APP4 => 'APP4',
        self::APP5 => 'APP5',
        self::APP6 => 'APP6',
        self::APP7 => 'APP7',
        self::APP8 => 'APP8',
        self::APP9 => 'APP9',
        self::APP10 => 'APP10',
        self::APP11 => 'APP11',
        self::APP12 => 'APP12',
        self::APP13 => 'APP13',
        self::APP14 => 'APP14',
        self::APP15 => 'APP15',
        self::JPG0 => 'JPG0',
        self::JPG1 => 'JPG1',
        self::JPG2 => 'JPG2',
        self::JPG3 => 'JPG3',
        self::JPG4 => 'JPG4',
        self::JPG5 => 'JPG5',
        self::JPG6 => 'JPG6',
        self::JPG7 => 'JPG7',
        self::JPG8 => 'JPG8',
        self::JPG9 => 'JPG9',
        self::JPG10 => 'JPG10',
        self::JPG11 => 'JPG11',
        self::JPG12 => 'JPG12',
        self::JPG13 => 'JPG13',
        self::COM => 'COM'
    );

    /**
     * Values for marker's descriptions names.
     */
    protected static $jpegMarkerDescriptions = array(
        self::SOF0 => 'Encoding (baseline)',
        self::SOF1 => 'Encoding (extended sequential)',
        self::SOF2 => 'Encoding (progressive)',
        self::SOF3 => 'Encoding (lossless)',
        self::SOF5 => 'Encoding (differential sequential)',
        self::SOF6 => 'Encoding (differential progressive)',
        self::SOF7 => 'Encoding (differential lossless)',
        self::SOF9 => 'Encoding (extended sequential, arithmetic)',
        self::SOF10 => 'Encoding (progressive, arithmetic)',
        self::SOF11 => 'Encoding (lossless, arithmetic)',
        self::SOF13 => 'Encoding (differential sequential, arithmetic)',
        self::SOF14 => 'Encoding (differential progressive, arithmetic)',
        self::SOF15 => 'Encoding (differential lossless, arithmetic)',
        self::SOI => 'Start of image',
        self::EOI => 'End of image',
        self::SOS => 'Start of scan',
        self::COM => 'Comment',
        self::DHT => 'Define Huffman table',
        self::JPG => 'Extension',
        self::DAC => 'Define arithmetic coding conditioning',
        'RST' => 'Restart %d',
        self::DQT => 'Define quantization table',
        self::DNL => 'Define number of lines',
        self::DRI => 'Define restart interval',
        self::DHP => 'Define hierarchical progression',
        self::EXP => 'Expand reference component',
        'APP' => 'Application segment %d',
        'JPG' => 'Extension %d',
        self::COM => 'Comment'
    );

    /**
     * Check if a byte is a valid JPEG marker.
     * If the byte is recognized true is returned, otherwise false will be returned.
     *
     * @param integer $marker
     *            the marker as defined in {@link PelJpegMarker}
     *
     * @return boolean
     */
    public static function isValid($marker)
    {
        return ($marker >= self::SOF0 && $marker <= self::COM);
    }

    /**
     * Turn a JPEG marker into bytes.
     * This will be a string with just a single byte since all JPEG markers are simply single bytes.
     *
     * @param integer $marker
     *            the marker as defined in {@link PelJpegMarker}
     *
     * @return string
     */
    public static function getBytes($marker)
    {
        return chr($marker);
    }

    /**
     * Return the short name for a marker, e.g., 'SOI' for the Start
     * of Image marker.
     *
     * @param integer $marker
     *            the marker as defined in {@link PelJpegMarker}
     *
     * @return string
     */
    public static function getName($marker)
    {
        if (array_key_exists($marker, self::$jpegMarkerShort)) {
            return self::$jpegMarkerShort[$marker];
        } else {
            return Pel::fmt('Unknown marker: 0x%02X', $marker);
        }
    }

    /**
     * Returns a description of a JPEG marker.
     *
     * @param integer $marker
     *            the marker as defined in {@link PelJpegMarker}
     *
     * @return string
     */
    public static function getDescription($marker)
    {
        if (array_key_exists($marker, self::$jpegMarkerShort)) {
            if (array_key_exists($marker, self::$jpegMarkerDescriptions)) {
                return self::$jpegMarkerDescriptions[$marker];
            } else {
                $splitted = preg_split(
                    "/(\d+)/",
                    self::$jpegMarkerShort[$marker],
                    - 1,
                    PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                if ((count($splitted) == 2) && array_key_exists($splitted[0], self::$jpegMarkerDescriptions)) {
                    return Pel::fmt(self::$jpegMarkerDescriptions[$splitted[0]], $splitted[1]);
                }
            }
        }
        return Pel::fmt('Unknown marker: 0x%02X', $marker);
    }
}
