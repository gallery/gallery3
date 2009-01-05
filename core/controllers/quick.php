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
class Quick_Controller extends Controller {
  public function pane($id) {
    $item = ORM::factory("item", $id);
    if (!$item->loaded) {
      return "";
    }

    if ($item->type == "photo") {
      $view = new View("quick_pane.html");
      $view->item = $item;
      print $view;
    }
  }

  public function rotate($id, $dir) {
    access::verify_csrf();
    $item = ORM::factory("item", $id);
    if (!$item->loaded) {
      return "";
    }

    $degrees = 0;
    switch($dir) {
    case "ccw":
      $degrees = -90;
      break;

    case "cw":
      $degrees = 90;
      break;
    }

    if ($degrees) {
      graphics::rotate($item->file_path(), $item->file_path(), array("degrees" => $degrees));

      list($item->width, $item->height) = getimagesize($item->file_path());
      $item->resize_dirty= 1;
      $item->thumb_dirty= 1;
      $item->save();

      graphics::generate($item);

      $parent = $item->parent();
      if ($parent->album_cover_item_id == $item->id) {
        copy($item->thumb_path(), $parent->thumb_path());
        $parent->thumb_width = $item->thumb_width;
        $parent->thumb_height = $item->thumb_height;
        $parent->save();
      }
    }

    print json_encode(
      array("src" => $item->thumb_url() . "?rnd=" . rand(),
            "width" => $item->thumb_width,
            "height" => $item->thumb_height));
  }

  public function form_edit($id) {
    $item = ORM::factory("item", $id);
    access::required("edit", $item);
    $form = photo::get_edit_form($item);
    print $form;
  }
}
