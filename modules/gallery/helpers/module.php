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
  public static $active = array();
  public static $modules = array();
  public static $var_cache = null;
  public static $available = array();

  /**
   * Set the version of the corresponding Module_Model
   * @param string  $module_name
   * @param integer $version
   */
  static function set_version($module_name, $version) {
    $module = self::get($module_name);
    if (!$module->loaded) {
      $module->name = $module_name;
      $module->active = $module_name == "gallery";  // only gallery is active by default
    }
    $module->version = $version;
    $module->save();
    Kohana::log("debug", "$module_name: version is now $version");
  }

  /**
   * Load the corresponding Module_Model
   * @param string $module_name
   */
  static function get($module_name) {
    // @todo can't easily use model_cache here because it throw an exception on missing models.
    return ORM::factory("module", array("name" => $module_name));
  }

  /**
   * Check to see if a module is installed
   * @param string $module_name
   */
  static function is_installed($module_name) {
    return array_key_exists($module_name, self::$modules);
  }

  /**
   * Check to see if a module is active
   * @param string $module_name
   */
  static function is_active($module_name) {
    return array_key_exists($module_name, self::$modules) &&
      self::$modules[$module_name]->active;
  }

  /**
   * Return the list of available modules, including uninstalled modules.
   */
  static function available() {
    if (empty(self::$available)) {
      $modules = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
      foreach (glob(MODPATH . "*/module.info") as $file) {
        $module_name = basename(dirname($file));
        $modules->$module_name = new ArrayObject(parse_ini_file($file), ArrayObject::ARRAY_AS_PROPS);
        $m =& $modules->$module_name;
        $m->installed = self::is_installed($module_name);
        $m->active = self::is_active($module_name);
        $m->code_version = $m->version;
        $m->version = self::get_version($module_name);
        $m->locked = false;
      }

      // Lock certain modules
      $modules->gallery->locked = true;
      $modules->user->locked = true;
      $modules->ksort();
      self::$available = $modules;
    }

    return self::$available;
  }

  /**
   * Return a list of all the active modules in no particular order.
   */
  static function active() {
    return self::$active;
  }

  /**
   * Install a module.  This will call <module>_installer::install(), which is responsible for
   * creating database tables, setting module variables and calling module::set_version().
   * Note that after installing, the module must be activated before it is available for use.
   * @param string $module_name
   */
  static function install($module_name) {
    $kohana_modules = Kohana::config("core.modules");
    array_unshift($kohana_modules, MODPATH . $module_name);
    Kohana::config_set("core.modules",  $kohana_modules);

    $installer_class = "{$module_name}_installer";
    if (method_exists($installer_class, "install")) {
      call_user_func_array(array($installer_class, "install"), array());
    } else {
      module::set_version($module_name, 1);
    }
    module::load_modules();

    // Now the module is installed but inactive, so don't leave it in the active path
    array_shift($kohana_modules);
    Kohana::config_set("core.modules",  $kohana_modules);

    log::success(
      "module", t("Installed module %module_name", array("module_name" => $module_name)));
  }

  /**
   * Upgrade a module.  This will call <module>_installer::upgrade(), which is responsible for
   * modifying database tables, changing module variables and calling module::set_version().
   * Note that after upgrading, the module must be activated before it is available for use.
   * @param string $module_name
   */
  static function upgrade($module_name) {
    $kohana_modules = Kohana::config("core.modules");
    array_unshift($kohana_modules, MODPATH . $module_name);
    Kohana::config_set("core.modules",  $kohana_modules);

    $version_before = module::get_version($module_name);
    $installer_class = "{$module_name}_installer";
    if (method_exists($installer_class, "upgrade")) {
      call_user_func_array(array($installer_class, "upgrade"), array($version_before));
    } else {
      $available = module::available();
      if (isset($available->$module_name->code_version)) {
        module::set_version($module_name, $available->$module_name->code_version);
      } else {
        throw new Exception("@todo UNKNOWN_MODULE");
      }
    }
    module::load_modules();

    // Now the module is upgraded but inactive, so don't leave it in the active path
    array_shift($kohana_modules);
    Kohana::config_set("core.modules",  $kohana_modules);

    $version_after = module::get_version($module_name);
    if ($version_before != $version_after) {
      log::success(
        "module", t("Upgraded module %module_name from %version_before to %version_after",
                    array("module_name" => $module_name,
                          "version_before" => $version_before,
                          "version_after" => $version_after)));
    }
  }

  /**
   * Activate an installed module.  This will call <module>_installer::activate() which should take
   * any steps to make sure that the module is ready for use.  This will also activate any
   * existing graphics rules for this module.
   * @param string $module_name
   */
  static function activate($module_name) {
    $kohana_modules = Kohana::config("core.modules");
    array_unshift($kohana_modules, MODPATH . $module_name);
    Kohana::config_set("core.modules",  $kohana_modules);

    $installer_class = "{$module_name}_installer";
    if (method_exists($installer_class, "activate")) {
      call_user_func_array(array($installer_class, "activate"), array());
    }

    $module = self::get($module_name);
    if ($module->loaded) {
      $module->active = true;
      $module->save();
    }
    module::load_modules();

    graphics::activate_rules($module_name);
    log::success(
      "module", t("Activated module %module_name", array("module_name" => $module_name)));
  }

  /**
   * Deactivate an installed module.  This will call <module>_installer::deactivate() which should
   * take any cleanup steps to make sure that the module isn't visible in any way.  Note that the
   * module remains available in Kohana's cascading file system until the end of the request!
   * @param string $module_name
   */
  static function deactivate($module_name) {
    $installer_class = "{$module_name}_installer";
    if (method_exists($installer_class, "deactivate")) {
      call_user_func_array(array($installer_class, "deactivate"), array());
    }

    $module = self::get($module_name);
    if ($module->loaded) {
      $module->active = false;
      $module->save();
    }
    module::load_modules();

    graphics::deactivate_rules($module_name);
    log::success(
      "module", t("Deactivated module %module_name", array("module_name" => $module_name)));
  }

  /**
   * Uninstall a deactivated module.  This will call <module>_installer::uninstall() which should
   * take whatever steps necessary to make sure that all traces of a module are gone.
   * @param string $module_name
   */
  static function uninstall($module_name) {
    $installer_class = "{$module_name}_installer";
    if (method_exists($installer_class, "uninstall")) {
      call_user_func(array($installer_class, "uninstall"));
    }

    graphics::remove_rule($module_name);
    $module = self::get($module_name);
    if ($module->loaded) {
      $module->delete();
    }
    module::load_modules();

    // We could delete the module vars here too, but it's nice to leave them around
    // in case the module gets reinstalled.

    log::success(
      "module", t("Uninstalled module %module_name", array("module_name" => $module_name)));
  }

  /**
   * Load the active modules.  This is called at bootstrap time.
   */
  static function load_modules() {
    self::$modules = array();
    self::$active = array();
    $kohana_modules = array();
    foreach (ORM::factory("module")->find_all() as $module) {
      self::$modules[$module->name] = $module;
      if (!$module->active) {
        continue;
      }

      if ($module->name == "gallery") {
        $gallery = $module;
      } else {
        self::$active[] = $module;
        $kohana_modules[] = MODPATH . $module->name;
      }
    }
    self::$active[] = $gallery;  // put gallery last in the module list to match core.modules
    Kohana::config_set(
      "core.modules", array_merge($kohana_modules, Kohana::config("core.modules")));
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

    // @todo: consider calling gallery_event first, since for things menus we need it to do some
    // setup
    foreach (self::$active as $module) {
      $class = "{$module->name}_event";
      if (method_exists($class, $function)) {
        call_user_func_array(array($class, $function), $args);
      }
    }
  }

  /**
   * Get a variable from this module
   * @param string $module_name
   * @param string $name
   * @param string $default_value
   * @return the value
   */
  static function get_var($module_name, $name, $default_value=null) {
    // We cache all vars in gallery._cache so that we can load all vars at once for
    // performance.
    if (empty(self::$var_cache)) {
      $row = Database::instance()
        ->select("value")
        ->from("vars")
        ->where(array("module_name" => "gallery", "name" => "_cache"))
        ->get()
        ->current();
      if ($row) {
        self::$var_cache = unserialize($row->value);
      } else {
        // gallery._cache doesn't exist.  Create it now.
        foreach (Database::instance()
                 ->select("module_name", "name", "value")
                 ->from("vars")
                 ->orderby("module_name", "name")
                 ->get() as $row) {
          if ($row->module_name == "gallery" && $row->name == "_cache") {
            // This could happen if there's a race condition
            continue;
          }
          self::$var_cache->{$row->module_name}->{$row->name} = $row->value;
        }
        $cache = ORM::factory("var");
        $cache->module_name = "gallery";
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
      $var->module_name = $module_name;
      $var->name = $name;
    }
    $var->value = $value;
    $var->save();

    Database::instance()->delete("vars", array("module_name" => "gallery", "name" => "_cache"));
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

    Database::instance()->delete("vars", array("module_name" => "gallery", "name" => "_cache"));
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

    Database::instance()->delete("vars", array("module_name" => "gallery", "name" => "_cache"));
    self::$var_cache = null;
  }

  /**
   * Return the version of the installed module.
   * @param string $module_name
   */
  static function get_version($module_name) {
    return self::get($module_name)->version;
  }
}
