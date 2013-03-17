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
      $tmpfile = system::temp_filename("rotate",
        pathinfo($item->file_path(), PATHINFO_EXTENSION));
      gallery_graphics::rotate($item->file_path(), $tmpfile, array("degrees" => $degrees), $item);
      $item->set_data_file($tmpfile);
      $item->save();
    }

    if (Input::instance()->get("page_type") == "collection") {
      json::reply(
        array("src" => $item->thumb_url(),
              "width" => $item->thumb_width,
              "height" => $item->thumb_height));
    } else {
      json::reply(
        array("src" => $item->resize_url(),
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

    json::reply(array("result" => "success", "reload" => 1));
  }

  public function form_delete($id) {
    $item = model_cache::get("item", $id);
    access::required("view", $item);
    access::required("edit", $item);

    $v = new View("quick_delete_confirm.html");
    $v->item = $item;
    $v->form = item::get_delete_form($item);
    print $v;
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

    if ($item->is_album()) {
      // Album delete will trigger deletes for all children.  Do this in a batch so that we can be
      // smart about notifications, album cover updates, etc.
      batch::start();
      $item->delete();
      batch::stop();
    } else {
      $item->delete();
    }
    message::success($msg);

    $from_id = Input::instance()->get("from_id");
    if (Input::instance()->get("page_type") == "collection" &&
        $from_id != $id /* deleted the item we were viewing */) {
      json::reply(array("result" => "success", "reload" => 1));
    } else {
      json::reply(array("result" => "success", "location" => $parent->url()));
    }
  }

  public function form_edit($id) {
    $item = model_cache::get("item", $id);
    access::required("view", $item);
    access::required("edit", $item);

    switch ($item->type) {
    case "album":
      $form = album::get_edit_form($item);
      break;

    case "photo":
      $form = photo::get_edit_form($item);
      break;

    case "movie":
      $form = movie::get_edit_form($item);
      break;
    }

    // Pass on the source item where this form was generated, so we have an idea where to return to.
    $form->hidden("from_id")->value((int)Input::instance()->get("from_id", 0));

    print $form;
  }
}
