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
class Quick_Controller extends Controller {
  public function rotate($id, $dir) {
    access::verify_csrf();
    $item = model_cache::get("item", $id);
    access::required("view", $item);
    access::required("edit", $item);

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

    if (Input::instance()->get("page_type") == "album") {
      print json_encode(
        array("src" => $item->thumb_url() . "?rnd=" . rand(),
              "width" => $item->thumb_width,
              "height" => $item->thumb_height));
    } else {
      print json_encode(
        array("src" => $item->resize_url() . "?rnd=" . rand(),
              "width" => $item->resize_width,
              "height" => $item->resize_height));
    }
  }

  public function make_album_cover($id) {
    access::verify_csrf();

    $item = model_cache::get("item", $id);
    access::required("view", $item);
    access::required("view", $item->parent());
    access::required("edit", $item->parent());

    $msg = t("Made <b>%title</b> this album's cover", array("title" => html::purify($item->title)));

    item::make_album_cover($item);
    message::success($msg);

    print json_encode(array("result" => "success", "reload" => 1));
  }

  public function form_delete($id) {
    $item = model_cache::get("item", $id);
    access::required("view", $item);
    access::required("edit", $item);

    if ($item->is_album()) {
      print t(
        "Delete the album <b>%title</b>? All photos and movies in the album will also be deleted.",
        array("title" => html::purify($item->title)));
    } else {
      print t("Are you sure you want to delete <b>%title</b>?",
              array("title" => html::purify($item->title)));
    }

    $form = item::get_delete_form($item);
    print $form;
  }

  public function delete($id) {
    access::verify_csrf();
    $item = model_cache::get("item", $id);
    access::required("view", $item);
    access::required("edit", $item);

    if ($item->is_album()) {
      $msg = t("Deleted album <b>%title</b>", array("title" => html::purify($item->title)));
    } else {
      $msg = t("Deleted photo <b>%title</b>", array("title" => html::purify($item->title)));
    }

    $parent = $item->parent();
    $item->delete();
    message::success($msg);

    if (Input::instance()->get("page_type") == "album") {
      print json_encode(array("result" => "success", "reload" => 1));
    } else {
      print json_encode(array("result" => "success",
                              "location" => $parent->url()));
    }
  }

  public function form_edit($id) {
    $item = model_cache::get("item", $id);
    access::required("view", $item);
    access::required("edit", $item);

    if ($item->is_album()) {
      $form = album::get_edit_form($item);
    } else {
      $form = photo::get_edit_form($item);
    }
    print $form;
  }
}
