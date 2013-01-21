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

/**
 * This is the API for handling albums.
 *
 * Note: by design, this class does not do any permission checking.
 */
class album_Core {

  static function get_add_form($parent) {
    $form = new Forge("albums/create/{$parent->id}", "", "post", array("id" => "g-add-album-form"));
    $group = $form->group("add_album")
      ->label(t("Add an album to %album_title", array("album_title" => $parent->title)));
    $group->input("title")->label(t("Title"))
      ->error_messages("required", t("You must provide a title"))
      ->error_messages("length", t("Your title is too long"));
    $group->textarea("description")->label(t("Description"));
    $group->input("name")->label(t("Directory name"))
      ->error_messages("no_slashes", t("The directory name can't contain the \"/\" character"))
      ->error_messages("required", t("You must provide a directory name"))
      ->error_messages("length", t("Your directory name is too long"))
      ->error_messages("conflict", t("There is already a movie, photo or album with this name"));
    $group->input("slug")->label(t("Internet Address"))
      ->error_messages(
        "reserved", t("This address is reserved and can't be used."))
      ->error_messages(
        "not_url_safe",
        t("The internet address should contain only letters, numbers, hyphens and underscores"))
      ->error_messages("required", t("You must provide an internet address"))
      ->error_messages("length", t("Your internet address is too long"));
    $group->hidden("type")->value("album");

    module::event("album_add_form", $parent, $form);

    $group->submit("")->value(t("Create"));
    $form->script("")
      ->url(url::abs_file("modules/gallery/js/albums_form_add.js"));

    return $form;
  }

  static function get_edit_form($parent) {
    $form = new Forge(
      "albums/update/{$parent->id}", "", "post", array("id" => "g-edit-album-form"));
    $form->hidden("from_id")->value($parent->id);
    $group = $form->group("edit_item")->label(t("Edit Album"));

    $group->input("title")->label(t("Title"))->value($parent->title)
        ->error_messages("required", t("You must provide a title"))
      ->error_messages("length", t("Your title is too long"));
    $group->textarea("description")->label(t("Description"))->value($parent->description);
    if ($parent->id != 1) {
      $group->input("name")->label(t("Directory Name"))->value($parent->name)
        ->error_messages("conflict", t("There is already a movie, photo or album with this name"))
        ->error_messages("no_slashes", t("The directory name can't contain a \"/\""))
        ->error_messages("no_trailing_period", t("The directory name can't end in \".\""))
        ->error_messages("required", t("You must provide a directory name"))
        ->error_messages("length", t("Your directory name is too long"));
      $group->input("slug")->label(t("Internet Address"))->value($parent->slug)
        ->error_messages(
          "conflict", t("There is already a movie, photo or album with this internet address"))
        ->error_messages(
          "reserved", t("This address is reserved and can't be used."))
        ->error_messages(
          "not_url_safe",
          t("The internet address should contain only letters, numbers, hyphens and underscores"))
        ->error_messages("required", t("You must provide an internet address"))
        ->error_messages("length", t("Your internet address is too long"));
    } else {
      $group->hidden("name")->value($parent->name);
      $group->hidden("slug")->value($parent->slug);
    }

    $sort_order = $group->group("sort_order", array("id" => "g-album-sort-order"))
      ->label(t("Sort Order"));

    $sort_order->dropdown("column", array("id" => "g-album-sort-column"))
      ->label(t("Sort by"))
      ->options(album::get_sort_order_options())
      ->selected($parent->sort_column);
    $sort_order->dropdown("direction", array("id" => "g-album-sort-direction"))
      ->label(t("Order"))
      ->options(array("ASC" => t("Ascending"),
                      "DESC" => t("Descending")))
      ->selected($parent->sort_order);

    module::event("item_edit_form", $parent, $form);

    $group = $form->group("buttons")->label("");
    $group->hidden("type")->value("album");
    $group->submit("")->value(t("Modify"));
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
                 "name" => t("File name"),
                 "updated" => t("Date modified"),
                 "view_count" => t("Number of views"),
                 "rand_key" => t("Random"));
  }
}
