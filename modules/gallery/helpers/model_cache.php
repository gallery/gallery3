<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
class model_cache_Core {
  private static $cache = array();

  static function get($model_name, $id, $field_name="id") {
    if (TEST_MODE || empty(self::$cache[$model_name][$field_name][$id])) {
      $model = ORM::factory($model_name)->where($field_name, "=", $id)->find();
      if (!$model->loaded()) {
        throw new Exception("@todo MISSING_MODEL $model_name:$id");
      }
      self::$cache[$model_name][$field_name][$id] = $model;
    }

    return self::$cache[$model_name][$field_name][$id];
  }

  static function clear() {
    self::$cache = array();
  }

  static function set($model) {
    self::$cache[$model->object_name][$model->primary_key][$model->{$model->primary_key}] = $model;
  }
}
