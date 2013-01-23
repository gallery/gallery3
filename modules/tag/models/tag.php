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
class Tag_Model_Core extends ORM {
  protected $has_and_belongs_to_many = array("items");

  public function __construct($id=null) {
    parent::__construct($id);

    if (!$this->loaded()) {
      // Set reasonable defaults
      $this->count = 0;
    }
  }

  /**
   * Return all viewable items associated with this tag.
   * @param integer  $limit  number of rows to limit result to
   * @param integer  $offset offset in result to start returning rows from
   * @param string   $where   an array of arrays, each compatible with ORM::where()
   * @return ORM_Iterator
   */
  public function items($limit=null, $offset=null, $where=array()) {
    if (is_scalar($where)) {
      // backwards compatibility
      $where = array(array("items.type", "=", $where));
    }
    return ORM::factory("item")
      ->viewable()
      ->join("items_tags", "items.id", "items_tags.item_id")
      ->where("items_tags.tag_id", "=", $this->id)
      ->merge_where($where)
      ->order_by("items.id")
      ->find_all($limit, $offset);
  }

  /**
   * Return the count of all viewable items associated with this tag.
   * @param string   $where   an array of arrays, each compatible with ORM::where()
   * @return integer
   */
  public function items_count($where=array()) {
    if (is_scalar($where)) {
      // backwards compatibility
      $where = array(array("items.type", "=", $where));
    }
    return $model = ORM::factory("item")
      ->viewable()
      ->join("items_tags", "items.id", "items_tags.item_id")
      ->where("items_tags.tag_id", "=", $this->id)
      ->merge_where($where)
      ->count_all();
  }

  /**
   * Overload ORM::save() to trigger an item_related_update event for all items that are related
   * to this tag.
   */
  public function save() {
    // Check to see if another tag exists with the same name
    $duplicate_tag = ORM::factory("tag")
      ->where("name", "=", $this->name)
      ->where("id", "!=", $this->id)
      ->find();
    if ($duplicate_tag->loaded()) {
      // If so, tag its items with this tag so as to merge it
      $duplicate_tag_items = ORM::factory("item")
        ->join("items_tags", "items.id", "items_tags.item_id")
        ->where("items_tags.tag_id", "=", $duplicate_tag->id)
        ->find_all();
      foreach ($duplicate_tag_items as $item) {
        $this->add($item);
      }

      // ... and remove the duplicate tag
      $duplicate_tag->delete();
    }

    if (isset($this->object_relations["items"])) {
      $added = array_diff($this->changed_relations["items"], $this->object_relations["items"]);
      $removed = array_diff($this->object_relations["items"], $this->changed_relations["items"]);
      if (isset($this->changed_relations["items"])) {
        $changed = array_merge($added, $removed);
      }
      $this->count = count($this->object_relations["items"]) + count($added) - count($removed);
    }

    $result = parent::save();

    if (!empty($changed)) {
      foreach (ORM::factory("item")->where("id", "IN", $changed)->find_all() as $item) {
        module::event("item_related_update", $item);
      }
    }

    return $result;
  }

  /**
   * Overload ORM::delete() to trigger an item_related_update event for all items that are
   * related to this tag, and delete all items_tags relationships.
   */
  public function delete($ignored_id=null) {
    $related_item_ids = array();
    foreach (db::build()
             ->select("item_id")
             ->from("items_tags")
             ->where("tag_id", "=", $this->id)
             ->execute() as $row) {
      $related_item_ids[$row->item_id] = 1;
    }

    db::build()->delete("items_tags")->where("tag_id", "=", $this->id)->execute();
    $result = parent::delete();

    if ($related_item_ids) {
      foreach (ORM::factory("item")
               ->where("id", "IN", array_keys($related_item_ids))
               ->find_all() as $item) {
        module::event("item_related_update", $item);
      }
    }
    return $result;
  }

  /**
   * Return the server-relative url to this item, eg:
   *   /gallery3/index.php/tags/35/Bob
   *
   * @param string $query the query string (eg "page=3")
   */
  public function url($query=null) {
    $url = url::site("tag/{$this->id}/" . urlencode($this->name));
    if ($query) {
      $url .= "?$query";
    }
    return $url;
  }

  /**
   * Return the full url to this item, eg:
   *   http://example.com/gallery3/index.php/tags/35/Bob
   *
   * @param string $query the query string (eg "page=3")
   */
  public function abs_url($query=null) {
    $url = url::abs_site("tag/{$this->id}/" . urlencode($this->name));
    if ($query) {
      $url .= "?$query";
    }
    return $url;
  }
}
