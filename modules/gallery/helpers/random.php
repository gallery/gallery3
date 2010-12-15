<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
class random_Core {
  /**
   * Return a random 32 bit hash value.
   * @param string extra entropy data
   */
  static function hash($entropy="") {
    return md5($entropy . uniqid(mt_rand(), true));
  }

  /**
   * Return a random hexadecimal string of the given length.
   * @param int the desired length of the string
   */
  static function string($length) {
    return substr(random::hash(), 0, $length);
  }

  /**
   * Return a random floating point number between 0 and 1
   */
  static function percent() {
    return ((float)mt_rand()) / (float)mt_getrandmax();
  }

  /**
   * Return a random number between 0 and mt_getrandmax()
   */
  static function int() {
    return mt_rand();
  }
}