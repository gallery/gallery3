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
class Gallery_Controller_Items extends Controller {
  /**
   * Show an item.  This finds the item by its URL and generates a view.  This is how all items
   * (albums, photos, and movies) get displayed in Gallery.
   */
  public function action_show() {
    // See if we got here via items/show/<id> - if so, do a 301 redirect to our canonical URL.
    if ($item_id = $this->request->arg_optional(0)) {
      $item = ORM::factory("Item", $item_id);
      if (!$item->loaded()) {
        throw HTTP_Exception::factory(404);
      }
      Access::required("view", $item);
      $this->redirect($item->abs_url(), 301);
    }

    // Find the item by its URL, check access, and increment the view count.
    $item_url = $this->request->param("item_url");
    if (empty($item_url)) {
      $item = Item::root();
    } else {
      $item = Item::find_by_relative_url($item_url);
      if (!$item->loaded()) {
        throw HTTP_Exception::factory(404);
      }
    }
    Access::required("view", $item);
    $item->increment_view_count();

    // Build the view.  Photos and movies are nearly identical, but albums are different.
    if ($item->is_album()) {
      $template = new View_Theme("required/page.html", "collection", "album");
      $template->content = new View("required/album.html");
      $template->set_global(array(
        "page_title" => null,
        "item" => $item,
        "children_query" => $item->children->viewable(),
        "breadcrumbs" => Breadcrumb::array_from_item_parents($item),
      ));
      $template->init_paginator();
      Item::set_display_context_callback("Controller_Items::get_display_context");
    } else {
      $template = new View_Theme("required/page.html", "item", $item->type);
      $template->content = new View("required/{$item->type}.html");
      $template->set_global(array(
        "item" => $item,
      ));
      $template->set_global(Item::get_display_context($item));
    }

    $this->response->body($template);
  }

  /**
   * Edit an item.  This generates the form, validates it, adds the item, and returns a response.
   * This can be used as an ajax dialog (preferable) or a normal view.
   */
  public function action_edit() {
    $item_id = $this->request->arg(0, "digit");
    $item = ORM::factory("Item", $item_id);
    if (!$item->loaded()) {
      throw HTTP_Exception::factory(404);
    }
    Access::required("view", $item);
    Access::required("edit", $item);

    // Get the from_id query parameter, which defaults to the edited item's id.
    $from_id = Arr::get($this->request->query(), "from_id", $item->id);

    // Build the form.
    $form = Formo::form()
      ->attr("id", "g-edit-item-form")
      ->add("from_id", "input|hidden", $from_id)
      ->add("item", "group")
      ->add("other", "group");
    $form->item
      ->set("label", Arr::get(array(
          "album" => t("Edit Album"),
          "photo" => t("Edit Photo"),
          "movie" => t("Edit Movie")
        ), $item->type))
      ->add("title", "input")
      ->add("description", "textarea")
      ->add("name", "input")
      ->add("slug", "input");
    $form->other
      ->add("submit", "input|submit", t("Modify"));

    // Add sorting options for albums.
    if ($item->is_album()) {
      $form->item
        ->add("sorting", "group");
      $form->item->sorting
        ->set("label", t("Sort Order"))
        ->add("sort_column", "select")
        ->add("sort_order", "select");
      $form->item->sorting->sort_column
        ->set("opts", static::get_sort_column_options());
      $form->item->sorting->sort_order
        ->set("opts", array(
            "ASC"  => t("Ascending"),
            "DESC" => t("Descending")
          ));
    }

    // Get the labels and error messages, link the ORM model, and call the form event.
    $form->item->orm("link", array("model" => $item));
    $form->item->set_var_fields("label", static::get_form_labels($item->type), false);
    $form->item->set_var_fields("error_messages", static::get_form_error_messages($item->type), false);
    Module::event("item_edit_form", $item, $form);

    // We can't edit the root item's name or slug.
    if ($item->is_root()) {
      $form->item->name
        ->attr("type", "hidden")
        ->add_rule("equals", array(":value", $item->name));
      $form->item->slug
        ->attr("type", "hidden")
        ->add_rule("equals", array(":value", $item->slug));
    }

    // Load and validate the form.
    if ($form->load()->validate()) {
      // Passed - save item, run event, add to log, send message, then redirect to new item.
      $item->save();
      Module::event("item_edit_form_completed", $item, $form);
      GalleryLog::success("content", Arr::get(array(
          "album" => t("Updated album"),
          "photo" => t("Updated photo"),
          "movie" => t("Updated movie")
        ), $item->type), HTML::anchor($item->url(), t("view")));
      Message::success(Arr::get(array(
        "album" => t("Saved album %album_title", array("album_title" => HTML::purify($item->title))),
        "photo" => t("Saved photo %photo_title", array("photo_title" => HTML::purify($item->title))),
        "movie" => t("Saved movie %movie_title", array("movie_title" => HTML::purify($item->title)))
      ), $item->type));

      // If from_id points to the item itself, redirect as the address may have changed.
      if ($form->from_id->val() == $item->id) {
        $form->set("response", $item->abs_url());
      }
    }

    // Merge the groups together for presentation purposes
    $form->merge_groups("other", "item");

    $this->response->ajax_form($form);
  }

