<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

  var $rules = array(
    "name" => "required|length[1,64]");

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
    return ORM::factory("item")
      ->viewable()
      ->join("items_tags", "items.id", "items_tags.item_id")
      ->where("items_tags.tag_id", $this->id)
      ->count_all();
 }
}