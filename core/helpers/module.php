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

/**
 * This is the API for handling modules.
 *
 * Note: by design, this class does not do any permission checking.
 */
class module_Core {
  public static $module_names = array();
  public static $modules = array();
  public static $var_cache = null;

  static function get_version($module_name) {
    return ORM::factory("module")->where("name", $module_name)->find()->version;
  }

  /**
   * Set the version of the corresponding Module_Model
   * @param string  $module_name
   * @param integer $version
   */
  static function set_version($module_name, $version) {
    $module = ORM::factory("module")->where("name", $module_name)->find();
    if (!$module->loaded) {
      $module->name = $module_name;
    }
    $module->version = 1;
    $module->save();
    Kohana::log("debug", "$module_name: version is now $version");
  }

  /**
   * Load the corresponding Module_Model
   * @param string $module_name
   */
  static function get($module_name) {
    return model_cache::get("module", $module_name, "name");
  }

  /**
   * Delete the corresponding Module_Model
   * @param string $module_name
   */
  static function delete($module_name) {
    $module = ORM::factory("module")->where("name", $module_name)->find();
    if ($module->loaded) {
      $db = Database::instance();
      $db->delete("graphics_rules", array("module_name" => $module->name));
      $module->delete();

      // We could delete the module vars here too, but it's nice to leave them around in case the
      // module gets reinstalled.

      Kohana::log("debug", "$module_name: module deleted");
    }
  }

  /**
   * Check to see if a module is installed
   * @param string $module_name
   */
  static function is_installed($module_name) {
    return !empty(self::$module_names[$module_name]);
  }

  /**
   * Return the list of installed modules.
   */
  static function installed() {
    return self::$modules;
  }

  /**
   * Return the list of available modules.
   */
  static function available() {
    $modules = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
    foreach (array_merge(array("core/module.info"), glob(MODPATH . "*/module.info")) as $file) {
      $module_name = basename(dirname($file));
      $modules->$module_name = new ArrayObject(parse_ini_file($file), ArrayObject::ARRAY_AS_PROPS);
      $modules->$module_name->installed =
        empty(self::$modules[$module_name]) ?
        null : self::$modules[$module_name]->version;
      $modules->$module_name->locked = false;
    }

    // Lock certain modules
    $modules->core->locked = true;
    $modules->user->locked = true;
    $modules->ksort();

    return $modules;
  }

  /**
   * Install a module.
   */
  static function install($module_name) {
    $installer_class = "{$module_name}_installer";
    Kohana::log("debug", "$installer_class install (initial)");
    if ($module_name != "core") {
      require_once(DOCROOT . "modules/${module_name}/helpers/{$installer_class}.php");
    }
    $kohana_modules = Kohana::config("core.modules");
    $kohana_modules[] = MODPATH . $module_name;
    Kohana::config_set("core.modules",  $kohana_modules);

    if (method_exists($installer_class, "install")) {
      call_user_func_array(array($installer_class, "install"), array());
    }

    self::load_modules();
    log::success(
      "module", t("Installed module %module_name", array("module_name" => $module_name)));
  }

  /**
   * Uninstall a module.

   */
  static function uninstall($module_name) {
    $installer_class = "{$module_name}_installer";
    Kohana::log("debug", "$installer_class uninstall");
    call_user_func(array($installer_class, "uninstall"));
    log::success(
      "module", t("Uninstalled module %module_name", array("module_name" => $module_name)));
  }

  /**
   * Load the active modules.  This is called at bootstrap time.
   */
  static function load_modules() {
    // Reload module list from the config file since we'll do a refresh after calling install()
    $core = Kohana::config_load("core");
    $kohana_modules = $core["modules"];
    $modules = ORM::factory("module")->find_all();

    foreach ($modules as $module) {
      self::$module_names[$module->name] = $module->name;
      self::$modules[$module->name] = $module;

      // @todo For some reason if we don't load the core module here, the test framework fails.
      // This requires some investigation.
      if ($module->name != "core") {
        $kohana_modules[] = MODPATH . $module->name;
      }
    }
    Kohana::config_set("core.modules", $kohana_modules);

    self::event("gallery_ready");
  }

  /**
   * Run a specific event on all active modules.
   * @param string $name the event name
   * @param mixed  $data data to pass to each event handler
   */
  static function event($name, &$data=null) {
    $args = func_get_args();
    array_shift($args);
    $function = str_replace(".", "_", $name);

    foreach (self::installed() as $module) {
      $class = "{$module->name}_event";
      if (method_exists($class, $function)) {
        call_user_func_array(array($class, $function), $args);
      }
    }
  }

  /**
   * Kohana shutdown event handler
   * @param string $module_name
   * @param string $name
   * @param string $default_value
   * @return the value
   */
  static function shutdown() {
    self::event("gallery_shutdown");
  }

  /**
   * Get a variable from this module
   * @param string $module_name
   * @param string $name
   * @param string $default_value
   * @return the value
   */
  static function get_var($module_name, $name, $default_value=null) {
    // We cache all vars in core._cache so that we can load all vars at once for
    // performance.
    if (empty(self::$var_cache)) {
      $row = Database::instance()
        ->select("value")
        ->from("vars")
        ->where(array("module_name" => "core", "name" => "_cache"))
        ->get()
        ->current();
      if ($row) {
        self::$var_cache = unserialize($row->value);
      } else {
        // core._cache doesn't exist.  Create it now.
        foreach (Database::instance()
                 ->select("module_name", "name", "value")
                 ->from("vars")
                 ->orderby("module_name", "name")
                 ->get() as $row) {
          if ($row->module_name == "core" && $row->name == "_cache") {
            // This could happen if there's a race condition
            continue;
          }
          self::$var_cache->{$row->module_name}->{$row->name} = $row->value;
        }
        $cache = ORM::factory("var");
        $cache->module_name = "core";
        $cache->name = "_cache";
        $cache->value = serialize(self::$var_cache);
        $cache->save();
      }
    }

    if (isset(self::$var_cache->$module_name->$name)) {
      return self::$var_cache->$module_name->$name;
    } else {
      return $default_value;
    }
  }

  /**
   * Store a variable for this module
   * @param string $module_name
   * @param string $name
   * @param string $value
   */
  static function set_var($module_name, $name, $value) {
    $var = ORM::factory("var")
      ->where("module_name", $module_name)
      ->where("name", $name)
      ->find();
    if (!$var->loaded) {
      $var = ORM::factory("var");
      $var->module_name = $module_name;
      $var->name = $name;
    }
    $var->value = $value;
    $var->save();

    Database::instance()->delete("vars", array("module_name" => "core", "name" => "_cache"));
    self::$var_cache = null;
 }

  /**
   * Increment the value of a variable for this module
   * @param string $module_name
   * @param string $name
   * @param string $increment (optional, default is 1)
   */
  static function incr_var($module_name, $name, $increment=1) {
    Database::instance()->query(
      "UPDATE {vars} SET `value` = `value` + $increment " .
      "WHERE `module_name` = '$module_name' " .
      "AND `name` = '$name'");

    Database::instance()->delete("vars", array("module_name" => "core", "name" => "_cache"));
    self::$var_cache = null;
  }

 /**
   * Remove a variable for this module.
   * @param string $module_name
   * @param string $name
   */
  static function clear_var($module_name, $name) {
    $var = ORM::factory("var")
      ->where("module_name", $module_name)
      ->where("name", $name)
      ->find();
    if ($var->loaded) {
      $var->delete();
    }

    Database::instance()->delete("vars", array("module_name" => "core", "name" => "_cache"));
    self::$var_cache = null;
  }
}
