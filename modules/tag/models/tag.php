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
    "name" => "required|length[4,32]");

  /**
   * Return all items associated with this tag.
   * @param integer  $limit  number of rows to limit result to
   * @param integer  $offset offset in result to start returning rows from
   * @return ORM_Iterator
   */
  public function items($limit=null, $offset=0) {
    return ORM::factory("item")
      ->join("items_tags", "items.id", "items_tags.item_id")
      ->where("items_tags.tag_id", $this->id)
      ->find_all($limit, $offset);
 }
}