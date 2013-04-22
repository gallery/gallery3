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
    $filter = Request::current()->query("filter");

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

  public function action_form_delete() {
    $id = $this->request->arg(0, "digit");
    $tag = ORM::factory("Tag", $id);
    if ($tag->loaded()) {
      $this->response->body(Tag::get_delete_form($tag));
    }
  }

  public function action_delete() {
    $id = $this->request->arg(0, "digit");
    Access::verify_csrf();

    $tag = ORM::factory("Tag", $id);
    if (!$tag->loaded()) {
      throw HTTP_Exception::factory(404);
    }

    $form = Tag::get_delete_form($tag);
    if ($form->validate()) {
      $name = $tag->name;
      $tag->delete();
      Message::success(t("Deleted tag %tag_name", array("tag_name" => $name)));
      GalleryLog::success("tags", t("Deleted tag %tag_name", array("tag_name" => $name)));

      $this->response->json(array("result" => "success", "location" => URL::site("admin/tags")));
    } else {
      $this->response->json(array("result" => "error", "html" => (string)$form));
    }
  }

  public function action_form_rename() {
    $id = $this->request->arg(0, "digit");
    $tag = ORM::factory("Tag", $id);
    if ($tag->loaded()) {
      $this->response->body(InPlaceEdit::factory($tag->name)
                            ->action("admin/tags/rename/$id")
                            ->render());
    }
  }

  public function action_rename() {
    $id = $this->request->arg(0, "digit");
    Access::verify_csrf();

    $tag = ORM::factory("Tag", $id);
    if (!$tag->loaded()) {
      throw HTTP_Exception::factory(404);
    }

    $in_place_edit = InPlaceEdit::factory($tag->name)
      ->action("admin/tags/rename/$tag->id")
      ->rules(array("required", "length[1,64]"));

    if ($in_place_edit->validate()) {
      $old_name = $tag->name;
      $new_name_or_list = $in_place_edit->value();
      $tag_list = explode(",", $new_name_or_list);

      $tag->name = array_shift($tag_list);
      $tag->save();

      if (!empty($tag_list)) {
        $this->_copy_items_for_tags($tag, $tag_list);
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
      $this->response->json(array("result" => "error", "form" => (string)$in_place_edit->render()));
    }
  }

  private function _copy_items_for_tags($tag, $tag_list) {
    foreach ($tag->items() as $item) {
      foreach ($tag_list as $new_tag_name) {
        Tag::add($item, trim($new_tag_name));
      }
    }
  }
}
