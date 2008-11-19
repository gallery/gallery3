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
    $this->_lock();

    try {
      $parent = ORM::factory($this->model_name, $parent_id);
      $parent->_grow();
      $this->left = $parent->right - 2;
      $this->right = $parent->right - 1;
      $this->parent_id = $parent->id;
      $this->level = $parent->level + 1;
      $this->save();
    } catch (Exception $e) {
      $this->_unlock();
      throw $e;
    }

    $this->_unlock();
    return $this;
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
  function children($limit=NULL, $offset=0) {
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
      $this->children_count =
        $this->where("parent_id", $this->id)
        ->orderby("id", "ASC")
        ->count_all();
    }
    return $this->children_count;
  }

  /**
   * Return all of the children of the specified type, ordered by id.
   *
   * @chainable
   * @param   string   type to return
   * @param   integer  SQL limit
   * @param   integer  SQL offset
   * @param   boolean  flag to return all grandchildren as well
   * @return array ORM
   */
  function decendents_by_type($type="photo", $limit=NULL, $offset=0, $grand_children=false) {
    if (!isset($this->children)) {
      if (!empty($grandchildren)) {
        $this->where("left >=", $this->left)
             ->where("right <=", $this->right);
      } else {
        $this->where("parent_id", $this->id);
      }
      $this->children =
        $this->where("type", $type)
        ->orderby("id", "ASC")
        ->find_all($limit, $offset);
    }
    return $this->children;
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
   * Grow this node's space enough to make room for 1 or more new nodes.
   *
   * @param integer $count the number of new nodes to add
   */
  private function _grow($count=1) {
    $size = $count * 2;
    $this->db->query(
      "UPDATE `{$this->table_name}` SET `left` = `left` + $size WHERE `left` >= {$this->right}");
    $this->db->query(
      "UPDATE `{$this->table_name}` SET `right` = `right` + $size WHERE `right` >= {$this->right}");
    $this->right += 2;
  }

  /**
   * Lock the tree to prevent concurrent modification.
   */
  private function _lock() {
    $result = $this->db->query("SELECT GET_LOCK('{$this->table_name}', 1) AS L")->current();
    if (empty($result->L)) {
      throw new Exception("@todo UNABLE_TO_LOCK_EXCEPTION");
    }
  }

  /**
   * Unlock the tree.
   */
  private function _unlock() {
    $this->db->query("SELECT RELEASE_LOCK('{$this->table_name}')");
  }
}
