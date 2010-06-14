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
class Organize_Controller extends Controller {
  function dialog($album_id) {
    $input = Input::instance();

    $album = ORM::factory("item", $album_id);
    access::required("view", $album);
    access::required("edit", $album);

    $v = new View("organize_dialog.html");
    $v->album = $album;
    // @todo turn this into an api call.
    $v->file_filter = addslashes(json_encode(
      array("photo" => array("label" => "Images",
                             "types" => array("*.jpg", "*.jpeg", "*.png", "*.gif")),
            "movie" => array("label" => "Movies", "types" => array("*.flv", "*.mp4")))));
    $v->domain = $input->server("SERVER_NAME");
    // @todo figure out how to connect this w/o a dependency
    $v->base_url = url::abs_site("rest") . "/";

    $v->sort_order = addslashes(json_encode(array("ASC" => (string)t("Ascending"), "DESC" => (string)t("Descending"))));
    $sort_fields = array();
    foreach (album::get_sort_order_options() as $field => $description) {
      $sort_fields[$field] = (string)$description;
    }
    $v->sort_fields = addslashes(json_encode($sort_fields));

    $user = identity::active_user();
    $v->access_key = rest::get_access_key($user->id)->access_key;

    $v->protocol = (empty($_SERVER["HTTPS"]) OR $_SERVER["HTTPS"] === "off") ? "http" : "https";
    print $v;
  }

  function add_album_fields() {
    print json_encode(array("title" => (string)t("Title"),
                            "description" => (string)t("Description"),
                            "name" => (string)t("Directory name"),
                            "slug" => (string)t("Internet Address")));
  }

}
