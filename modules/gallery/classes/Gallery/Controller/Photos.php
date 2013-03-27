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
class Gallery_Controller_Photos extends Controller_Items {
  public function show($photo) {
    if (!is_object($photo)) {
      // show() must be public because we route to it in URL::parse_url(), so make
      // sure that we're actually receiving an object
      throw new HTTP_Exception_404();
    }

    Access::required("view", $photo);

    $template = new View_Theme("page.html", "item", "photo");
    $template->set_global(array("item" => $photo,
                                "children" => array(),
                                "children_count" => 0));
    $template->set_global(Item::get_display_context($photo));
    $template->content = new View("photo.html");

    $photo->increment_view_count();

    print $template;
  }

  public function update($photo_id) {
    Access::verify_csrf();
    $photo = ORM::factory("Item", $photo_id);
    Access::required("view", $photo);
    Access::required("edit", $photo);

    $form = Photo::get_edit_form($photo);
    try {
      $valid = $form->validate();
      $photo->title = $form->edit_item->title->value;
      $photo->description = $form->edit_item->description->value;
      $photo->slug = $form->edit_item->slug->value;
      $photo->name = $form->edit_item->inputs["name"]->value;
      $photo->validate();
    } catch (ORM_Validation_Exception $e) {
      // Translate ORM validation errors into form error messages
      foreach ($e->validation->errors() as $key => $error) {
        $form->edit_item->inputs[$key]->add_error($error, 1);
      }
      $valid = false;
    }

    if ($valid) {
      $photo->save();
      Module::event("item_edit_form_completed", $photo, $form);

      Log::success("content", "Updated photo", "<a href=\"{$photo->url()}\">view</a>");
      Message::success(
        t("Saved photo %photo_title", array("photo_title" => HTML::purify($photo->title))));

      if ($form->from_id->value == $photo->id) {
        // Use the new URL; it might have changed.
        JSON::reply(array("result" => "success", "location" => $photo->url()));
      } else {
        // Stay on the same page
        JSON::reply(array("result" => "success"));
      }
    } else {
      JSON::reply(array("result" => "error", "html" => (string)$form));
    }
  }

  public function form_edit($photo_id) {
    $photo = ORM::factory("Item", $photo_id);
    Access::required("view", $photo);
    Access::required("edit", $photo);

    print Photo::get_edit_form($photo);
  }
}
