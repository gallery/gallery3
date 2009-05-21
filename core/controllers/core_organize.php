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
class Core_Organize_Controller extends Controller {
  public function general() {
    access::verify_csrf();

    $itemids = Input::instance()->post("item");
    $item = ORM::factory("item")
      ->in("id", $itemids[0])
      ->find();
    access::required("edit", $item);

    $form = core_organize::get_general_edit_form($item);
    if ($form->validate()) {
      $orig = clone $item;
      $item->title = $form->title->value;
      $item->description = $form->description->value;
      $item->rename($form->dirname->value);
      $item->save();

      module::event("item_updated", $orig, $item);

      if ($item->is_album()) {
        log::success("content", "Updated album", "<a href=\"albums/$item->id\">view</a>");
        $message = t("Saved album %album_title", array("album_title" => $item->title));
      } else {
        log::success("content", "Updated photo", "<a href=\"photos/$item->id\">view</a>");
        $message = t("Saved photo %photo_title", array("photo_title" => $item->title));
      }
      print json_encode(array("form" => $form->__toString(), "message" => $message));
    } else {
      print json_encode(array("form" => $form->__toString()));
    }
  }

  public function sort() {
    access::verify_csrf();

    $itemids = Input::instance()->post("item");
    $item = ORM::factory("item")
      ->in("id", $itemids[0])
      ->find();
    access::required("edit", $item);

    $form = core_organize::get_sort_edit_form($item);
    if ($form->validate()) {
      $orig = clone $item;
      $item->sort_column = $form->column->value;
      $item->sort_order = $form->direction->value;
      $item->save();

      module::event("item_updated", $orig, $item);

      log::success("content", "Updated album", "<a href=\"albums/$item->id\">view</a>");
      $message = t("Saved album %album_title", array("album_title" => $item->title));
      print json_encode(array("form" => $form->__toString(), "message" => $message));
    } else {
      print json_encode(array("form" => $form->__toString()));
    }
  }

  public function reset_general() {
    $itemids = Input::instance()->get("item");
    $item = ORM::factory("item")
      ->in("id", $itemids[0])
      ->find();
    access::required("edit", $item);

    print core_organize::get_general_edit_form($item);
  }

  public function reset_sort() {
    $itemids = Input::instance()->get("item");
    $item = ORM::factory("item")
      ->in("id", $itemids[0])
      ->find();
    access::required("edit", $item);

    print core_organize::get_sort_edit_form($item);
  }

}
