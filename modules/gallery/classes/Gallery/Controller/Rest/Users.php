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
class Gallery_Controller_Rest_Users extends Controller_Rest {
  /**
   * This read-only resource represents a user profile.
   *
   * GET can accept the following query parameters:
   *   show=self
   *     Return the active user
   *   show=guest
   *     Return the guest user
   *   @see  Controller_Rest_User::before()
   */

  /**
   * GET the user's entity.
   */
  static function get_entity($id, $params) {
    $user = Identity::lookup_user($id);
    if (!Identity::can_view_profile($user)) {
      throw Rest_Exception::factory(404);
    }

    // Add fields from a whitelist.
    $data = array();
    foreach (array("id", "name", "full_name", "email", "url", "locale") as $field) {
      $data[$field] = isset($user->$field) ? $user->$field : null;
    }

    return $data;
  }

  /**
   * Override Controller_Rest::before() to use the "show" parameter, if specified.
   */
  public function before() {
    parent::before();

    if ($show = $this->request->query("show")) {
      switch ($show) {
      case "self":
        $this->rest_id = Identity::active_user()->id;
        break;

      case "guest":
        $this->rest_id = Identity::guest()->id;
        break;

      default:
        throw Rest_Exception::factory(400, array("show" => "invalid"));
      }

      // Remove the "show" query parameter so it doesn't appear in URLs downstream.
      $query = $this->request->query();
      unset($query["show"]);
      $this->request->query($query);
    }
  }
}
