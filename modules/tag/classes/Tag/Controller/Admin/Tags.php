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

    if ($form->sent()) {
      if ($form->load()->validate()) {
        $tag->delete();
        Message::success(t("Deleted tag %tag_name", array("tag_name" => $tag->name)));
        GalleryLog::success("tags", t("Deleted tag %tag_name", array("tag_name" => $tag->name)));

        $this->response->json(array("result" => "success", "location" => URL::site("admin/tags")));
      } else {
        $this->response->json(array("result" => "error", "html" => (string)$form));
      }
      return;
    }

    $this->response->body($form);
  }

  public function action_edit() {
    $tag_id = $this->request->arg(0, "digit");
    $tag = ORM::factory("Tag", $tag_id);
    if (!$tag->loaded()) {
      throw HTTP_Exception::factory(404);
    }

    // Build our form.
    $form = Formo::form()
      ->attr("id", "g-in-place-edit-form")
      ->add_class("g-short-form")
      ->add("input", "input", $tag->name)
      ->add("submit", "input|submit", t("Save"));
    $form->input
      ->add_rule("not_empty")
      ->add_rule("max_length", array(":value", 64), t("Your tag is too long"));

    if ($form->sent()) {
      if ($form->load()->validate()) {
        $old_name = $tag->name;
        $new_name_or_list = $form->input->val();
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

        $this->response->json(array("result" => "success", "location" => URL::site("admin/tags")));
      } else {
        $this->response->json(array("result" => "error", "form" => (string)$form));
      }
      return;
    }

    // This is being called using in_place_edit - return the raw form.
    $this->response->body($form);
  }
}