  /**
   * Add a new album.  This generates the form, validates it, adds the item, and returns a response.
   * This can be used as an ajax dialog (preferable) or a normal view.
   */
  public function action_add_album() {
    $parent_id = $this->request->arg(0, "digit");
    $parent = ORM::factory("Item", $parent_id);
    if (!$parent->loaded() || !$parent->is_album()) {
      throw HTTP_Exception::factory(404);
    }
    Access::required("view", $parent);
    Access::required("add", $parent);

    // Build the item model.
    $item = ORM::factory("Item");
    $item->type = "album";
    $item->parent_id = $parent_id;

    // Build the form.
    $form = Formo::form()
      ->attr("id", "g-add-album-form")
      ->add_script_url("modules/gallery/assets/albums_form_add.js")
      ->add("item", "group")
      ->add("other", "group");
    $form->item
      ->set("label", t("Add an album to %album_title",
                       array("album_title" => HTML::purify($parent->title))))
      ->add("title", "input")
      ->add("description", "textarea")
      ->add("name", "input")
      ->add("slug", "input");
    $form->other
      ->add("submit", "input|submit", t("Create"));

    // Get the labels and error messages, link the ORM model, and call the form event.
    $form->item->orm("link", array("model" => $item));
    $form->item->set_var_fields("label", static::get_form_labels($item->type), false);
    $form->item->set_var_fields("error_messages", static::get_form_error_messages($item->type), false);
    Module::event("album_add_form", $parent, $form);

    // Load and validate the form.
    if ($form->load()->validate()) {
      // Passed - save item, run event, add to log, send message, then redirect to new item.
      $item->save();
      Module::event("album_add_form_completed", $item, $form);
      GalleryLog::success("content", t("Created an album"),
                          HTML::anchor($item->url(), t("view")));
      Message::success(t("Created album %album_title",
                         array("album_title" => HTML::purify($item->title))));

      $form->set("response", $item->abs_url());
    }

    // Merge the groups together for presentation purposes
    $form->merge_groups("other", "item");

    $this->response->ajax_form($form);
  }

