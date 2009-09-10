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
 * Implement Modified Preorder Tree Traversal on top of ORM.
 *
 * MPTT is an efficient way to store and retrieve hierarchical data in a single database table.
 * For a good description, read http://www.sitepoint.com/article/hierarchical-data-database/3/
 *
 * This code was heavily influenced by code from:
 *  - http://code.google.com/p/kohana-mptt/
 *  - http://code.google.com/p/kohana-mptt/wiki/Documentation
 *  - http://code.google.com/p/s7ncms/source/browse/trunk/modules/s7ncms/libraries/ORM_MPTT.php
 *
 * Unfortunately that code was not ready for production and I did not want to absorb their code
 * and licensing issues so I've reimplemented just the features that we need.
 */
class ORM_MPTT_Core extends ORM {
  private $model_name = null;

  function __construct($id=null) {
    parent::__construct($id);
    $this->model_name = inflector::singular($this->table_name);
  }

  /**
   * Add this node as a child of the parent provided.
   *
   * @chainable
   * @param integer $parent_id the id of the parent node
   * @return ORM
   */
  function add_to_parent($parent) {
    $this->lock();

    try {
      // Make a hole in the parent for this new item
      $this->db->query(
        "UPDATE {{$this->table_name}} SET `left_ptr` = `left_ptr` + 2 WHERE `left_ptr` >= {$parent->right_ptr}");
      $this->db->query(
        "UPDATE {{$this->table_name}} SET `right_ptr` = `right_ptr` + 2 WHERE `right_ptr` >= {$parent->right_ptr}");
      $parent->right_ptr += 2;

      // Insert this item into the hole
      $this->left_ptr = $parent->right_ptr - 2;
      $this->right_ptr = $parent->right_ptr - 1;
      $this->parent_id = $parent->id;
      $this->level = $parent->level + 1;
      $this->save();
      $parent->reload();
    } catch (Exception $e) {
      $this->unlock();
      throw $e;
    }

    $this->unlock();
    return $this;
  }

  /**
   * Delete this node and all of its children.
   */
  public function delete() {
    $children = $this->children();
    if ($children) {
      foreach ($this->children() as $item) {
        // Deleting children affects the MPTT tree, so we have to reload each child before we
        // delete it so that we have current left_ptr/right_ptr pointers.  This is inefficient.
        // @todo load each child once, not twice.
        $item->reload()->delete();
      }

      // Deleting children has affected this item
      $this->reload();
    }

    $this->lock();
    try {
      $this->db->query(
        "UPDATE {{$this->table_name}} SET `left_ptr` = `left_ptr` - 2 WHERE `left_ptr` > {$this->right_ptr}");
      $this->db->query(
        "UPDATE {{$this->table_name}} SET `right_ptr` = `right_ptr` - 2 WHERE `right_ptr` > {$this->right_ptr}");
    } catch (Exception $e) {
      $this->unlock();
      throw $e;
    }

    $this->unlock();
    parent::delete();
  }

  /**
   * Return true if the target is descendant of this item.
   * @param ORM $target
   * @return boolean
   */
  function contains($target) {
    return ($this->left_ptr <= $target->left_ptr && $this->right_ptr >= $target->right_ptr);
  }

  /**
   * Return the parent of this node
   *
   * @return ORM
   */
  function parent() {
    if (!$this->parent_id) {
      return null;
    }
    return model_cache::get($this->model_name, $this->parent_id);
  }

  /**
   * Return all the parents of this node, in order from root to this node's immediate parent.
   *
   * @return array ORM
   */
  function parents() {
    return $this
      ->where("`left_ptr` <= {$this->left_ptr}")
      ->where("`right_ptr` >= {$this->right_ptr}")
      ->where("id <> {$this->id}")
      ->orderby("left_ptr", "ASC")
      ->find_all();
  }

  /**
   * Return all of the children of this node, ordered by id.
   *
   * @chainable
   * @param   integer  SQL limit
   * @param   integer  SQL offset
   * @param   array    additional where clauses
   * @param   array    orderby
   * @return array ORM
   */
  function children($limit=null, $offset=0, $where=array(), $orderby=array("id" => "ASC")) {
    return $this
      ->where("parent_id", $this->id)
      ->where($where)
      ->orderby($orderby)
      ->find_all($limit, $offset);
  }

  /**
   * Return all of the children of this node, ordered by id.
   *
   * @chainable
   * @param   array    additional where clauses
   * @return array ORM
   */
  function children_count($where=array()) {
    return $this
      ->where($where)
      ->where("parent_id", $this->id)
      ->count_all();
  }

