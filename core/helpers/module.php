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
  private static $module_names = array();
  private static $modules = array();

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
    $module = ORM::factory("module")->where("name", $module_name)->find();
    $module_id = $module->id;
    $module->delete();

    $db = Database::instance();
    $db->query("DELETE FROM vars WHERE module_id = '{$module->id}';");

    Kohana::log("debug", "$module_name: module deleted");
  }

  public static function is_installed($module_name) {
    return in_array($module_name, self::$module_names);
  }

  public static function installed() {
    return self::$modules;
  }

  public static function event($name, &$data=null) {
    foreach (self::installed() as $module) {
      $class = "{$module->name}_event";
      $function = str_replace(".", "_", $name);
      if (method_exists($class, $function)) {
        call_user_func_array(array($class, $function), array(&$data));
      }
    }
  }

  public static function available() {
    $modules = array();
    foreach (glob(MODPATH . "*/helpers/*_installer.php") as $file) {
      if (empty($modules[basename(dirname(dirname($file)))])) {
        $modules[basename(dirname(dirname($file)))] = 0;
      }
    }

    return $modules;
  }

  public static function install($module_name) {
    $installer_class = "{$module_name}_installer";
    Kohana::log("debug", "$installer_class install (initial)");
    if ($module_name != "core") {
      require_once(DOCROOT . "modules/${module_name}/helpers/{$installer_class}.php");
    }
    $kohana_modules = Kohana::config('core.modules');
    $kohana_modules[] = MODPATH . $module_name;
    Kohana::config_set('core.modules',  $kohana_modules);

    call_user_func(array($installer_class, "install"));

    if (method_exists($installer_class, "install")) {
      call_user_func_array(array($installer_class, "install"), array());
    }

    self::load_modules();
  }

  public static function uninstall($module_name) {
    $installer_class = "{$module_name}_installer";
    Kohana::log("debug", "$installer_class uninstall");
    call_user_func(array($installer_class, "uninstall"));
  }

  public static function load_modules() {
    // This is one of the first database operations that we'll do, so it may fail if there's no
    // install yet.  Try to handle this situation gracefully expecting that the scaffolding will
    // Do The Right Thing.
    //
    // @todo get rid of this extra error checking when we have an installer.
    set_error_handler(array("module", "_dummy_error_handler"));

    // Reload module list from the config file since we'll do a refresh after calling install()
    $core = Kohana::config_load('core');
    $kohana_modules = $core['modules'];
    self::$module_names = array();
    self::$modules = array();
    try {
      foreach (ORM::factory("module")->find_all() as $module) {
        self::$module_names[] = $module->name;
        self::$modules[] = $module;
        $kohana_modules[] = MODPATH . $module->name;
      }

      Kohana::config_set('core.modules', $kohana_modules);
    } catch (Exception $e) {
      self::$module_names = array();
      self::$modules = array();
    }

    restore_error_handler();
  }

  public function get_var($module_name, $name, $default_value=null) {
    $module = ORM::factory("module")->where("name", $module_name)->find();
    $var = ORM::factory("var")
      ->where("module_id", $module->id)
      ->where("name", $name)
      ->find();
    return $var->loaded ? $var->value : $default_value;
  }

  public function set_var($module_name, $name, $value) {
    $module = ORM::factory("module")->where("name", $module_name)->find();
    $var = ORM::factory("var")
      ->where("module_id", $module->id)
      ->where("name", $name)
      ->find();
    if (!$var->loaded) {
      $var = ORM::factory("var");
      $var->module_id = $module->id;
      $var->name = $name;
    }
    $var->value = $value;
    $var->save();
  }

  /**
   * Dummy error handler used in module::load_modules.
   *
   * @todo remove this when we have an installer.
   */
  public static function _dummy_error_handler() {
  }
}
