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
class organize_Core {
  static function get_general_edit_form($item) {
    $generalPane = new Forge("organize/__FUNCTION__", "", "post",
                             array("id" => "gEditGeneral", "ref" => "general"));
    // In this case we know there is only 1 item, but in general we should loop
    // and create multiple hidden items.
    $generalPane->hidden("item[]")->value($item->id);
    $generalPane->input("title")->label(t("Title"))->value($item->title);
    $generalPane->textarea("description")->label(t("Description"))->value($item->description);
    $generalPane->input("dirname")->label(t("Path Name"))->value($item->name)
      ->callback("item::validate_no_slashes")
      ->error_messages("no_slashes", t("The directory name can't contain a \"/\""))
      ->callback("item::validate_no_trailing_period")
      ->error_messages("no_trailing_period", t("The directory name can't end in \".\""))
      ->callback("item::validate_no_name_conflict")
      ->error_messages("conflict", t("The path name is not unique"));

    return $generalPane;
  }

  static function get_sort_edit_form($item) {
    $sortPane = new Forge("organize/__FUNCTION__", "", "post",
                          array("id" => "gEditSort", "ref" => "sort"));
    $sortPane->hidden("item[]")->value($item->id);
    $sortPane->dropdown("column", array("id" => "gAlbumSortColumn"))
      ->label(t("Sort by"))
      ->options(array("weight" => t("Default"),
                      "captured" => t("Capture Date"),
                      "created" => t("Creation Date"),
                      "title" => t("Title"),
                      "updated" => t("Updated Date"),
                      "view_count" => t("Number of views"),
                      "rand_key" => t("Random")))
      ->selected($item->sort_column);
    $sortPane->dropdown("direction", array("id" => "gAlbumSortDirection"))
      ->label(t("Order"))
      ->options(array("ASC" => t("Ascending"),
                      "DESC" => t("Descending")))
      ->selected($item->sort_order);

    return $sortPane;
  }

  static function get_tag_form($itemids) {
    $tagPane = new Forge("organize/__FUNCTION__", "", "post",
                             array("id" => "gEditTags", "ref" => "edit_tags"));
    $tagPane->hidden("item")->value(implode("|", $itemids));
    $item_count = count($itemids);
    $ids = implode(", ", $itemids);
    $tags = Database::instance()->query(
      "SELECT t.name, COUNT(it.item_id) as count
         FROM {items_tags} it, {tags} t
        WHERE it.tag_id = t.id
          AND it.item_id in($ids)
       GROUP BY it.tag_id
       ORDER BY t.name ASC");
    $taglist = array();
    foreach ($tags as $tag) {
      $taglist[] = $tag->name . ($item_count > $tag->count ? "*" : "");
    }
    $taglist = implode("; ", $taglist);
    $tagPane->textarea("tags")->label(t("Tags"))->value($taglist);

    return $tagPane;
  }

}