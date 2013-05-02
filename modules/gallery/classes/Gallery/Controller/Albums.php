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
    Access::required("view", $parent);
    Access::required("add", $parent);

    // Build the form.
    $form = Formo::form()
      ->add("item", "group");
    $form->item
      ->add("title", "input")
      ->add("description", "textarea")
      ->add("name", "input")
      ->add("slug", "input")
      ->add("type", "input|hidden", "album")
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
          "name_conflict" => t("There is already a movie, photo or album with this name")
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

    Module::event("album_add_form", $parent, $form);

    // Load and validate the form.
    if ($form->load()->validate()) {
      // Set a default title if none given.
      // @todo: consider moving this to the item model.
      if (!$form->item->title->val()) {
        $form->item->title->val($form->item->name->val());
      }

      // Build the item model.
      $item = ORM::factory("Item");
      $item->parent_id = $parent_id;
      $form->item->orm("save", array("model" => $item));

      if ($form->item->get("orm_passed")) {
        // Passed - run event, add to log, send message, then redirect to new item.
        Module::event("album_add_form_completed", $item, $form);
        GalleryLog::success("content", "Created an album",
                            HTML::anchor("albums/$item->id", "view album"));
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
      $view_theme = new View_Theme("required/page.html", "other", "albums_add");
      $view_theme->page_title = $form->item->get("label");
      $view_theme->content = $form;
      $this->response->body($view_theme);
    }
  }

  public function action_update() {
    $album_id = $this->request->arg(0, "digit");
    Access::verify_csrf();
    $album = ORM::factory("Item", $album_id);
    Access::required("view", $album);
    Access::required("edit", $album);

    $form = $this->get_edit_form($album);
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
        $this->response->json(array("result" => "success", "location" => $album->url()));
      } else {
        // Stay on the same page
        $this->response->json(array("result" => "success"));
      }
    } else {
      $this->response->json(array("result" => "error", "html" => (string)$form));
    }
  }

  public function action_form_edit() {
    $album_id = $this->request->arg(0, "digit");
    $album = ORM::factory("Item", $album_id);
    Access::required("view", $album);
    Access::required("edit", $album);

    $this->response->body($this->get_edit_form($album));
  }

  public function get_edit_form($parent) {
    $form = new Forge(
      "albums/update/{$parent->id}", "", "post", array("id" => "g-edit-album-form"));
    $form->hidden("from_id")->value($parent->id);
    $group = $form->group("edit_item")->label(t("Edit Album"));

    $group->input("title")->label(t("Title"))->value($parent->title)
      ->error_messages("not_empty", t("You must provide a title"))
      ->error_messages("max_length", t("Your title is too long"));
    $group->textarea("description")->label(t("Description"))->value($parent->description);
    if ($parent->id != 1) {
      $group->input("name")->label(t("Directory Name"))->value($parent->name)
        ->error_messages("name_conflict", t("There is already a movie, photo or album with this name"))
        ->error_messages("no_slashes", t("The directory name can't contain a \"/\""))
        ->error_messages("no_backslashes", t("The directory name can't contain a \"\\\""))
        ->error_messages("no_trailing_period", t("The directory name can't end in \".\""))
        ->error_messages("not_empty", t("You must provide a directory name"))
        ->error_messages("max_length", t("Your directory name is too long"));
      $group->input("slug")->label(t("Internet Address"))->value($parent->slug)
        ->error_messages(
          "conflict", t("There is already a movie, photo or album with this internet address"))
        ->error_messages(
          "reserved", t("This address is reserved and can't be used."))
        ->error_messages(
          "not_url_safe",
          t("The internet address should contain only letters, numbers, hyphens and underscores"))
        ->error_messages("not_empty", t("You must provide an internet address"))
        ->error_messages("max_length", t("Your internet address is too long"));
    } else {
      $group->hidden("name")->value($parent->name);
      $group->hidden("slug")->value($parent->slug);
    }

    $sort_order = $group->group("sort_order", array("id" => "g-album-sort-order"))
      ->label(t("Sort Order"));

    $sort_order->dropdown("column", array("id" => "g-album-sort-column"))
      ->label(t("Sort by"))
      ->options(Album::get_sort_order_options())
      ->selected($parent->sort_column);
    $sort_order->dropdown("direction", array("id" => "g-album-sort-direction"))
      ->label(t("Order"))
      ->options(array("ASC" => t("Ascending"),
                      "DESC" => t("Descending")))
      ->selected($parent->sort_order);

    Module::event("item_edit_form", $parent, $form);

    $group = $form->group("buttons")->label("");
    $group->hidden("type")->value("album");
    $group->submit("")->value(t("Modify"));
    return $form;
  }
}
