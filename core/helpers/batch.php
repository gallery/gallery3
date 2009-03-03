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

class batch_Core {
  static function operation($name, $item) {
    if (!self::in_progress($name)) {
      Session::instance()->set("operation_$name", "1");
      module::event("operation", $name, $item);
    }
  }

  static function end_operation($name) {
    if (self::in_progress($name)) {
      module::event("end_operation", $name);
      Session::instance()->set("operation_$name", null);
    }
  }

  static function in_progress($name) {
    $value = Session::instance()->get("operation_$name", null);
    return !empty($value);
  }
}
