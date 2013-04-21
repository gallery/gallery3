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
class Tag_Model_Tag extends ORM {
  public function __construct($id=null) {
    parent::__construct($id);

    if (!$this->loaded()) {
      // Set reasonable defaults
      $this->count = 0;
    }
  }

  /**
   * Return all viewable items associated with this tag.
   * @param integer  $limit   number of rows to limit result to
   * @param integer  $offset  offset in result to start returning rows from
   * @param string   $where   an array of arrays, each compatible with ORM::where()
   * @return Database_Result
   */
  public function items($limit=null, $offset=null, $where=array()) {
    if (is_scalar($where)) {
      // backwards compatibility
      $where = array(array("item.type", "=", $where));
    }
    return $this->items
      ->viewable()
      ->merge_where($where)
      ->order_by("item.id")
      ->limit($limit)->offset($offset)->find_all();
  }

  /**
   * Return the count of all viewable items associated with this tag.
   * @param string   $where   an array of arrays, each compatible with ORM::where()
   * @return integer
   */
  public function items_count($where=array()) {
    if (is_scalar($where)) {
      // backwards compatibility
      $where = array(array("item.type", "=", $where));
    }
    return $this->items
      ->viewable()
      ->merge_where($where)
      ->count_all();
  }

  /**
   * Overload ORM::save() to trigger an item_related_update event for all items that are related
   * to this tag and to combine duplicate tags.
   */
  public function save(Validation $validation=null) {
    // Check to see if another tag exists with the same name
    $duplicate_tag = ORM::factory("Tag")
      ->where("name", "=", $this->name)
      ->where("id", "!=", $this->id)
      ->find();
    if ($duplicate_tag->loaded()) {
      // If so, tag its items with this tag so as to merge it
      foreach ($duplicate_tag->items->find_all() as $item) {
        // Add the item to the tag without adding it to changed_through.
        $this->add("items", $item, false);
      }

      // ... and remove the duplicate tag
      $duplicate_tag->delete();
    }

    // Revise the count
    $this->count = $this->items->count_all();

    // If the tag name has changed, all related items are considered changed, too.
    if ($this->changed("name")) {
      $changed_items = $this->items->find_all();
    } else {
      $changed_items = $this->changed_through("items");
    }

    parent::save($validation);

    foreach ($changed_items as $item) {
      Module::event("item_related_update", $item);
    }

    return $this;
  }

  /**
   * Overload ORM::delete() to trigger an item_related_update event for all items that are
   * related to this tag.
   */
  public function delete() {
    $items = $this->items->find_all();
    parent::delete();
    foreach ($items as $item) {
      Module::event("item_related_update", $item);
    }

    return $this;
  }

  /**
   * Return the server-relative url to this item, eg:
   *   /gallery3/index.php/tags/35/Bob
   *
   * @param string $query the query string (eg "page=3")
   */
  public function url($query=null) {
    $url = URL::site("tag/{$this->id}/" . urlencode($this->name));
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
    $url = URL::abs_site("tag/{$this->id}/" . urlencode($this->name));
    if ($query) {
      $url .= "?$query";
    }
    return $url;
  }
}
