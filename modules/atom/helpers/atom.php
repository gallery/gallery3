<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

class atom_Core {

  /**
   * Converts a Unix timestamp to an Internet timestamp as defined in RFC3339.
   *  http://www.ietf.org/rfc/rfc3339.txt
   *
   * @todo Check if time zone is correct.
   * @todo Write test.
   *
   * @param int Unix timestamp
   * @return string Internet timestamp
   */
  public static function unix_to_internet_timestamp($timestamp) {
    return sprintf("%sZ", date("Y-m-d\TH:i:s", $timestamp));
  }

  /**
   * @todo can this be normalized with the code in MY_url
   */
  public static function get_absolute_url() {
    $base_url = atom::get_base_url();
    $absolute_url = $base_url . url::current(true);
    return $absolute_url;
  }

  /**
   * @todo can this be normalized with the code in MY_url
   */
  public static function get_base_url() {
    return sprintf("http://%s%s", $_SERVER["HTTP_HOST"], url::base(true));
  }
}
