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
class Tag_Controller_Admin_Tags extends Controller_Admin {
  /**
   * Display the main tag admin form, from which the other three actions below are called.
   */
  public function action_index() {
    $filter = $this->request->query("filter");

    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Manage tags");
    $view->content = new View("admin/tags.html");
    $view->content->filter = $filter;

    $query = ORM::factory("Tag");
    if ($filter) {
      $query->like("name", $filter);
    }
    $view->content->tags = $query->order_by("name", "ASC")->find_all();
    $this->response->body($view);
  }

  /**
   * Delete a tag.  This generates the confirmation form, validates it,
   * deletes the tag, and returns a response.
   */
  public function action_delete() {
    $tag_id = $this->request->arg(0, "digit");
    $tag = ORM::factory("Tag", $tag_id);
    if (!$tag->loaded()) {
      throw HTTP_Exception::factory(404);
    }

    $form = Formo::form()
      ->attr("id", "g-delete-tag-form")
      ->add("confirm", "group");
    $form->confirm
      ->set("label", t("Confirm Deletion"))
      ->html(t("Really delete tag %tag_name?", array("tag_name" => HTML::purify($tag->name))))
      ->add("submit", "input|submit", t("Delete Tag"));

    if ($form->load()->validate()) {
      $tag->delete();
      Message::success(t("Deleted tag %tag_name", array("tag_name" => $tag->name)));
      GalleryLog::success("tags", t("Deleted tag %tag_name", array("tag_name" => $tag->name)));
    }

    $this->response->ajax_form($form);
  }

  /**
   * Edit a tag's name.  This is a short form (i.e. one field) that uses gallery.in_place_edit.js.
   */
  public function action_edit_name() {
    $tag_id = $this->request->arg(0, "digit");
    $tag = ORM::factory("Tag", $tag_id);
    if (!$tag->loaded()) {
      throw HTTP_Exception::factory(404);
    }

    // Build our form.
    $form = Formo::form()
      ->attr("id", "g-in-place-edit-form")
      ->add_class("g-short-form")
      ->add("name", "input", $tag->name)
      ->add("submit", "input|submit", t("Save"));
    $form->name
      ->add_rule("not_empty")
      ->add_rule("max_length", array(":value", 128));

    // Get the error messages.
    $form->set_var_fields("error_messages", static::get_form_error_messages());

    if ($form->load()->validate()) {
      $old_name = $tag->name;
      $new_name_or_list = $form->name->val();
      $tag_list = explode(",", $new_name_or_list);

      $tag->name = trim(array_shift($tag_list));
      $tag->save();

      if (!empty($tag_list)) {
        foreach ($tag->items->find_all() as $item) {
          foreach ($tag_list as $new_tag_name) {
            Tag::add($item, trim($new_tag_name));
          }
        }
        $message = t("Split tag <i>%old_name</i> into <i>%tag_list</i>",
                     array("old_name" => $old_name, "tag_list" => $new_name_or_list));
      } else {
        $message = t("Renamed tag <i>%old_name</i> to <i>%new_name</i>",
                     array("old_name" => $old_name, "new_name" => $tag->name));
      }

      Message::success($message);
      GalleryLog::success("tags", $message);
    }

    // This is being called using in_place_edit - use Response::ajax_form().
    $this->response->ajax_form($form);
  }

  /**
   * Edit a tag.  This generates the form, validates it, updates the tag, and returns a response.
   */
  public function action_edit() {
    $tag_id = $this->request->arg(0, "digit");
    $tag = ORM::factory("Tag", $tag_id);
    if (!$tag->loaded()) {
      throw HTTP_Exception::factory(404);
    }

    // Build our form.
    $form = Formo::form()
      ->attr("id", "g-edit-tag-form")
      ->add("tag", "group")
      ->add("other", "group");
    $form->tag
      ->set("label", t("Edit Tag"))
      ->add("name", "input")
      ->add("slug", "input");
    $form->other
      ->add("submit", "input|submit", t("Modify"));

    // Get the labels and error messages and link the ORM model.
    $form->tag->orm("link", array("model" => $tag));
    $form->tag->set_var_fields("label", static::get_form_labels());
    $form->tag->set_var_fields("error_messages", static::get_form_error_messages());

    if ($form->load()->validate()) {
      $tag->save();
      $message = t("Updated tag %name", array("name" => $tag->name));
      Message::success($message);
      GalleryLog::success("tags", $message);
    }

    // Merge the groups together for presentation purposes
    $form->merge_groups("other", "tag");

    $this->response->ajax_form($form);
  }

  /**
   * Get form labels for the tag group.  This is a helper function for the edit/add forms.
   * @see Controller_Items::get_form_labels(), which uses some of the same labels.
   */
  public static function get_form_labels() {
    return array(
      "name" => t("Tag Name"),
      "slug" => t("Internet Address")
    );
  }

  /**
   * Get form error messages for the tag group.  This is a helper function for the edit/add forms.
   * @see Controller_Items::get_form_error_messages(), which uses some of the same error messages.
   */
  public static function get_form_error_messages() {
    return array(
      "name" => array(
        "no_commas"           => t("The tag can't contain a \",\""),
        "no_untrimmed_spaces" => t("The tag can't begin or end with a space"),
        "not_empty"           => t("You must provide a name"),
        "max_length"          => t("Your tag is too long")
      ),
      "slug" => array(
        "conflict"     => t("There is already a tag with this internet address"),
        "not_url_safe" => t("The internet address should contain only letters, numbers, hyphens and underscores"),
        "not_empty"    => t("You must provide an internet address"),
        "max_length"   => t("Your internet address is too long")
      )
    );
  }
}
