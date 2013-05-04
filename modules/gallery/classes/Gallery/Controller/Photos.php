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

  /**
   * Edit a photo.  This generates the form, validates it, adds the item, and returns a response.
   * This can be used as an ajax dialog (preferable) or a normal view.
   */
  public function action_edit() {
    $item_id = $this->request->arg(0, "digit");
    $item = ORM::factory("Item", $item_id);
    if (!$item->loaded() || !$item->is_photo()) {
      // Item doesn't exist or isn't a photo - fire a 400 Bad Request.
      throw HTTP_Exception::factory(400);
    }
    Access::required("view", $item);
    Access::required("edit", $item);

    // Get the from_id query parameter, which defaults to the edited item's id.
    $from_id = Arr::get($this->request->query(), "from_id", $item->id);

    // Build the form.
    $form = Formo::form()
      ->add("from_id", "input|hidden", $from_id)
      ->add("item", "group")
      ->add("buttons", "group");
    $form->item
      ->add("title", "input")
      ->add("description", "textarea")
      ->add("name", "input")
      ->add("slug", "input");
    $form->buttons
      ->add("submit", "input|submit", t("Modify"));

    $form
      ->attr("id", "g-edit-photo-form");
    $form->item
      ->set("label", t("Edit Photo"));
    $form->item->title
      ->set("label", t("Title"))
      ->set("error_messages", array(
          "not_empty" => t("You must provide a title"),
          "max_length" => t("Your title is too long")
        ));
    $form->item->description
      ->set("label", t("Description"));
    $form->item->name
      ->set("label", t("Filename"))
      ->set("error_messages", array(
          "no_slashes" => t("The photo name can't contain a \"/\""),
          "no_backslashes" => t("The photo name can't contain a \"\\\""),
          "no_trailing_period" => t("The photo name can't end in \".\""),
          "not_empty" => t("You must provide a photo file name"),
          "max_length" => t("Your photo file name is too long"),
          "conflict" => t("There is already a movie, photo or album with this name"),
          "data_file_extension" => t("You cannot change the photo file extension")
        ));
    $form->item->slug
      ->set("label", t("Internet Address"))
      ->set("error_messages", array(
          "conflict" => t("There is already a movie, photo or album with this internet address"),
          "reserved" => t("This address is reserved and can't be used."),
          "not_url_safe" => t("The internet address should contain only letters, numbers, hyphens and underscores"),
          "not_empty" => t("You must provide an internet address"),
          "max_length" => t("Your internet address is too long")
        ));
    $form->buttons
      ->set("label", "");

    // Link the ORM model and call the form event
    $form->item->orm("link", array("model" => $item));
    //Module::event("item_edit_form", $item, $form);  // @todo: make these work.

    // We can't edit the root item's name or slug.
    if ($item->id == 1) {
      $form->item->name
        ->attr("type", "hidden")
        ->add_rule("equals", array(":value", $item->name));
      $form->item->slug
        ->attr("type", "hidden")
        ->add_rule("equals", array(":value", $item->slug));
    }

    // Load and validate the form.
    if ($form->sent()) {
      if ($form->load()->validate()) {
        // Passed - save item, run event, add to log, send message, then redirect to new item.
        $item->save();
        //Module::event("item_edit_form_completed", $item, $form);  // @todo: make these work.
        GalleryLog::success("content", t("Updated photo"),
                            HTML::anchor($item->url(), t("view")));
        Message::success(t("Saved photo %photo_title",
                           array("photo_title" => HTML::purify($item->title))));

        if ($this->request->is_ajax()) {
          // If from_id points to the item itself, redirect as the address may have changed.
          if ($form->from_id->val() == $item->id) {
            $this->response->json(array("result" => "success", "location" => $item->url()));
          } else {
            $this->response->json(array("result" => "success"));
          }
          return;
        } else {
          // We ignore the from_id for non-ajax responses.
          $this->redirect($item->abs_url());
        }
      } else {
        // Failed - if ajax, return an error.
        if ($this->request->is_ajax()) {
          $this->response->json(array("result" => "error", "html" => (string)$form));
          return;
        }
      }
    }

    // Nothing sent yet (ajax or non-ajax) or item validation failed (non-ajax).
    if ($this->request->is_ajax()) {
      // Send the basic form.
      $this->response->body($form);
    } else {
      // Wrap the basic form in a theme.
      $view_theme = new View_Theme("required/page.html", "other", "item_edit");
      $view_theme->page_title = $form->item->get("label");
      $view_theme->content = $form;
      $this->response->body($view_theme);
    }
  }
}
