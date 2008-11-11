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

/**
 * This helper provides a common around the user management functions. 
 * 
 * @author Tim Almdal <public@timalmdal.com>
 *
 */
class user {
  /**
   * Function to determine if the user has logged in.
   * @param $user(optional) Defaults to null, if specified will compare against the user in the 
   *                        session.
   * @returns boolean   true if logged in
   */
  public static function is_logged_in($user=null) {
    $session_user = Session::instance()->get("user", null);
    $logged_in = false;
    if (!empty($session_user)) {
      $logged_in = !empty($user) && $session_user === $user;
    }

    return $logged_in;
  }
}