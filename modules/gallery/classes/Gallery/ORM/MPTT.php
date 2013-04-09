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
class Gallery_ORM_MPTT extends ORM {
  private $_model_name = null;

  function _initialize() {
    parent::_initialize();
    // Similar to how parent::_initialize() gets $_object_name.
    $this->_model_name = substr(get_class($this), 6);
  }

  /**
   * Overload ORM::create() to update the MPTT tree when we add new items to the hierarchy.
   *
   * @chainable
   * @return  ORM
   */
  function create(Validation $validation=null) {
    if ($this->_loaded) {
      throw new Kohana_Exception("Cannot create :model model because it is already loaded.",
                                 array(':model' => $this->_object_name));
    }

    // Require model validation before saving
    if (!$this->_valid || $validation) {
      $this->check($validation);
    }

    $this->lock();
    $parent = ORM::factory("Item", $this->parent_id);

    try {
      // Make a hole in the parent for this new item
      DB::update($this->table_name())
        ->set("left_ptr", DB::expr("`left_ptr` + 2"))
        ->where("left_ptr", ">=", $parent->right_ptr)
        ->execute($this->_db);
      DB::update($this->table_name())
        ->set("right_ptr", DB::expr("`right_ptr` + 2"))
        ->where("right_ptr", ">=", $parent->right_ptr)
        ->execute($this->_db);
      $parent->right_ptr += 2;

      // Insert this item into the hole
      $this->left_ptr = $parent->right_ptr - 2;
      $this->right_ptr = $parent->right_ptr - 1;
      $this->parent_id = $parent->id;
      $this->level = $parent->level + 1;
    } catch (Exception $e) {
      $this->unlock();
      throw $e;
    }

    parent::create();
    $this->unlock();

    return $this;
  }

