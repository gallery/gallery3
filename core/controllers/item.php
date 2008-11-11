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
class Item_Controller extends REST_Controller {
  protected $resource_type = "item";

  public function get($item) {
    // Redirect to the more specific resource type, since it will render
    // differently.  We could also just delegate here, but it feels more appropriate
    // to have a single canonical resource mapping.
    return url::redirect("{$item->type}/$item->id");
  }

  public function put($item) {
    // @todo Productionize this code
    // 1) Add security checks
    // 2) Support owner_ids properly

    switch ($this->input->post("type")) {
    case "album":
      $album = album::create(
        $item->id,
        $this->input->post("name"),
        $this->input->post("title", $this->input->post("name")),
        $this->input->post("description"));
      url::redirect("album/{$album->id}");
      break;

    case "photo":
      if (is_array($_FILES["file"]["name"])) {
        for ($i = 0; $i < count($_FILES["file"]["name"]); $i++) {
          if ($_FILES["file"]["error"][$i] == 0) {
            $photo = photo::create(
              $item->id,
              $_FILES["file"]["tmp_name"][$i],
              $_FILES["file"]["name"][$i],
              $_FILES["file"]["name"][$i]);
          } else {
            print "ERROR!";
            // @todo return a reasonable error
          }
        }
        url::redirect("album/{$item->id}");
      } else {
        $photo = photo::create(
          $item->id,
          $_FILES["file"]["tmp_name"],
          $_FILES["file"]["name"],
          $this->input->post("title", $this->input->post("name")),
          $this->input->post("description"));
        url::redirect("{$new_item->type}/{$new_item->id}");
      }
      break;
    }
  }

  public function delete($item) {
    // @todo Production this code
    // 1) Add security checks
    $parent = $item->parent();
    if ($parent->id) {
      $item->delete();
    }
    url::redirect("{$parent->type}/{$parent->id}");
  }

  public function post($item) {
    // @todo Productionize this
    // 1) Figure out how to do the right validation here.  Validate the form input and apply it to
    //    the model as appropriate.
    // 2) Figure out how to dispatch according to the needs of the client.  Ajax requests from
    //    jeditable will want the changed field back, and possibly the whole item in json.
    //
    // For now let's establish a simple protocol where the client passes in a __return parameter
    // that specifies which field it wants back from the item.  Later on we can expand that to
    // include a data format, etc.

    // These fields are safe to change
    $post = $this->input->post();
    foreach ($post as $key => $value) {
      switch ($key) {
      case "title":
      case "description":
        $item->$key = $value;
        break;
      }
    }

    // @todo Support additional fields
    // These fields require additional work if you change them
    // parent_id, owner_id

    $item->save();
    if (array_key_exists("__return", $post)) {
      print $item->{$post["__return"]};
    }
  }
}
