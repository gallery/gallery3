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

    $user = identity::active_user();
    $sort_fields = array();
    foreach (album::get_sort_order_options() as $field => $description) {
      $sort_fields[$field] = (string)$description;
    }
    $sort_order = array("ASC" => (string)t("Ascending"), "DESC" => (string)t("Descending"));
    $file_filter = json_encode(array(
      "photo" => array("label" => "Images", "types" => array("*.jpg", "*.jpeg", "*.png", "*.gif")),
      "movie" => array("label" => "Movies", "types" => array("*.flv", "*.mp4", "*.m4v"))));

    $v = new View("organize_dialog.html");
    $v->album = $album;
    $v->domain = $input->server("HTTP_HOST");
    $v->access_key = rest::access_key();
    $v->file_filter = addslashes($file_filter);
    $v->sort_order = addslashes(json_encode($sort_order));
    $v->sort_fields = addslashes(json_encode($sort_fields));
    $v->selected_id = Input::instance()->get("selected_id", null);
    $v->rest_uri = url::site("rest") . "/";
    $v->controller_uri = url::site("organize") . "/";
    $v->swf_uri = url::file("modules/organize/lib/Gallery3WebClient.swf?") .
      filemtime(MODPATH . "organize/lib/Gallery3WebClient.swf");
    print $v;
  }

  function add_album_fields() {
    json::reply(array("title" => (string)t("Title"),
                      "description" => (string)t("Description"),
                      "name" => (string)t("Directory name"),
                      "slug" => (string)t("Internet Address")));
  }

}
