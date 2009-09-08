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
class Photos_Controller extends Items_Controller {

  /**
   *  @see REST_Controller::_show($resource)
   */
  public function _show($photo) {
    access::required("view", $photo);

    $position = $photo->parent()->get_position($photo->id);
    if ($position > 1) {
      list ($previous_item, $ignore, $next_item) =
        $photo->parent()->children(3, $position - 2);
    } else {
      $previous_item = null;
      list ($next_item) = $photo->parent()->children(1, $position);
    }

    $template = new Theme_View("page.html", "photo");
    $template->set_global("item", $photo);
    $template->set_global("children", array());
    $template->set_global("children_count", $photo->children_count());
    $template->set_global("parents", $photo->parents());
    $template->set_global("next_item", $next_item);
    $template->set_global("previous_item", $previous_item);
    $template->set_global("sibling_count", $photo->parent()->children_count());
    $template->set_global("position", $position);

    $template->content = new View("photo.html");

    $photo->view_count++;
    $photo->save();

    print $template;
  }


  /**
   * @see REST_Controller::_update($resource)
   */
  public function _update($photo) {
    access::verify_csrf();
    access::required("view", $photo);
    access::required("edit", $photo);

    $form = photo::get_edit_form($photo);
    $valid = $form->validate();
    if ($valid = $form->validate()) {
      if ($form->edit_item->filename->value != $photo->name ||
          $form->edit_item->slug->value != $photo->slug) {
        // Make sure that there's not a name or slug conflict
        if ($row = Database::instance()
            ->select(array("name", "slug"))
            ->from("items")
            ->where("parent_id", $photo->parent_id)
            ->where("id <>", $photo->id)
            ->open_paren()
            ->where("name", $form->edit_item->filename->value)
            ->orwhere("slug", $form->edit_item->slug->value)
            ->close_paren()
            ->get()
            ->current()) {
          if ($row->name == $form->edit_item->filename->value) {
            $form->edit_item->filename->add_error("name_conflict", 1);
          }
          if ($row->slug == $form->edit_item->slug->value) {
            $form->edit_item->slug->add_error("slug_conflict", 1);
          }
          $valid = false;
        }
      }
    }

    if ($valid) {
      $photo->title = $form->edit_item->title->value;
      $photo->description = $form->edit_item->description->value;
      $photo->slug = $form->edit_item->slug->value;
      $photo->rename($form->edit_item->filename->value);
      $photo->save();
      module::event("item_edit_form_completed", $photo, $form);

      log::success("content", "Updated photo", "<a href=\"{$photo->url()}\">view</a>");
      message::success(
                       t("Saved photo %photo_title",
                         array("photo_title" => html::purify($photo->title))));

      print json_encode(
        array("result" => "success"));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  /**
   *  @see REST_Controller::_form_edit($resource)
   */
  public function _form_edit($photo) {
    access::required("view", $photo);
    access::required("edit", $photo);

    print photo::get_edit_form($photo);
  }
}
