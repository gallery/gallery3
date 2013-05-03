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
    $show = $this->request->query("show");

    if ($show) {
      $child = ORM::factory("Item", $show);
      $index = Item::get_position($child);
      if ($index) {
        $page = ceil($index / $page_size);
        if ($page == 1) {
          $this->redirect($album->abs_url());
        } else {
          $this->redirect($album->abs_url("page=$page"));
        }
      }
    }

    $page = Arr::get($this->request->query(), "page", "1");
    $children_count = $album->children->viewable()->count_all();
    $offset = ($page - 1) * $page_size;
    $max_pages = max(ceil($children_count / $page_size), 1);

    // Make sure that the page references a valid offset
    if ($page < 1) {
      $this->redirect($album->abs_url());
    } else if ($page > $max_pages) {
      $this->redirect($album->abs_url("page=$max_pages"));
    }

    $template = new View_Theme("required/page.html", "collection", "album");
    $template->set_global(
      array("page" => $page,
            "page_title" => null,
            "max_pages" => $max_pages,
            "page_size" => $page_size,
            "item" => $album,
            "children" => $album->children->viewable()->limit($page_size)->offset($offset)->find_all(),
            "parents" => $album->parents->find_all()->as_array(), // view calls empty() on this
            "breadcrumbs" => Breadcrumb::array_from_item_parents($album),
            "children_count" => $children_count));
    $template->content = new View("required/album.html");
    $album->increment_view_count();

    $this->response->body($template);
    Item::set_display_context_callback("Controller_Albums::get_display_context");
  }

  public static function get_display_context($item) {
    $position = Item::get_position($item, $where);
    if ($position > 1) {
      list ($previous_item, $ignore, $next_item) = $item->parent->children
        ->viewable()
        ->where("type", "!=", "album")
        ->limit(3)
        ->offset($position - 2)
        ->find_all();
    } else {
      $previous_item = null;
      list ($next_item) = $item->parent->children
        ->viewable()
        ->where("type", "!=", "album")
        ->limit(1)
        ->offset($position)
        ->find_all();
    }

    return array("position" => $position,
                 "previous_item" => $previous_item,
                 "next_item" => $next_item,
                 "sibling_count" =>
                   $item->parent->children->viewable()->where("type", "!=", "album")->count_all(),
                 "siblings_callback" => array("Controller_Albums::get_siblings", array($item)),
                 "parents" => $item->parents->find_all()->as_array(),
                 "breadcrumbs" => Breadcrumb::array_from_item_parents($item));
  }

  public static function get_siblings($item, $limit=null, $offset=null) {
    // @todo consider creating Model_Item::siblings() if we use this more broadly.
    return $item->parent->children->viewable()->limit($limit)->offset($offset)->find_all();
  }

  /**
   * Add a new album.  This generates the form, validates it, adds the item, and returns a response.
   * This can be used as an ajax dialog (preferable) or a normal view.
   */
  public function action_add() {
    $parent_id = $this->request->arg(0, "digit");
    $parent = ORM::factory("Item", $parent_id);
    if (!$parent->loaded() || !$parent->is_album()) {
      // Parent doesn't exist or isn't an album - fire a 400 Bad Request.
      throw HTTP_Exception::factory(400);
    }
    Access::required("view", $parent);
    Access::required("add", $parent);

    // Build the item model.
    $item = ORM::factory("Item");
    $item->type = "album";
    $item->parent_id = $parent_id;

    // Build the form.
    $form = Formo::form()
      ->add("item", "group")
      ->add("buttons", "group");
    $form->item
      ->add("title", "input")
      ->add("description", "textarea")
      ->add("name", "input")
      ->add("slug", "input");
    $form->buttons
      ->add("submit", "input|submit", t("Create"));

    $form
      ->attr("id", "g-add-album-form")
      ->add_script_url("modules/gallery/assets/albums_form_add.js");
    $form->item
      ->set("label", t("Add an album to %album_title", array("album_title" => $parent->title)));
    $form->item->title
      ->set("label", t("Title"))
      ->set("error_messages", array(
          "not_empty" => t("You must provide a title"),
          "max_length" => t("Your title is too long")
        ));
    $form->item->description
      ->set("label", t("Description"));
    $form->item->name
      ->set("label", t("Directory name"))
      ->set("error_messages", array(
          "no_slashes" => t("The directory name can't contain a \"/\""),
          "no_backslashes" => t("The directory name can't contain a \"\\\""),
          "no_trailing_period" => t("The directory name can't end in \".\""),
          "not_empty" => t("You must provide a directory name"),
          "max_length" => t("Your directory name is too long"),
          "conflict" => t("There is already a movie, photo or album with this name")
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
    Module::event("album_add_form", $parent, $form);

    // Load and validate the form.
    if ($form->sent()) {
      if ($form->load()->validate()) {
        // Passed - save item, run event, add to log, send message, then redirect to new item.
        $item->save();
        Module::event("album_add_form_completed", $item, $form);
        GalleryLog::success("content", t("Created an album"),
                            HTML::anchor($item->url(), t("view")));
        Message::success(t("Created album %album_title",
                           array("album_title" => HTML::purify($item->title))));

        if ($this->request->is_ajax()) {
          $this->response->json(array("result" => "success", "location" => $item->url()));
          return;
        } else {
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
      $view_theme = new View_Theme("required/page.html", "other", "item_add");
      $view_theme->page_title = $form->item->get("label");
      $view_theme->content = $form;
      $this->response->body($view_theme);
    }
  }

  /**
   * Edit an album.  This generates the form, validates it, adds the item, and returns a response.
   * This can be used as an ajax dialog (preferable) or a normal view.
   */
  public function action_edit() {
    $item_id = $this->request->arg(0, "digit");
    $item = ORM::factory("Item", $item_id);
    if (!$item->loaded() || !$item->is_album()) {
      // Item doesn't exist or isn't an album - fire a 400 Bad Request.
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
      ->add("slug", "input")
      ->add("sorting", "group");
    $form->item->sorting
      ->add("sort_column", "select")
      ->add("sort_order", "select");
    $form->buttons
      ->add("submit", "input|submit", t("Modify"));

    $form
      ->attr("id", "g-edit-album-form");
    $form->item
      ->set("label", t("Edit Album"));
    $form->item->title
      ->set("label", t("Title"))
      ->set("error_messages", array(
          "not_empty" => t("You must provide a title"),
          "max_length" => t("Your title is too long")
        ));
    $form->item->description
      ->set("label", t("Description"));
    $form->item->name
      ->set("label", t("Directory name"))
      ->set("error_messages", array(
          "no_slashes" => t("The directory name can't contain a \"/\""),
          "no_backslashes" => t("The directory name can't contain a \"\\\""),
          "no_trailing_period" => t("The directory name can't end in \".\""),
          "not_empty" => t("You must provide a directory name"),
          "max_length" => t("Your directory name is too long"),
          "conflict" => t("There is already a movie, photo or album with this name")
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
    $form->item->sorting
      ->set("label", t("Sort Order"));
    $form->item->sorting->sort_column
      ->set("label", t("Sort by"))
      ->set("opts", Album::get_sort_order_options());  // @todo: this function is poorly named...
    $form->item->sorting->sort_order
      ->set("label", t("Order"))
      ->set("opts", array(
          "ASC"  => t("Ascending"),
          "DESC" => t("Descending")
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
        GalleryLog::success("content", t("Updated album"),
                            HTML::anchor($item->url(), t("view")));
        Message::success(t("Saved album %album_title",
                           array("album_title" => HTML::purify($item->title))));

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
