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

/**
 * This is the API for handling albums.
 *
 * Note: by design, this class does not do any permission checking.
 */
class album_Core {
  /**
   * Create a new album.
   * @param integer $parent_id id of parent album
   * @param string  $name the name of this new album (it will become the directory name on disk)
   * @param integer $title the title of the new album
   * @param string  $description (optional) the longer description of this album
   * @param string  $slug (optional) the url component for this photo
   * @return Item_Model
   */
  static function create($parent, $name, $title, $description=null, $owner_id=null, $slug=null) {
    if (!$parent->loaded || !$parent->is_album()) {
      throw new Exception("@todo INVALID_PARENT");
    }

    if (strpos($name, "/")) {
      throw new Exception("@todo NAME_CANNOT_CONTAIN_SLASH");
    }

    // We don't allow trailing periods as a security measure
    // ref: http://dev.kohanaphp.com/issues/684
    if (rtrim($name, ".") != $name) {
      throw new Exception("@todo NAME_CANNOT_END_IN_PERIOD");
    }

    if (empty($slug)) {
      $slug = item::convert_filename_to_slug($name);
    }

    $album = ORM::factory("item");
    $album->type = "album";
    $album->title = $title;
    $album->description = $description;
    $album->name = $name;
    $album->owner_id = $owner_id;
    $album->thumb_dirty = 1;
    $album->resize_dirty = 1;
    $album->slug = $slug;
    $album->rand_key = ((float)mt_rand()) / (float)mt_getrandmax();
    $album->sort_column = "created";
    $album->sort_order = "ASC";

    // Randomize the name or slug if there's a conflict
    // @todo Improve this.  Random numbers are not user friendly
    while (ORM::factory("item")
           ->where("parent_id", $parent->id)
           ->open_paren()
           ->where("name", $album->name)
           ->orwhere("slug", $album->slug)
           ->close_paren()
           ->find()->id) {
      $rand = rand();
      $album->name = "{$name}-$rand";
      $album->slug = "{$slug}-$rand";
    }

    $album = $album->add_to_parent($parent);
    mkdir($album->file_path());
    mkdir(dirname($album->thumb_path()));
    mkdir(dirname($album->resize_path()));

    // @todo: publish this from inside Item_Model::save() when we refactor to the point where
    // there's only one save() happening here.
    module::event("item_created", $album);

    return $album;
  }

  static function get_add_form($parent) {
    $form = new Forge("albums/{$parent->id}", "", "post", array("id" => "gAddAlbumForm"));
    $group = $form->group("add_album")
      ->label(t("Add an album to %album_title", array("album_title" => $parent->title)));
    $group->input("title")->label(t("Title"));
    $group->textarea("description")->label(t("Description"));
    $group->input("name")->label(t("Directory name"))
      ->callback("item::validate_no_slashes")
      ->error_messages("no_slashes", t("The directory name can't contain the \"/\" character"));
    $group->input("slug")->label(t("Internet Address"))
      ->callback("item::validate_url_safe")
      ->error_messages(
        "not_url_safe",
        t("The internet address should contain only letters, numbers, hyphens and underscores"));
    $group->hidden("type")->value("album");
    $group->submit("")->value(t("Create"));
    $form->add_rules_from(ORM::factory("item"));
    $form->script("")
      ->url(url::abs_file("modules/gallery/js/albums_form_add.js"));
    return $form;
  }

  static function get_edit_form($parent) {
    $form = new Forge("albums/{$parent->id}", "", "post", array("id" => "gEditAlbumForm"));
    $form->hidden("_method")->value("put");
    $group = $form->group("edit_item")->label(t("Edit Album"));

    $group->input("title")->label(t("Title"))->value($parent->title);
    $group->textarea("description")->label(t("Description"))->value($parent->description);
    if ($parent->id != 1) {
      $group->input("dirname")->label(t("Directory Name"))->value($parent->name)
        ->rules("required")
        ->error_messages("name_conflict", t("There is already a photo or album with this name"))
        ->callback("item::validate_no_slashes")
        ->error_messages("no_slashes", t("The directory name can't contain a \"/\""))
        ->callback("item::validate_no_trailing_period")
        ->error_messages("no_trailing_period", t("The directory name can't end in \".\""));
      $group->input("slug")->label(t("Internet Address"))->value($parent->slug)
        ->error_messages(
          "slug_conflict", t("There is already a photo or album with this internet address"))
        ->callback("item::validate_url_safe")
        ->error_messages(
          "not_url_safe",
          t("The internet address should contain only letters, numbers, hyphens and underscores"));
    }

    $sort_order = $group->group("sort_order", array("id" => "gAlbumSortOrder"))
      ->label(t("Sort Order"));

    $sort_order->dropdown("column", array("id" => "gAlbumSortColumn"))
      ->label(t("Sort by"))
      ->options(album::get_sort_order_options())
      ->selected($parent->sort_column);
    $sort_order->dropdown("direction", array("id" => "gAlbumSortDirection"))
      ->label(t("Order"))
      ->options(array("ASC" => t("Ascending"),
                      "DESC" => t("Descending")))
      ->selected($parent->sort_order);

    module::event("item_edit_form", $parent, $form);

    $group = $form->group("buttons")->label("");
    $group->hidden("type")->value("album");
    $group->submit("")->value(t("Modify"));
    $form->add_rules_from(ORM::factory("item"));
    return $form;
  }

  /**
   * Return a structured set of all the possible sort orders.
   */
  static function get_sort_order_options() {
    return array("weight" => t("Manual"),
                 "captured" => t("Date captured"),
                 "created" => t("Date uploaded"),
                 "title" => t("Title"),
                 "updated" => t("Date modified"),
                 "view_count" => t("Number of views"),
                 "rand_key" => t("Random"));
  }
}
