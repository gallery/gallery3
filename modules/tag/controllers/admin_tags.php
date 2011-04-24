<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
    $filter = Input::instance()->get("filter");

    $view = new Admin_View("admin.html");
    $view->page_title = t("Manage tags");
    $view->content = new View("admin_tags.html");
    $view->content->filter = $filter;

    $query = ORM::factory("tag");
    if ($filter) {
      $query->like("name", $filter);
    }
    $view->content->tags = $query->order_by("name", "ASC")->find_all();
    print $view;
  }

  public function form_delete($id) {
    $tag = ORM::factory("tag", $id);
    if ($tag->loaded()) {
      print tag::get_delete_form($tag);
    }
  }

  public function delete($id) {
    access::verify_csrf();

    $tag = ORM::factory("tag", $id);
    if (!$tag->loaded()) {
      throw new Kohana_404_Exception();
    }

    $form = tag::get_delete_form($tag);
    if ($form->validate()) {
      $name = $tag->name;
      $tag->delete();
      message::success(t("Deleted tag %tag_name", array("tag_name" => $name)));
      log::success("tags", t("Deleted tag %tag_name", array("tag_name" => $name)));

      json::reply(array("result" => "success", "location" => url::site("admin/tags")));
    } else {
      print $form;
    }
  }

  public function form_rename($id) {
    $tag = ORM::factory("tag", $id);
    if ($tag->loaded()) {
      print InPlaceEdit::factory($tag->name)
        ->action("admin/tags/rename/$id")
        ->render();
    }
  }

  public function rename($id) {
    access::verify_csrf();

    $tag = ORM::factory("tag", $id);
    if (!$tag->loaded()) {
      throw new Kohana_404_Exception();
    }

    $in_place_edit = InPlaceEdit::factory($tag->name)
      ->action("admin/tags/rename/$tag->id")
      ->rules(array("required", "length[1,64]"));

    if ($in_place_edit->validate()) {
      $old_name = $tag->name;
      $tag_name = $in_place_edit->value();
      Kohana_Log::add("error", $tag_name);
      $tags = explode(",", $tag_name);
      $tag_count = count($tags);

      $tag->name = array_shift($tags);
      $tag->save();

      if (!empty($tags)) {
        $this->_copy_items_for_tags($tag, $tags);
        $message = t("Split tag <i>%old_name</i> into <i>%new_tags</i>",
                     array("old_name" => $old_name, "new_tags" => $tag_name));
      } else {
        $message = t("Renamed tag <i>%old_name</i> to <i>%new_name</i>",
                     array("old_name" => $old_name, "new_name" => $tag->name));
      }

      message::success($message);
      log::success("tags", $message);

      json::reply(array("result" => "success", "location" => url::site("admin/tags")));
    } else {
      json::reply(array("result" => "error", "form" => (string)$in_place_edit->render()));
    }
  }

  private function _copy_items_for_tags($tag, $tags) {
    foreach ($tag->items() as $item) {
      foreach ($tags as $idx => $new_tag) {
        tag::add($item, trim($new_tag));
      }
    }
  }
}
