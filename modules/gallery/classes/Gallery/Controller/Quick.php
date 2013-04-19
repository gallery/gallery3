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
class Gallery_Controller_Quick extends Controller {
  public function action_rotate() {
    $id = $this->arg_required(0, "digit");
    $dir = $this->arg_required(1, "alpha");
    Access::verify_csrf();
    $item = ORM::factory("Item", $id);
    Access::required("view", $item);
    Access::required("edit", $item);

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
      $tmpfile = System::temp_filename("rotate",
        pathinfo($item->file_path(), PATHINFO_EXTENSION));
      GalleryGraphics::rotate($item->file_path(), $tmpfile, array("degrees" => $degrees), $item);
      $item->set_data_file($tmpfile);
      $item->save();
    }

    if (Request::current()->query("page_type") == "collection") {
      $this->response->json(
        array("src" => $item->thumb_url(),
              "width" => $item->thumb_width,
              "height" => $item->thumb_height));
    } else {
      $this->response->json(
        array("src" => $item->resize_url(),
              "width" => $item->resize_width,
              "height" => $item->resize_height));
    }
  }

  public function action_make_album_cover() {
    $id = $this->arg_required(0, "digit");
    Access::verify_csrf();

    $item = ORM::factory("Item", $id);
    Access::required("view", $item);
    Access::required("view", $item->parent());
    Access::required("edit", $item->parent());

    $msg = t("Made <b>%title</b> this album's cover", array("title" => HTML::purify($item->title)));

    Item::make_album_cover($item);
    Message::success($msg);

    $this->response->json(array("result" => "success", "reload" => 1));
  }

  public function action_form_delete() {
    $id = $this->arg_required(0, "digit");
    $item = ORM::factory("Item", $id);
    Access::required("view", $item);
    Access::required("edit", $item);

    $v = new View("gallery/quick_delete_confirm.html");
    $v->item = $item;
    $v->form = Item::get_delete_form($item);
    $this->response->body($v);
  }

  public function action_delete() {
    $id = $this->arg_required(0, "digit");
    Access::verify_csrf();
    $item = ORM::factory("Item", $id);
    Access::required("view", $item);
    Access::required("edit", $item);

    if ($item->is_album()) {
      $msg = t("Deleted album <b>%title</b>", array("title" => HTML::purify($item->title)));
    } else {
      $msg = t("Deleted photo <b>%title</b>", array("title" => HTML::purify($item->title)));
    }

    $parent = $item->parent();

    if ($item->is_album()) {
      // Album delete will trigger deletes for all children.  Do this in a batch so that we can be
      // smart about notifications, album cover updates, etc.
      Batch::start();
      $item->delete();
      Batch::stop();
    } else {
      $item->delete();
    }
    Message::success($msg);

    $from_id = Request::current()->query("from_id");
    if (Request::current()->query("page_type") == "collection" &&
        $from_id != $id /* deleted the item we were viewing */) {
      $this->response->json(array("result" => "success", "reload" => 1));
    } else {
      $this->response->json(array("result" => "success", "location" => $parent->url()));
    }
  }

  public function action_form_edit() {
    $id = $this->arg_required(0, "digit");
    $item = ORM::factory("Item", $id);
    Access::required("view", $item);
    Access::required("edit", $item);

    switch ($item->type) {
    case "album":
      $form = Album::get_edit_form($item);
      break;

    case "photo":
      $form = Photo::get_edit_form($item);
      break;

    case "movie":
      $form = Movie::get_edit_form($item);
      break;
    }

    // Pass on the source item where this form was generated, so we have an idea where to return to.
    $form->hidden("from_id")->value((int) Request::current()->query("from_id"));

    $this->response->body($form);
  }
}
