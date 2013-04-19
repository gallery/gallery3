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
class Gallery_Controller_Albums extends Controller_Items {
  public function action_show() {
    $album = $this->request->param("item");
    if (!is_object($album)) {
      // action_show() must be a public action because we route to it in the bootstrap,
      // so make sure that we're actually receiving an object
      throw HTTP_Exception::factory(404);
    }
    Access::required("view", $album);

    $page_size = Module::get_var("gallery", "page_size", 9);
    $show = Request::current()->query("show");

    if ($show) {
      $child = ORM::factory("Item", $show);
      $index = Item::get_position($child);
      if ($index) {
        $page = ceil($index / $page_size);
        if ($page == 1) {
          HTTP::redirect($album->abs_url());
        } else {
          HTTP::redirect($album->abs_url("page=$page"));
        }
      }
    }

    $page = Arr::get(Request::current()->query(), "page", "1");
    $children_count = $album->viewable()->children_count();
    $offset = ($page - 1) * $page_size;
    $max_pages = max(ceil($children_count / $page_size), 1);

    // Make sure that the page references a valid offset
    if ($page < 1) {
      HTTP::redirect($album->abs_url());
    } else if ($page > $max_pages) {
      HTTP::redirect($album->abs_url("page=$max_pages"));
    }

    $template = new View_Theme("required/page.html", "collection", "album");
    $template->set_global(
      array("page" => $page,
            "page_title" => null,
            "max_pages" => $max_pages,
            "page_size" => $page_size,
            "item" => $album,
            "children" => $album->viewable()->children($page_size, $offset),
            "parents" => $album->parents()->as_array(), // view calls empty() on this
            "breadcrumbs" => Breadcrumb::array_from_item_parents($album),
            "children_count" => $children_count));
    $template->content = new View("required/album.html");
    $album->increment_view_count();

    $this->response->body($template);
    Item::set_display_context_callback("Controller_Albums::get_display_context");
  }

  public static function get_display_context($item) {
    $where = array(array("type", "!=", "album"));
    $position = Item::get_position($item, $where);
    if ($position > 1) {
      list ($previous_item, $ignore, $next_item) =
        $item->parent()->viewable()->children(3, $position - 2, $where);
    } else {
      $previous_item = null;
      list ($next_item) = $item->parent()->viewable()->children(1, $position, $where);
    }

    return array("position" => $position,
                 "previous_item" => $previous_item,
                 "next_item" => $next_item,
                 "sibling_count" => $item->parent()->viewable()->children_count($where),
                 "siblings_callback" => array("Controller_Albums::get_siblings", array($item)),
                 "parents" => $item->parents()->as_array(),
                 "breadcrumbs" => Breadcrumb::array_from_item_parents($item));
  }

  public static function get_siblings($item, $limit=null, $offset=null) {
    // @todo consider creating Model_Item::siblings() if we use this more broadly.
    return $item->parent()->viewable()->children($limit, $offset);
  }

  public function action_create() {
    $parent_id = $this->arg_required(0, "digit");
    Access::verify_csrf();
    $album = ORM::factory("Item", $parent_id);
    Access::required("view", $album);
    Access::required("add", $album);

    $form = Album::get_add_form($album);
    try {
      $valid = $form->validate();
      $album = ORM::factory("Item");
      $album->type = "album";
      $album->parent_id = $parent_id;
      $album->name = $form->add_album->inputs["name"]->value;
      $album->title = $form->add_album->title->value ?
        $form->add_album->title->value : $form->add_album->inputs["name"]->value;
      $album->description = $form->add_album->description->value;
      $album->slug = $form->add_album->slug->value;
      $album->validate();
    } catch (ORM_Validation_Exception $e) {
      // Translate ORM validation errors into form error messages
      foreach ($e->validation->errors() as $key => $error) {
        $form->add_album->inputs[$key]->add_error($error, 1);
      }
      $valid = false;
    }

    if ($valid) {
      $album->save();
      Module::event("album_add_form_completed", $album, $form);
      GalleryLog::success("content", "Created an album",
                   HTML::anchor("albums/$album->id", "view album"));
      Message::success(t("Created album %album_title",
                         array("album_title" => HTML::purify($album->title))));

      JSON::reply(array("result" => "success", "location" => $album->url()));
    } else {
      JSON::reply(array("result" => "error", "html" => (string)$form));
    }
  }

  public function action_update() {
    $album_id = $this->arg_required(0, "digit");
    Access::verify_csrf();
    $album = ORM::factory("Item", $album_id);
    Access::required("view", $album);
    Access::required("edit", $album);

    $form = Album::get_edit_form($album);
    try {
      $valid = $form->validate();
      $album->title = $form->edit_item->title->value;
      $album->description = $form->edit_item->description->value;
      $album->sort_column = $form->edit_item->sort_order->column->value;
      $album->sort_order = $form->edit_item->sort_order->direction->value;
      if (array_key_exists("name", $form->edit_item->inputs)) {
        $album->name = $form->edit_item->inputs["name"]->value;
      }
      $album->slug = $form->edit_item->slug->value;
      $album->validate();
    } catch (ORM_Validation_Exception $e) {
      // Translate ORM validation errors into form error messages
      foreach ($e->validation->errors() as $key => $error) {
        $form->edit_item->inputs[$key]->add_error($error, 1);
      }
      $valid = false;
    }

    if ($valid) {
      $album->save();
      Module::event("item_edit_form_completed", $album, $form);

      GalleryLog::success("content", "Updated album", "<a href=\"albums/$album->id\">view</a>");
      Message::success(t("Saved album %album_title",
                         array("album_title" => HTML::purify($album->title))));

      if ($form->from_id->value == $album->id) {
        // Use the new URL; it might have changed.
        JSON::reply(array("result" => "success", "location" => $album->url()));
      } else {
        // Stay on the same page
        JSON::reply(array("result" => "success"));
      }
    } else {
      JSON::reply(array("result" => "error", "html" => (string)$form));
    }
  }

  public function action_form_add() {
    $album_id = $this->arg_required(0, "digit");
    $album = ORM::factory("Item", $album_id);
    Access::required("view", $album);
    Access::required("add", $album);

    $this->response->body(Album::get_add_form($album));
  }

  public function action_form_edit() {
    $album_id = $this->arg_required(0, "digit");
    $album = ORM::factory("Item", $album_id);
    Access::required("view", $album);
    Access::required("edit", $album);

    $this->response->body(Album::get_edit_form($album));
  }
}