  /**
   * Overload ORM::delete to delete all of this node's children.
   */
  public function delete($ignored_id=null) {
    if (!$this->_loaded) {
      throw new Kohana_Exception("Cannot delete :model model because it is not loaded.",
                                 array(':model' => $this->_object_name));
    }

    $children = $this->children();
    if ($children) {
      foreach ($this->children() as $item) {
        // Deleting children affects the MPTT tree, so we have to reload each child before we
        // delete it so that we have current left_ptr/right_ptr pointers.  This is inefficient.
        // @todo load each child once, not twice.
        set_time_limit(30);
        $item->reload()->delete();
      }

      // Deleting children has affected this item, but we'll reload it below.
    }

    $this->lock();
    $this->reload();  // Assume that the prior lock holder may have changed this entry
    if (!$this->loaded()) {
      // Concurrent deletes may result in this item already being gone.  Ignore it.
      return;
    }

    try {
      DB::update($this->table_name())
        ->set("left_ptr", DB::expr("`left_ptr` - 2"))
        ->where("left_ptr", ">", $this->right_ptr)
        ->execute($this->_db);
      DB::update($this->table_name())
        ->set("right_ptr", DB::expr("`right_ptr` - 2"))
        ->where("right_ptr", ">", $this->right_ptr)
        ->execute($this->_db);
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
    return ModelCache::get($this->_model_name, $this->parent_id);
  }

  /**
   * Return all the parents of this node, in order from root to this node's immediate parent.
   *
   * @return array ORM
   */
  function parents($where=null) {
    return $this
      ->merge_where($where)
      ->where("left_ptr", "<=", $this->left_ptr)
      ->where("right_ptr", ">=", $this->right_ptr)
      ->where("id", "<>", $this->id)
      ->order_by("left_ptr", "ASC")
      ->find_all();
  }

  /**
   * Return all of the children of this node, ordered by id.
   *
   * @chainable
   * @param   integer  SQL limit
   * @param   integer  SQL offset
   * @param   array    additional where clauses
   * @param   array    order_by
   * @return array ORM
   */
  function children($limit=null, $offset=null, $where=null, $order_by=array("id" => "ASC")) {
    return $this
      ->merge_where($where)
      ->where("parent_id", "=", $this->id)
      ->merge_order_by($order_by)
      ->limit($limit)->offset($offset)->find_all();
  }

  /**
   * Return the number of children of this node.
   *
   * @chainable
   * @param   array    additional where clauses
   * @return array ORM
   */
  function children_count($where=null) {
    return $this
      ->merge_where($where)
      ->where("parent_id", "=", $this->id)
      ->count_all();
  }

  /**
   * Return all of the decendents of the specified type, ordered by id.
   *
   * @param   integer  SQL limit
   * @param   integer  SQL offset
   * @param   array    additional where clauses
   * @param   array    order_by
   * @return object ORM_Iterator
   */
  function descendants($limit=null, $offset=null, $where=null, $order_by=array("id" => "ASC")) {
    return $this
      ->merge_where($where)
      ->where("left_ptr", ">", $this->left_ptr)
      ->where("right_ptr", "<=", $this->right_ptr)
      ->merge_order_by($order_by)
      ->limit($limit)->offset($offset)->find_all();
  }

  /**
   * Return the count of all the children of the specified type.
   *
   * @param    array    additional where clauses
   * @return   integer  child count
   */
  function descendants_count($where=null) {
    return $this
      ->merge_where($where)
      ->where("left_ptr", ">", $this->left_ptr)
      ->where("right_ptr", "<=", $this->right_ptr)
      ->count_all();
  }

  /**
   * Move this item to the specified target.
   *
   * @chainable
   * @param   Model_Item $target Target node
   * @return  ORM_MPTT
   */
  protected function move_to($target) {
    if ($this->contains($target)) {
      throw new Exception("@todo INVALID_TARGET can't move item inside itself");
    }

    $this->lock();
    $this->reload();  // Assume that the prior lock holder may have changed this entry
    $target->reload();

    $number_to_move = (int)(($this->right_ptr - $this->left_ptr) / 2 + 1);
    $size_of_hole = $number_to_move * 2;
    $original_left_ptr = $this->left_ptr;
    $original_right_ptr = $this->right_ptr;
    $target_right_ptr = $target->right_ptr;
    $level_delta = ($target->level + 1) - $this->level;

    try {
      if ($level_delta) {
        // Update the levels for the to-be-moved items
        DB::update($this->table_name())
          ->set("level", DB::expr("`level` + $level_delta"))
          ->where("left_ptr", ">=", $original_left_ptr)
          ->where("right_ptr", "<=", $original_right_ptr)
          ->execute($this->_db);
      }

      // Make a hole in the target for the move
      DB::update($this->table_name())
        ->set("left_ptr", DB::expr("`left_ptr` + $size_of_hole"))
        ->where("left_ptr", ">=", $target_right_ptr)
        ->execute($this->_db);
      DB::update($this->table_name())
        ->set("right_ptr", DB::expr("`right_ptr` + $size_of_hole"))
        ->where("right_ptr", ">=", $target_right_ptr)
        ->execute($this->_db);

      // Change the parent.
      DB::update($this->table_name())
        ->set("parent_id", $target->id)
        ->where("id", "=", $this->id)
        ->execute($this->_db);

      // If the source is to the right of the target then we just adjusted its left_ptr and
      // right_ptr above.
      $left_ptr = $original_left_ptr;
      $right_ptr = $original_right_ptr;
      if ($original_left_ptr > $target_right_ptr) {
        $left_ptr += $size_of_hole;
        $right_ptr += $size_of_hole;
      }

      $new_offset = $target->right_ptr - $left_ptr;
      DB::update($this->table_name())
        ->set("left_ptr", DB::expr("`left_ptr` + $new_offset"))
        ->set("right_ptr", DB::expr("`right_ptr` + $new_offset"))
        ->where("left_ptr", ">=", $left_ptr)
        ->where("right_ptr", "<=", $right_ptr)
        ->execute($this->_db);

      // Close the hole in the source's parent after the move
      DB::update($this->table_name())
        ->set("left_ptr", DB::expr("`left_ptr` - $size_of_hole"))
        ->where("left_ptr", ">", $right_ptr)
        ->execute($this->_db);
      DB::update($this->table_name())
        ->set("right_ptr", DB::expr("`right_ptr` - $size_of_hole"))
        ->where("right_ptr", ">", $right_ptr)
        ->execute($this->_db);
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
    $timeout = Module::get_var("gallery", "lock_timeout", 1);
    $result = $this->_db->query("SELECT GET_LOCK('" . $this->table_name() . "', $timeout) AS l")
      ->current();
    if (empty($result->l)) {
      throw new Exception("@todo UNABLE_TO_LOCK_EXCEPTION");
    }
  }

  /**
   * Unlock the tree.
   */
  protected function unlock() {
    $this->db->query("SELECT RELEASE_LOCK('" . $this->table_name() . "')");
  }
}
