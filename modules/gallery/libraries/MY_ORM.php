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
class ORM extends ORM_Core {
  // Track the original value of this ORM so that we can look it up in ORM::original()
  protected $original = null;

  public function open_paren() {
    $this->db->open_paren();
    return $this;
  }

  public function close_paren() {
    $this->db->close_paren();
    return $this;
  }

  public function save() {
    model_cache::clear();
    $result = parent::save();
    $this->original = clone $this;
    return $result;
  }

  public function __set($column, $value) {
    if (!isset($this->original)) {
      $this->original = clone $this;
    }

    if ($value instanceof SafeString) {
      $value = $value->unescaped();
    }

    return parent::__set($column, $value);
  }

  public function __unset($column) {
    if (!isset($this->original)) {
      $this->original = clone $this;
    }

    return parent::__unset($column);
  }

  public function original() {
    return $this->original;
  }
}

/**
 * Slide this in here for convenience.  We won't ever be overloading ORM_Iterator without ORM.
 */
class ORM_Iterator extends ORM_Iterator_Core {
  /**
   * Cache the result row
   */
  public function current() {
    $row = parent::current();
    if (is_object($row)) {
      model_cache::set($row);
    }
    return $row;
  }
}