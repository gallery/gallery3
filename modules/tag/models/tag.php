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

  protected $_children = array();

  var $rules = array(
    "name" => "required|length[4,32]");

  /**
   * Emulate the album method charactistics so that the tag looks like an album to the framework.
   */
  public function __call($function, $args) {
    if ($function == "children") {
      return $this->_get_tag_children($args[0], $args[1]);
    } else if ($function == "children_count") {
      return $this->count;
    } else if ($function == "parents") {
      // Need to return as an ORM_Iterator as opposed to just the model.
      return ORM::factory("item")
        ->where("id", 1)
        ->find_all();
    } else {
      return parent::__call($function, $args);
    }
  }

  /**
   * Emulate the album property charactistics so that the tag looks like an album to the framework.
   */
  public function __get($property) {
    if ($property == "title" || $property == "title_edit" || $property == "name_edit") {
      return $this->name;
    } else if ($property == "description_edit") {
      return "There are {$this->count} items tagged.";
    } else if ($property == "owner") {
      return null;
    } else {
      return parent::__get($property);
    }
  }

  /**
   * Get the item children. This code was borrowed from the ORM::__get($column) method and modified
   * to allow for the specification of the limit and offset.
   * @param int $limit
   * @param int $offset
   * @return ORM_Iterator
   */
  private function _get_tag_children($limit, $offset) {
    // Load the child model
    $model = ORM::factory(inflector::singular("items"));

    // Load JOIN info
    $join_table = $model->join_table($this->table_name);
    $join_col1  = $model->foreign_key(NULL, $join_table);
    $join_col2  = $model->foreign_key(TRUE);

    // one<>alias:many relationship
    return $model
      ->join($join_table, $join_col1, $join_col2)
      ->where($this->foreign_key(NULL, $join_table), $this->object[$this->primary_key])
      ->find_all($limit, $offset);
  }
}