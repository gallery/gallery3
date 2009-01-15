<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
      Database::instance()->query("DELETE from `items_tags` where `tag_id` = $tag->id");
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
      print tag::get_rename_form($tag);
    }
  }

  public function rename($id) {
    access::verify_csrf();

    $tag = ORM::factory("tag", $id);
    if (!$tag->loaded) {
      kohana::show_404();
    }

    $form = tag::get_rename_form($tag);
    $valid = $form->validate();
    if ($valid) {
      $new_name = $form->rename_tag->inputs["name"]->value;
      $new_tag = ORM::factory("tag")->where("name", $new_name)->find();
      if ($new_tag->loaded) {
        $form->rename_tag->inputs["name"]->add_error("in_use", 1);
        $valid = false;
      }
    }

    if ($valid) {
      $old_name = $tag->name;
      $tag->name = $new_name;
      $tag->save();

      $message = t("Renamed tag %old_name to %new_name",
                   array("old_name" => $old_name, "new_name" => $tag->name));
      message::success($message);
      log::success("tags", $message);

      print json_encode(
        array("result" => "success",
              "location" => url::site("admin/tags")));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }
}

