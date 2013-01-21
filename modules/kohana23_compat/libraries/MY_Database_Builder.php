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
class Database_Builder extends Database_Builder_Core {
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

  public function compile() {
    return parent::compile();
  }
}