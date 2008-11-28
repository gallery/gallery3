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
 * This is the API for handling modules.
 *
 * Note: by design, this class does not do any permission checking.
 */
class module_Core {
  public static function get_version($module_name) {
    return ORM::factory("module")->where("name", $module_name)->find()->version;
  }

  public static function set_version($module_name, $version) {
    $module = ORM::factory("module")->where("name", $module_name)->find();
    if (!$module->loaded) {
      $module->name = $module_name;
    }
    $module->version = 1;
    $module->save();
    Kohana::log("debug", "$module_name: version is now $version");
  }

  public static function get($module_name) {
    return ORM::factory("module")->where("name", $module_name)->find();
  }

  public static function delete ($module_name) {
    ORM::factory("module")->where("name", $module_name)->find()->delete();
    Kohana::log("debug", "$module_name: module deleted");
  }

  public static function is_installed($module_name) {
    return ORM::factory("module")->where("name", $module_name)->find()->loaded;
  }

  public static function installed() {
    return ORM::factory("module")->find_all();
  }

  public static function event($name, &$data=null) {
    foreach (self::installed() as $module) {
      $class = "{$module->name}_event";
      $function = str_replace(".", "_", $name);
      if (method_exists($class, $function)) {
        call_user_func_array(array($class, $function), array($data));
      }
    }
  }
}