  /**
   * Add a new item.  This generates the form, validates it, adds the item, and returns a response.
   * This can be used as an ajax dialog (preferable) or a normal view.
   */
  public function action_add() {
    $parent_id = $this->request->arg(0, "digit");
    $parent = ORM::factory("Item", $parent_id);
    if (!$parent->loaded() || !$parent->is_album()) {
      throw HTTP_Exception::factory(404);
    }
    Access::required("view", $parent);
    Access::required("add", $parent);

    // Build the item model.
    $item = ORM::factory("Item");
    $item->parent_id = $parent->id;

    // Build the form.
    $form = Formo::form()
      ->attr("id", "g-add-item-form")
      ->add("item", "group")
      ->add("other", "group");
    $form->item
      ->set("label", t("Add photos to %album_title",             // @todo: make this "photos/movies"
                       array("album_title" => HTML::purify($parent->title))))
      ->add("data_file", "file");
    $form->item->data_file
      ->set("label", t("File"))
      ->add_rule("not_empty", array(":value"),          t("You must select a file"))
      ->add_rule("Upload::type",
          array(":value", LegalFile::get_extensions()), t("The file's extension is invalid"));
    $form->other
      ->add("submit", "input|submit", t("Upload"));

    // Link the ORM model (using a callback, not the typical ORM link) and call the form event.
    $form->item->set("linked_orm_model", $item);
    $form->item->callback("pass", array("Controller_Items::item_add_callback"));
    Module::event("item_add_form", $parent, $form);

    // Load and validate the form.
    if ($form->load()->validate()) {
      Module::event("item_add_form_completed", $item, $form);
      GalleryLog::success("content", Arr::get(array(
        "photo" => t("Added a photo"),
        "movie" => t("Added a movie")
      ), $item->type), HTML::anchor($item->url(), t("view")));
    }

    // Merge the groups together for presentation purposes
    $form->merge_groups("other", "item");

    $this->response->ajax_form($form);
  }

