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
   * (see Kohana_ORM::_initialize())
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
}
