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
class Admin_Tags_Controller extends Admin_Controller {
  public function index() {
    $filter = $this->input->get("filter");

    $view = new Admin_View("admin.html");
    $view->content = new View("admin_tags.html");
    $view->content->filter = $filter;

    $query = ORM::factory("tag");
    if ($filter) {
      $query->like("name", $filter);
    }
    $view->content->tags = $query->orderby("name", "ASC")->find_all();
    print $view;
  }

  public function form_delete($id) {
    $tag = ORM::factory("tag", $id);
    if ($tag->loaded) {
      print tag::get_delete_form($tag);
    }
  }

  public function delete($id) {
    access::verify_csrf();

    $tag = ORM::factory("tag", $id);
    if (!$tag->loaded) {
      kohana::show_404();
    }

    $form = tag::get_delete_form($tag);
    if ($form->validate()) {
      $name = $tag->name;
      Database::instance()->delete("items_tags", array("tag_id" => "$tag->id"));
      $tag->delete();
      message::success(t("Deleted tag %tag_name", array("tag_name" => $name)));
      log::success("tags", t("Deleted tag %tag_name", array("tag_name" => $name)));

      print json_encode(
        array("result" => "success",
              "location" => url::site("admin/tags")));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  public function form_rename($id) {
    $tag = ORM::factory("tag", $id);
    if ($tag->loaded) {
      print InPlaceEdit::factory($tag->name)
        ->action("admin/tags/rename/$id")
        ->render();
    }
  }

  public function rename($id) {
    access::verify_csrf();

    $tag = ORM::factory("tag", $id);
    if (!$tag->loaded) {
      kohana::show_404();
    }

    $in_place_edit = InPlaceEdit::factory($tag->name)
      ->action("admin/tags/rename/$tag->id")
      ->rules(array("required", "length[1,64]"))
      ->messages(array("in_use" => t("There is already a tag with that name")))
      ->callback(array($this, "check_for_duplicate"));

    if ($in_place_edit->validate()) {
      $old_name = $tag->name;
      $tag->name = $in_place_edit->value();
      $tag->save();

      $message = t("Renamed tag %old_name to %new_name",
                   array("old_name" => $old_name, "new_name" => $tag->name));
      message::success($message);
      log::success("tags", $message);

      print json_encode(array("result" => "success"));
    } else {
      print json_encode(array("result" => "error", "form" => $in_place_edit->render()));
    }
  }

  public function check_for_duplicate(Validation $post_data, $field) {
    $tag_exists = ORM::factory("tag")->where("name", $post_data[$field])->count_all();
    if ($tag_exists) {
      $post_data->add_error($field, "in_use");
    }
  }

}

