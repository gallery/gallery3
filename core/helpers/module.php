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

  /**
   * Set the version of the corresponding Module_Model
   * @param string  $module_name
   * @param integer $version
   */
  public static function set_version($module_name, $version) {
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
  public static function get($module_name) {
    return model_cache::get("module", $module_name, "name");
  }

  /**
   * Delete the corresponding Module_Model
   * @param string $module_name
   */
  public static function delete($module_name) {
    $module = ORM::factory("module")->where("name", $module_name)->find();
    $module_id = $module->id;
    $module->delete();

    $db = Database::instance();
    $db->query("DELETE FROM vars WHERE module_id = '{$module->id}';");

    Kohana::log("debug", "$module_name: module deleted");
  }

  /**
   * Check to see if a module is installed
   * @param string $module_name
   */
  public static function is_installed($module_name) {
    return !empty(self::$module_names[$module_name]);
  }

  /**
   * Return the list of installed modules.
   */
  public static function installed() {
    return self::$modules;
  }

  /**
   * Return the list of available modules.
   */
  public static function available() {
    $modules = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
    foreach (array_merge(array("core/module.info"), glob(MODPATH . "*/module.info")) as $file) {
      $module_name = basename(dirname($file));
      $modules->$module_name = new ArrayObject(parse_ini_file($file), ArrayObject::ARRAY_AS_PROPS);
      $modules->$module_name->installed =
        empty(self::$modules[$module_name]) ?
        null : self::$modules[$module_name]->version;
    }

    return $modules;
  }

  /**
   * Install a module.
   */
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

  /**
   * Uninstall a module.
   */
  public static function uninstall($module_name) {
    $installer_class = "{$module_name}_installer";
    Kohana::log("debug", "$installer_class uninstall");
    call_user_func(array($installer_class, "uninstall"));
  }

  /**
   * Load the active modules.  This is called at bootstrap time.
   */
  public static function load_modules() {
    // Reload module list from the config file since we'll do a refresh after calling install()
    $core = Kohana::config_load('core');
    $kohana_modules = $core['modules'];
    self::$module_names = array();
    self::$modules = array();

    // This is one of the first database operations that we'll do, so it may fail if there's no
    // install yet.  Try to handle this situation gracefully expecting that the scaffolding will
    // Do The Right Thing.
    //
    // @todo get rid of this extra error checking when we have an installer.
    try {
      $modules = ORM::factory("module")->find_all();
    } catch (Exception $e) {
      return;
    }

    try {
      foreach ($modules as $module) {
        self::$module_names[$module->name] = $module->name;
        self::$modules[$module->name] = $module;
        $kohana_modules[] = MODPATH . $module->name;
      }

      Kohana::config_set('core.modules', $kohana_modules);
    } catch (Exception $e) {
      self::$module_names = array();
      self::$modules = array();
    }

    self::event("gallery_ready");
  }

  /**
   * Load the active theme.  This is called at bootstrap time.  We will only ever have one theme
   * active for any given request.
   */
  public static function load_themes() {
    $modules = Kohana::config('core.modules');
    if (Router::$controller == "admin") {
      array_unshift($modules, THEMEPATH . 'admin_default');
    } else {
      array_unshift($modules, THEMEPATH . 'default');
    }
    Kohana::config_set('core.modules', $modules);
  }

  /**
   * Run a specific event on all active modules.
   * @param string $name the event name
   * @param mixed  $data data to pass to each event handler
   */
  public static function event($name, &$data=null) {
    foreach (self::installed() as $module) {
      $class = "{$module->name}_event";
      $function = str_replace(".", "_", $name);
      if (method_exists($class, $function)) {
        call_user_func_array(array($class, $function), array(&$data));
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
  public function get_var($module_name, $name, $default_value=null) {
    $module = model_cache::get("module", $module_name, "name");
    $var = ORM::factory("var")
      ->where("module_id", $module->id)
      ->where("name", $name)
      ->find();
    return $var->loaded ? $var->value : $default_value;
  }

  /**
   * Store a variable for this module
   * @param string $module_name
   * @param string $name
   * @param string $value
   */
  public function set_var($module_name, $name, $value) {
    $module = model_cache::get("module", $module_name, "name");
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
}
