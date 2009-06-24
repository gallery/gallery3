<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class digibug_Core {
  /**
   * Get a unique id for a print request.
   * Its a good thing we only support linux as this won't work on Windows
   */
  static function uuid() {
    $fp = @fopen("/dev/urandom", "rb");
    $bits = @fread($fp, 16);
    @fclose($fp);

    $time_low = bin2hex(substr($bits, 0, 4));
    $time_mid = bin2hex(substr($bits, 4, 2));
    $time_hi_and_version = bin2hex(substr($bits, 6, 2));
    $clock_seq_hi_and_reserved = bin2hex(substr($bits, 8, 2));
    $node = bin2hex ( substr ( $bits, 10, 6 ) );

    /**
     * Set the four most significant bits (bits 12 through 15) of the
     * time_hi_and_version field to the 4-bit version number from
     * Section 4.1.3.
     * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
     */
    $time_hi_and_version = hexdec ( $time_hi_and_version );
    $time_hi_and_version = $time_hi_and_version >> 4;
    $time_hi_and_version = $time_hi_and_version | 0x4000;

    /**
     * Set the two most significant bits (bits 6 and 7) of the
     * clock_seq_hi_and_reserved to zero and one, respectively.
     */
    $clock_seq_hi_and_reserved = hexdec ( $clock_seq_hi_and_reserved );
    $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
    $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;

    return sprintf ('%08s-%04s-%04x-%04x-%012s',
                    $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node);
  }
}
