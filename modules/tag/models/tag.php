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
class Tag_Model extends ORM {
  protected $has_and_belongs_to_many = array("items");

  /**
   * Return all viewable items associated with this tag.
   * @param integer  $limit  number of rows to limit result to
   * @param integer  $offset offset in result to start returning rows from
   * @param string   $type   the type of item (album, photo)
   * @return ORM_Iterator
   */
  public function items($limit=null, $offset=0, $type=null) {
    $model = ORM::factory("item")
      ->viewable()
      ->join("items_tags", "items.id", "items_tags.item_id")
      ->where("items_tags.tag_id", $this->id);
    if ($type) {
      $model->where("items.type", $type);
    }
    return $model->find_all($limit, $offset);
  }

  /**
   * Return the count of all viewable items associated with this tag.
   * @param string   $type   the type of item (album, photo)
   * @return integer
   */
  public function items_count($type=null) {
    $model = ORM::factory("item")
      ->viewable()
      ->join("items_tags", "items.id", "items_tags.item_id")
      ->where("items_tags.tag_id", $this->id);

    if ($type) {
      $model->where("items.type", $type);
    }
    return $model->count_all();
  }

  /**
   * Overload ORM::save() to trigger an item_related_update event for all items that are related
   * to this tag.  Since items can be added or removed as part of the save, we need to trigger an
   * event for the union of all related items before and after the save.
   */
  public function save() {
    $db = Database::instance();
    $related_item_ids = array();
    foreach ($db->getwhere("items_tags", array("tag_id" => $this->id)) as $row) {
      $related_item_ids[$row->item_id] = 1;
    }

    $result = parent::save();

    foreach ($db->getwhere("items_tags", array("tag_id" => $this->id)) as $row) {
      $related_item_ids[$row->item_id] = 1;
    }

    if ($related_item_ids) {
      foreach (ORM::factory("item")->in("id", array_keys($related_item_ids))->find_all() as $item) {
        module::event("item_related_update", $item);
      }
    }

    return $result;
  }

  /**
   * Overload ORM::delete() to trigger an item_related_update event for all items that are
   * related to this tag.
   */
  public function delete() {
    $related_item_ids = array();
    $db = Database::Instance();
    foreach ($db->getwhere("items_tags", array("tag_id" => $this->id)) as $row) {
      $related_item_ids[$row->item_id] = 1;
    }

    $result = parent::delete();

    if ($related_item_ids) {
      foreach (ORM::factory("item")->in("id", array_keys($related_item_ids))->find_all() as $item) {
        module::event("item_related_update", $item);
      }
    }
    return $result;
  }
}