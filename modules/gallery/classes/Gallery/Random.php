<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
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
class Gallery_Random {
  /**
   * Return a random 32 byte hash value.
   * @param string extra entropy data
   */
  static function hash($length=32) {
    require_once(MODPATH . "gallery/vendor/joomla/crypt.php");
    return md5(JCrypt::genRandomBytes($length));
  }

  /**
   * Return a random decimal number between 0 and 1, formatted as a string.
   * This formatting guards against numbers like "2.1E-6", so they are safe
   * for insertion into decimal-type MySQL columns.
   * @param  int  number of decimal places (default: 10)
   */
  static function percent($digits=10) {
    $max = pow(10, $digits);
    return sprintf("%f", mt_rand(0, $max) / $max);
  }

  /**
   * Return a random number between $min and $max.  If $min and $max are not specified,
   * return a random number between 0 and mt_getrandmax()
   */
  static function int($min=null, $max=null) {
    if ($min || $max) {
      return mt_rand($min, $max);
    }
    return mt_rand();
  }
}