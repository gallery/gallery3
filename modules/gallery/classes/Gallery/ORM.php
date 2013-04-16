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
class Gallery_ORM extends Kohana_ORM {
  /**
   * Merge in a series of where clause tuples and call where() on each one.
   * @chainable
   */
  public function merge_where($tuples) {
    if ($tuples) {
      foreach ($tuples as $tuple) {
        $this->where($tuple[0], $tuple[1], $tuple[2]);
      }
    }
    return $this;
  }

  /**
   * Merge in a series of where clause tuples and call or_where() on each one.
   * @chainable
   */
  public function merge_or_where($tuples) {
    if ($tuples) {
      foreach ($tuples as $tuple) {
        $this->or_where($tuple[0], $tuple[1], $tuple[2]);
      }
    }
    return $this;
  }

  /**
   * Merge in a series of order by column-direction pairs and call order_by() on each one.
   * @chainable
   */
  public function merge_order_by($pairs) {
    if ($pairs) {
      foreach ($pairs as $column => $direction) {
        $this->order_by($column, $direction);
      }
    }
    return $this;
  }

  /**
   * Set the table name using our method.  Kohana finds it by getting the lowercase version of
   * the model name and adding plural if needed.  The problem is that it's not sensitive to
   * camelcase, which breaks how Gallery's DB is set up.  By setting $this->_table_name ahead
   * of time, the parent function will use our value instead.  Note: can give odd results if you
   * use a series of capital letters (see examples below).
   *
   * Examples: "Model_Item"                --> "items"
   *           "Model_IncomingTranslation" --> "incoming_translations", not "incomingtranslations"
   *           "Model_ORM_Example"         --> "orm_examples"
   *           "Model_ORMExample"          --> "ormexamples"
   *           "Model_ORMAnotherExample"   --> "ormanother_examples", not "ormanotherexamples"
   *           "Model_AnotherORMExample"   --> "another_ormexamples", not "anotherormexamples"
   *
   * @see ORM::_initialize()
   */
  protected function _initialize() {
    if (empty($this->_table_name)) {
      // Get the table name by using Inflector::decamelize() instead of strtolower()
      $this->_table_name = Inflector::convert_class_to_module_name(substr(get_class($this), 6));
      // Make the table name plural (if specified)
      if ($this->_table_names_plural === true) {
        $this->_table_name = Inflector::plural($this->_table_name);
      }
    }
    parent::_initialize();
  }

  /**
   * Implements the "delete_through" argument of a has_many relationship, which removes the pivot
   * table rows corresponding to the model at the same time as the model itself.  By default,
   * Kohana's ORM does not do this, so the pivot table (defined by "through") remains populated
   * with rows corresponding to deleted models.
   *
   * Example: users can belong to one or more groups, related by the pivot table "groups_users".
   * In Model_User, we have:
   *   protected $_has_many = array("groups" =>
   *                                array("through" => "groups_users", "delete_through" => true)
   * In Model_Group, we have:
   *   protected $_has_many = array("users" =>
   *                                array("through" => "groups_users", "delete_through" => true)
   * Now, when either a group or user is deleted, all rows in "groups_users" are also deleted.
   *
   * @see ORM::delete()
   */
  public function delete() {
    if (!empty($this->_has_many)) {
      foreach ($this->_has_many as $column => $details) {
        if (!empty($details["delete_through"])) {
          $this->remove($column);
        }
      }
    }
    return parent::delete();
  }
}
