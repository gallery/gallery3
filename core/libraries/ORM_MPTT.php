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
  private $parent = null;
  private $parents = null;
  private $children = null;
  private $children_count = null;
  private $descendants = array();
  private $descendants_count = array();

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
  function add_to_parent($parent_id) {
    $this->lock();

    try {
      // Make a hole in the parent for this new item
      $parent = ORM::factory($this->model_name, $parent_id);
      $this->db->query(
        "UPDATE `{$this->table_name}` SET `left` = `left` + 2 WHERE `left` >= {$parent->right}");
      $this->db->query(
        "UPDATE `{$this->table_name}` SET `right` = `right` + 2 WHERE `right` >= {$parent->right}");
      $parent->right += 2;

      // Insert this item into the hole
      $this->left = $parent->right - 2;
      $this->right = $parent->right - 1;
      $this->parent_id = $parent->id;
      $this->level = $parent->level + 1;
      $this->save();
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
        $item->delete();
      }

      // Deleting children has affected this item
      $this->reload();
    }

    $this->lock();
    try {
      $this->db->query(
        "UPDATE `{$this->table_name}` SET `left` = `left` - 2 WHERE `left` > {$this->right}");
      $this->db->query(
        "UPDATE `{$this->table_name}` SET `right` = `right` - 2 WHERE `right` > {$this->right}");
    } catch (Exception $e) {
      $this->unlock();
      throw $e;
    }

    $this->unlock();
    parent::delete();
  }

  /**
   * Return the parent of this node
   *
   * @return ORM
   */
  function parent() {
    if (!isset($this->parent)) {
      $this->parent =
        ORM::factory($this->model_name)->where("id", $this->parent_id)->find();
    }
    return $this->parent;
  }

  /**
   * Return all the parents of this node, in order from root to this node's immediate parent.
   *
   * @return array ORM
   */
  function parents() {
    if (!isset($this->parents)) {
      $this->parents = $this
        ->where("`left` <= {$this->left}")
        ->where("`right` >= {$this->right}")
        ->where("id <> {$this->id}")
        ->orderby("left", "ASC")
        ->find_all();
    }
    return $this->parents;
  }

  /**
   * Return all of the children of this node, ordered by id.
   *
   * @chainable
   * @param   integer  SQL limit
   * @param   integer  SQL offset
   * @return array ORM
   */
  function children($limit=null, $offset=0) {
    if (!isset($this->children)) {
      $this->children =
        $this->where("parent_id", $this->id)
        ->orderby("id", "ASC")
        ->find_all($limit, $offset);
    }
    return $this->children;
  }

  /**
   * Return all of the children of this node, ordered by id.
   *
   * @chainable
   * @param   integer  SQL limit
   * @param   integer  SQL offset
   * @return array ORM
   */
  function children_count() {
    if (!isset($this->children_count)) {
      $this->children_count = $this->where("parent_id", $this->id)->count_all();
    }
    return $this->children_count;
  }

  /**
   * Return all of the children of the specified type, ordered by id.
   *
   * @param   integer  SQL limit
   * @param   integer  SQL offset
   * @param   string   type to return
   * @return object ORM_Iterator
   */
  function descendants($limit=null, $offset=0, $type=null) {
    if (!isset($this->descendants[$type][$offset])) {
      $this->where("left >", $this->left)
        ->where("right <=", $this->right);
      if ($type) {
        $this->where("type", $type);
      }

      // @todo: make the order column data driven
      $this->orderby("id", "ASC");

      $this->descendants[$type][$offset] = $this->find_all($limit, $offset);
    }
    return $this->descendants[$type][$offset];
  }

  /**
   * Return the count of all the children of the specified type.
   *
   * @param   string   type to count
   * @return   integer  child count
   */
  function descendants_count($type=null) {
    if (!isset($this->descendants_count[$type])) {
      $this->where("left >", $this->left)
        ->where("right <=", $this->right);
      if ($type) {
        $this->where("type", $type);
      }
      $this->descendants_count[$type] = $this->count_all();
    }

    return $this->descendants_count[$type];
  }

  /**
   * @see ORM::reload
   */
  function reload() {
    $this->parent = null;
    $this->parents = null;
    $this->children = null;
    $this->children_count = null;
    return parent::reload();
  }

  /**
   * Move this item to the specified target.
   *
   * @chainable
   * @param   Item_Model $target  Target item (must be an album)
   * @param   boolean    $locked  The caller is already holding the lock
   * @return  ORM_MTPP
   */
  function move_to($target, $locked=false) {
    if ($target->type != "album") {
      throw new Exception("@todo '{$target->type}' IS NOT A VALID MOVE TARGET");
    }

    if ($this->id == 1) {
      throw new Exception("@todo '{$this->title}' IS NOT A VALID SOURCE");
    }

    $number_to_move = (int)(($this->right - $this->left) / 2 + 1);
    $size_of_hole = $number_to_move * 2;
    $original_parent = $this->parent;
    $original_left = $this->left;
    $original_right = $this->right;
    $target_right = $target->right;

    if (empty($locked)) {
      $this->lock();
    }
    try {
      // Make a hole in the target for the move
      $target->db->query(
        "UPDATE `{$this->table_name}` SET `left` = `left` + $size_of_hole" .
        " WHERE `left` >= $target_right");
      $target->db->query(
        "UPDATE `{$this->table_name}` SET `right` = `right` + $size_of_hole" .
        " WHERE `right` >= $target_right");

      // Change the parent.
      $this->db->query(
        "UPDATE `{$this->table_name}` SET `parent_id` = {$target->id}" .
        " WHERE `id` = {$this->id}");

      // If the source is to the right of the target then we just adjusted its left and right above.
      $left = $original_left;
      $right = $original_right;
      if ($original_left > $target_right) {
        $left += $size_of_hole;
        $right += $size_of_hole;
      }

      $newOffset = $target->right - $left;
      $this->db->query(
        "UPDATE `{$this->table_name}`" .
        "   SET `left` = `left` + $newOffset," .
        "       `right` = `right` + $newOffset" .
      " WHERE `left` >= $left" .
        "   AND `right` <= $right");

      // Close the hole in the source's parent after the move
      $this->db->query(
        "UPDATE `{$this->table_name}` SET `left` = `left` - $size_of_hole" .
        " WHERE `left` > $right");
      $this->db->query(
        "UPDATE `{$this->table_name}` SET `right` = `right` - $size_of_hole" .
        " WHERE `right` > $right");

    } catch (Exception $e) {
      if (empty($locked)) {
        $this->unlock();
      }
      throw $e;
    }

    if (empty($locked)) {
      $this->_unlock();
    }

    // Lets reload to get the changes.
    $this->reload();
    return $this;
  }

  /**
   * Lock the tree to prevent concurrent modification.
   */
  protected function lock() {
    $result = $this->db->query("SELECT GET_LOCK('{$this->table_name}', 1) AS L")->current();
    if (empty($result->L)) {
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