  /**
   * Return all of the decendents of the specified type, ordered by id.
   *
   * @param   integer  SQL limit
   * @param   integer  SQL offset
   * @param   array    additional where clauses
   * @param   array    orderby
   * @return object ORM_Iterator
   */
  function descendants($limit=null, $offset=0, $where=array(), $orderby=array("id" => "ASC")) {
    return $this
      ->where("left_ptr >", $this->left_ptr)
      ->where("right_ptr <=", $this->right_ptr)
      ->where($where)
      ->orderby($orderby)
      ->find_all($limit, $offset);
  }

  /**
   * Return the count of all the children of the specified type.
   *
   * @param    array    additional where clauses
   * @return   integer  child count
   */
  function descendants_count($where=array()) {
    return $this
      ->where("left_ptr >", $this->left_ptr)
      ->where("right_ptr <=", $this->right_ptr)
      ->where($where)
      ->count_all();
  }

  /**
   * Move this item to the specified target.
   *
   * @chainable
   * @param   Item_Model $target Target node
   * @return  ORM_MTPP
   */
  function move_to($target) {
    if ($this->contains($target)) {
      throw new Exception("@todo INVALID_TARGET can't move item inside itself");
    }

    $number_to_move = (int)(($this->right_ptr - $this->left_ptr) / 2 + 1);
    $size_of_hole = $number_to_move * 2;
    $original_left_ptr = $this->left_ptr;
    $original_right_ptr = $this->right_ptr;
    $target_right_ptr = $target->right_ptr;
    $level_delta = ($target->level + 1) - $this->level;

    $this->lock();
    try {
      if ($level_delta) {
        // Update the levels for the to-be-moved items
        $this->db->query(
          "UPDATE {{$this->table_name}} SET `level` = `level` + $level_delta" .
          " WHERE `left_ptr` >= $original_left_ptr AND `right_ptr` <= $original_right_ptr");
      }

      // Make a hole in the target for the move
      $target->db->query(
        "UPDATE {{$this->table_name}} SET `left_ptr` = `left_ptr` + $size_of_hole" .
        " WHERE `left_ptr` >= $target_right_ptr");
      $target->db->query(
        "UPDATE {{$this->table_name}} SET `right_ptr` = `right_ptr` + $size_of_hole" .
        " WHERE `right_ptr` >= $target_right_ptr");

      // Change the parent.
      $this->db->query(
        "UPDATE {{$this->table_name}} SET `parent_id` = {$target->id}" .
        " WHERE `id` = {$this->id}");

      // If the source is to the right of the target then we just adjusted its left_ptr and right_ptr above.
      $left_ptr = $original_left_ptr;
      $right_ptr = $original_right_ptr;
      if ($original_left_ptr > $target_right_ptr) {
        $left_ptr += $size_of_hole;
        $right_ptr += $size_of_hole;
      }

      $new_offset = $target->right_ptr - $left_ptr;
      $this->db->query(
        "UPDATE {{$this->table_name}}" .
        "   SET `left_ptr` = `left_ptr` + $new_offset," .
        "       `right_ptr` = `right_ptr` + $new_offset" .
      " WHERE `left_ptr` >= $left_ptr" .
        "   AND `right_ptr` <= $right_ptr");

      // Close the hole in the source's parent after the move
      $this->db->query(
        "UPDATE {{$this->table_name}} SET `left_ptr` = `left_ptr` - $size_of_hole" .
        " WHERE `left_ptr` > $right_ptr");
      $this->db->query(
        "UPDATE {{$this->table_name}} SET `right_ptr` = `right_ptr` - $size_of_hole" .
        " WHERE `right_ptr` > $right_ptr");
    } catch (Exception $e) {
      $this->unlock();
      throw $e;
    }

    $this->unlock();

    // Lets reload to get the changes.
    $this->reload();
    $target->reload();
    return $this;
  }

  /**
   * Lock the tree to prevent concurrent modification.
   */
  protected function lock() {
    $result = $this->db->query("SELECT GET_LOCK('{$this->table_name}', 1) AS l")->current();
    if (empty($result->l)) {
      throw new Exception("@todo UNABLE_TO_LOCK_EXCEPTION");
    }
  }

  /**
   * Unlock the tree.
   */
  protected function unlock() {
    $this->db->query("SELECT RELEASE_LOCK('{$this->table_name}')");
  }
}