  /**
   * Delete an item.  This generates the confirmation form, validates it,
   * deletes the item, and returns a response.
   */
  public function action_delete() {
    $item_id = $this->request->arg(0, "digit");
    $item = ORM::factory("Item", $item_id);
    Access::required("view", $item);
    Access::required("edit", $item);

    // Get the from_id query parameter, which defaults to the edited item's id.
    $from_id = Arr::get($this->request->query(), "from_id", $item->id);

    $form = Formo::form()
      ->attr("id", "g-delete-item-form")
      ->add("from_id", "input|hidden", $from_id)
      ->add("confirm", "group")
      ->add_script_text(
          '$("#g-delete-item-form").submit(function() {
            $("#g-delete-item-form input[type=submit]").gallery_show_loading();
          });'
        );  // @todo: make all dialogs do something like this automatically.
    $form->confirm
      ->set("label", t("Confirm Deletion"))
      ->html($item->is_album() ?
          t("Delete the album <b>%title</b>? All photos and movies in the album will also be deleted.",
            array("title" => HTML::purify($item->title))) :
          t("Are you sure you want to delete <b>%title</b>?",
            array("title" => HTML::purify($item->title)))
        )
      ->add("submit", "input|submit", t("Delete"));

    if ($form->load()->validate()) {
      $msg = Arr::get(array(
        "album" => t("Deleted album <b>%title</b>", array("title" => HTML::purify($item->title))),
        "photo" => t("Deleted photo <b>%title</b>", array("title" => HTML::purify($item->title))),
        "movie" => t("Deleted movie <b>%title</b>", array("title" => HTML::purify($item->title)))
      ), $item->type);

      // If we just deleted the item we were viewing, we'll need to redirect to the parent.
      if ($form->from_id->val() == $item->id) {
        $form->set("response", $item->parent->abs_url());
      }

      if ($item->is_album()) {
        // Album delete will trigger deletes for all children.  Do this in a batch so that we can
        // be smart about notifications, album cover updates, etc.
        Batch::start();
        $item->delete();
        Batch::stop();
      } else {
        $item->delete();
      }

      Message::success($msg);
    }

    $this->response->ajax_form($form);
  }

  /**
   * Make an item the album cover.  This checks access, makes it the cover, and then reloads.
   */
  public function action_make_album_cover() {
    Access::verify_csrf();

    $item_id = $this->request->arg(0, "digit");
    $item = ORM::factory("Item", $item_id);
    Access::required("view", $item);
    Access::required("view", $item->parent);
    Access::required("edit", $item->parent);

    $msg = t("Made <b>%title</b> this album's cover", array("title" => HTML::purify($item->title)));

    Item::make_album_cover($item);
    Message::success($msg);

    $this->response->json(array("result" => "success", "reload" => 1));
  }

  /**
   * Rotate an item.  This checks access, rotates it, and then sends an ajax response with the
   * new image dimensions (no reload required).
   */
  public function action_rotate() {
    Access::verify_csrf();

    $item_id = $this->request->arg(0, "digit");
    $dir = $this->request->arg(1, "alpha");
    $item = ORM::factory("Item", $item_id);
    Access::required("view", $item);
    Access::required("edit", $item);

    switch($dir) {
      case "ccw": $degrees = -90; break;
      case "cw":  $degrees =  90; break;
      default:    throw HTTP_Exception::factory(400);
    }

    // Get the from_id query parameter, which defaults to the edited item's id.
    $from_id = Arr::get($this->request->query(), "from_id", $item->id);

    $tmpfile = System::temp_filename("rotate", pathinfo($item->file_path(), PATHINFO_EXTENSION));
    GalleryGraphics::rotate($item->file_path(), $tmpfile, array("degrees" => $degrees), $item);
    $item->set_data_file($tmpfile);
    $item->save();

    // We don't need to refresh the page - just tell js what the new dimensions are.
    if ($from_id == $item->id) {
      $this->response->json(
        array("src" => $item->resize_url(),
              "width" => $item->resize_width,
              "height" => $item->resize_height));
    } else {
      $this->response->json(
        array("src" => $item->thumb_url(),
              "width" => $item->thumb_width,
              "height" => $item->thumb_height));
    }
  }

  /**
   * Return the width/height dimensions for the given item as a json response.
   */
  public function action_dimensions() {
    $id = $this->request->arg(0, "digit");
    $item = ORM::factory("Item", $id);
    Access::required("view", $item);
    $this->response->json(array(
      "thumb"  => array((int)$item->thumb_width,  (int)$item->thumb_height),
      "resize" => array((int)$item->resize_width, (int)$item->resize_height),
      "full"   => array((int)$item->width,        (int)$item->height)
    ));
  }

  /**
   * Display context callback for albums.
   *
   * @see  Item::set_display_context_callback()
   * @see  Item::get_display_context_callback()
   * @see  Item::clear_display_context_callback()
   * @see  Controller_Search::get_display_context()
   * @see  Controller_Tags::get_display_context()
   */
  public static function get_display_context($item) {
    $where = array(array("type", "!=", "album"));
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
                 "siblings_callback" => array("Controller_Items::get_siblings", array($item)),
                 "parents" => $item->parents->find_all()->as_array(),
                 "breadcrumbs" => Breadcrumb::array_from_item_parents($item));
  }

  /**
   * Siblings callback for albums.
   *
   * @see  View_Theme::siblings()
   * @see  Controller_Search::get_siblings()
   */
  public static function get_siblings($item, $limit=null, $offset=null) {
    // @todo consider creating Model_Item::siblings() if we use this more broadly.
    return $item->parent->children->viewable()->limit($limit)->offset($offset)->find_all();
  }

  /**
   * Item add callback to process file uploads.  This is used in the item add form.
   */
  public static function item_add_callback($item_group) {
    // Process the data file.
    $file_array = $item_group->data_file->val();
    $path = Upload::save($file_array);
    System::delete_later($path);

    // Add to the item model.
    $item = $item_group->get("linked_orm_model");
    $item->name = $file_array["name"];
    $item->set_data_file($path);
    $item->type = LegalFile::get_photo_extensions(pathinfo($item->name, PATHINFO_EXTENSION)) ?
      "photo" : "movie";

    // Try to save the item model, and add an error if it failed.
    try {
      $item->save();
      return true;
    } catch (ORM_Validation_Exception $e) {
      Log::instance()->add(Log::ERROR, "Validation errors: " . print_r($e->errors(), 1));
      $item_group->data_file->error(t("Invalid upload"));
    }
  }

  /**
   * Get form error messages for the item group.  This is a helper function for the edit/add forms.
   * @see Controller_Admin_Tags::action_edit_slug(), which uses some of the same messages.
   */
  public static function get_form_error_messages($type) {
    // Define all of the error messages.
    $error_messages = array(
      "title" => array(
        "all" => array(
          "not_empty"  => t("You must provide a title"),
          "max_length" => t("Your title is too long")
        )
      ),
      "name" => array(
        "album" => array(
          "no_slashes"          => t("The directory name can't contain a \"/\""),
          "no_backslashes"      => t("The directory name can't contain a \"\\\""),
          "no_trailing_period"  => t("The directory name can't end in \".\""),
          "not_empty"           => t("You must provide a directory name"),
          "max_length"          => t("Your directory name is too long")
        ),
        "photo" => array(
          "no_slashes"          => t("The photo name can't contain a \"/\""),
          "no_backslashes"      => t("The photo name can't contain a \"\\\""),
          "no_trailing_period"  => t("The photo name can't end in \".\""),
          "not_empty"           => t("You must provide a photo file name"),
          "max_length"          => t("Your photo file name is too long"),
          "data_file_extension" => t("You cannot change the photo file extension")
        ),
        "movie" => array(
          "no_slashes"          => t("The movie name can't contain a \"/\""),
          "no_backslashes"      => t("The movie name can't contain a \"\\\""),
          "no_trailing_period"  => t("The movie name can't end in \".\""),
          "not_empty"           => t("You must provide a movie file name"),
          "max_length"          => t("Your movie file name is too long"),
          "data_file_extension" => t("You cannot change the movie file extension")
        ),
        "all" => array(
          "conflict"            => t("There is already a movie, photo or album with this name")
        )
      ),
      "slug" => array(
        "all" => array(
          "conflict"     => t("There is already a movie, photo or album with this internet address"),
          "reserved"     => t("This address is reserved and can't be used."),
          "not_url_safe" => t("The internet address should contain only letters, numbers, hyphens and underscores"),
          "not_empty"    => t("You must provide an internet address"),
          "max_length"   => t("Your internet address is too long")
        )
      )
    );

    // Flatten the array by type.
    foreach ($error_messages as $key => &$value) {
      $value = array_merge_recursive(
        Arr::get($value, $type, array()),
        Arr::get($value, "all", array())
      );
    }
    return $error_messages;
  }

  /**
   * Get form labels for the item group.  This is a helper function for the edit/add forms.
   * @see Controller_Admin_Tags::action_edit_slug(), which uses some of the same labels.
   */
  public static function get_form_labels($type) {
    // Define all of the labels.
    $labels = array(
      "title" => array(
        "all"   => t("Title")
      ),
      "description" => array(
        "all"   => t("Description")
      ),
      "name" => array(
        "album" => t("Directory name"),
        "photo" => t("Filename"),
        "movie" => t("Filename")
      ),
      "slug" => array(
        "all"   => t("Internet Address")
      ),
      "sort_column" => array(
        "album" => t("Sort by")
      ),
      "sort_order" => array(
        "album" => t("Order")
      )
    );

    // Flatten the array by type.
    foreach ($labels as $key => &$value) {
      $value = Arr::get($value, $type, Arr::get($value, "all", ""));
    }
    return $labels;
  }

  /**
   * Return a structured set of all the possible sort columns.
   */
  public static function get_sort_column_options() {
    return array("weight"     => t("Manual"),
                 "captured"   => t("Date captured"),
                 "created"    => t("Date uploaded"),
                 "title"      => t("Title"),
                 "name"       => t("File name"),
                 "updated"    => t("Date modified"),
                 "view_count" => t("Number of views"),
                 "rand_key"   => t("Random"));
  }
}
