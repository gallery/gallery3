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

class Task_Core {
  public $callback;
  public $description;
  public $name;
  public $severity;

  static function factory($id) {
    return new Task();
  }

  function callback($callback) {
    $this->callback = $callback;
    return $this;
  }

  function description($description) {
    $this->description = $description;
    return $this;
  }

  function name($name) {
    $this->name = $name;
    return $this;
  }

  function severity($severity) {
    $this->severity = $severity;
    return $this;
  }

}
