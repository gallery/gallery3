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
class ORM extends ORM_Core {

  /**
   * Make sure that we're only using integer ids.
   */
  static function factory($model, $id=null) {
    if ($id && !is_int($id) && !is_string($id)) {
      throw new Exception("@todo ORM::factory requires integer ids");
    }
    return ORM_Core::factory($model, (int) $id);
  }

  public function save() {
    model_cache::clear();
    return parent::save();
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