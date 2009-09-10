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
class Organize_Controller extends Controller {
  function dialog($album_id) {
    $album = ORM::factory("item", $album_id);
    access::required("view", $album);
    access::required("edit", $album);

    $v = new View("organize_dialog.html");
    $v->album = $album;
    $v->album_tree = self::_expanded_tree(ORM::factory("item", 1), $album);
    $v->micro_thumb_grid = self::_get_micro_thumb_grid($album, 0);
    print $v;
  }

  function album($album_id, $offset) {
    $album = ORM::factory("item", $album_id);
    access::required("view", $album);
    access::required("edit", $album);

    print json_encode(
      array("grid" => self::_get_micro_thumb_grid($album, $offset)->__toString(),
            "sort_column" => $album->sort_column,
            "sort_order" => $album->sort_order));
  }

  function move_to($target_album_id) {
    access::verify_csrf();

    $target_album = ORM::factory("item", $target_album_id);
    foreach ($this->input->post("source_ids") as $source_id) {
      $source = ORM::factory("item", $source_id);
      if (!$source->contains($target_album)) {
        item::move($source, $target_album);
      }
    }

    print json_encode(
      array("tree" => self::_expanded_tree(ORM::factory("item", 1), $album)->__toString(),
            "grid" => self::_get_micro_thumb_grid($album, 0)->__toString()));
  }

  function rearrange($target_id, $before_or_after) {
    access::verify_csrf();

    $target = ORM::factory("item", $target_id);
    $album = $target->parent();
    access::required("view", $album);
    access::required("edit", $album);

    $source_ids = $this->input->post("source_ids", array());

    if ($album->sort_column != "weight") {
      $i = 0;
      foreach ($album->children() as $child) {
        // Do this directly in the database to avoid sending notifications
        Database::Instance()->update("items", array("weight" => ++$i), array("id" => $child->id));
      }
      $album->sort_column = "weight";
      $album->sort_order = "ASC";
      $album->save();
      $target->reload();
    }

    // Find the insertion point
    $target_weight = $target->weight;
    if ($before_or_after == "after") {
      $target_weight++;
    }

    // Make a hole
    $count = count($source_ids);
    Database::Instance()->query(
      "UPDATE {items} " .
      "SET `weight` = `weight` + $count " .
      "WHERE `weight` >= $target_weight AND `parent_id` = {$album->id}");

    // Insert source items into the hole
    foreach ($source_ids as $source_id) {
      Database::Instance()->update(
        "items", array("weight" => $target_weight++), array("id" => $source_id));
    }

    module::event("album_rearrange", $album);

    print json_encode(
      array("grid" => self::_get_micro_thumb_grid($album, 0)->__toString(),
            "sort_column" => $album->sort_column,
            "sort_order" => $album->sort_order));
  }

  function sort_order($album_id, $col, $dir) {
    access::verify_csrf();

    $album = ORM::factory("item", $album_id);
    access::required("view", $album);
    access::required("edit", $album);

    $options = album::get_sort_order_options();
    if (!isset($options[$col])) {
      return;
    }

    $album->sort_column = $col;
    $album->sort_order = $dir;
    $album->save();

    print json_encode(
      array("grid" => self::_get_micro_thumb_grid($album, 0)->__toString(),
            "sort_column" => $album->sort_column,
            "sort_order" => $album->sort_order));
  }

  private static function _get_micro_thumb_grid($album, $offset) {
    $v = new View("organize_thumb_grid.html");
    $v->album = $album;
    $v->offset = $offset;
    return $v;
  }

  public function tree($album_id) {
    $album = ORM::factory("item", $album_id);
    access::required("view", $album);

    print self::_expanded_tree($album);
  }

  /**
   * Create an HTML representation of the tree from the root down to the selected album.  We only
   * include albums along the descendant hierarchy that includes the selected album, and the
   * immediate child albums.
   */
  private static function _expanded_tree($root, $selected_album=null) {
    $v = new View("organize_tree.html");
    $v->album = $root;
    $v->selected = $selected_album;
    return $v;
  }
}
