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
  public function action_show() {
    $photo = $this->request->param("item");
    if (!is_object($photo)) {
      // action_show() must be a public action because we route to it in the bootstrap,
      // so make sure that we're actually receiving an object
      throw HTTP_Exception::factory(404);
    }
    Access::required("view", $photo);

    $template = new View_Theme("required/page.html", "item", "photo");
    $template->set_global(array("item" => $photo,
                                "children" => array(),
                                "children_count" => 0));
    $template->set_global(Item::get_display_context($photo));
    $template->content = new View("required/photo.html");

    $photo->increment_view_count();

    $this->response->body($template);
  }

  public function action_update() {
    $photo_id = $this->request->arg(0, "digit");
    Access::verify_csrf();
    $photo = ORM::factory("Item", $photo_id);
    Access::required("view", $photo);
    Access::required("edit", $photo);

    $form = $this->get_edit_form($photo);
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

      GalleryLog::success("content", "Updated photo", "<a href=\"{$photo->url()}\">view</a>");
      Message::success(
        t("Saved photo %photo_title", array("photo_title" => HTML::purify($photo->title))));

      if ($form->from_id->value == $photo->id) {
        // Use the new URL; it might have changed.
        $this->response->json(array("result" => "success", "location" => $photo->url()));
      } else {
        // Stay on the same page
        $this->response->json(array("result" => "success"));
      }
    } else {
      $this->response->json(array("result" => "error", "html" => (string)$form));
    }
  }

  public function action_form_edit() {
    $photo_id = $this->request->arg(0, "digit");
    $photo = ORM::factory("Item", $photo_id);
    Access::required("view", $photo);
    Access::required("edit", $photo);

    $this->response->body($this->get_edit_form($photo));
  }

  public function get_edit_form($photo) {
    $form = new Forge("photos/update/$photo->id", "", "post", array("id" => "g-edit-photo-form"));
    $form->hidden("from_id")->value($photo->id);
    $group = $form->group("edit_item")->label(t("Edit Photo"));
    $group->input("title")->label(t("Title"))->value($photo->title)
      ->error_messages("not_empty", t("You must provide a title"))
      ->error_messages("max_length", t("Your title is too long"));
    $group->textarea("description")->label(t("Description"))->value($photo->description);
    $group->input("name")->label(t("Filename"))->value($photo->name)
      ->error_messages("name_conflict", t("There is already a movie, photo or album with this name"))
      ->error_messages("no_slashes", t("The photo name can't contain a \"/\""))
      ->error_messages("no_backslashes", t("The photo name can't contain a \"\\\""))
      ->error_messages("no_trailing_period", t("The photo name can't end in \".\""))
      ->error_messages("data_file_extension", t("You cannot change the photo file extension"))
      ->error_messages("not_empty", t("You must provide a photo file name"))
      ->error_messages("max_length", t("Your photo file name is too long"));
    $group->input("slug")->label(t("Internet Address"))->value($photo->slug)
      ->error_messages(
        "conflict", t("There is already a movie, photo or album with this internet address"))
      ->error_messages(
        "not_url_safe",
        t("The internet address should contain only letters, numbers, hyphens and underscores"))
      ->error_messages("not_empty", t("You must provide an internet address"))
      ->error_messages("max_length", t("Your internet address is too long"));

    Module::event("item_edit_form", $photo, $form);

    $group = $form->group("buttons")->label("");
    $group->submit("")->value(t("Modify"));
    return $form;
  }
}
