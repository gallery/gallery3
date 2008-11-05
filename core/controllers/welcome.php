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
class Welcome_Controller extends Template_Controller {
  public $template = "welcome.html";

  function index() {
    $this->template->syscheck = new View("welcome_syscheck.html");
    $this->template->syscheck->errors = $this->_get_config_errors();
    $this->template->syscheck->modules = $this->_read_modules();
    $this->template->album_count = 0;
    $this->template->photo_count = 0;
    try {
      $this->template->album_count = ORM::factory("item")->where("type", "album")->count_all();
      $this->template->photo_count = ORM::factory("item")->where("type", "photo")->count_all();
    } catch (Exception $e) {
    }

    $this->_create_directories();
  }

  function install($module) {
    call_user_func(array("{$module}_installer", "install"));
    url::redirect("welcome");
  }

  function uninstall($module) {
    call_user_func(array("{$module}_installer", "uninstall"));
    url::redirect("welcome");
  }

  function add($count) {
    srand(time());
    $parents = ORM::factory("item")->where("type", "album")->find_all()->as_array();
    for ($i = 0; $i < $count; $i++) {
      $parent = $parents[array_rand($parents)];
      switch(rand(0, 1)) {
      case 0:
        $album = album::create($parent->id, "rnd_" . rand(), "Rnd $i", "rnd $i");
        $parents[] = $album;
        break;

      case 1:
        photo::create($parent->id, DOCROOT . "themes/default/images/thumbnail.jpg",
                      "thumbnail.jpg", "rnd_" . rand(), "sample thumbnail");
        break;
      }

      print "$i ";
      if (!($i % 100)) {
        set_time_limit(30);
      }
    }
    print "<br/>";
    print html::anchor("welcome", "return");
    $this->auto_render = false;
  }

  private function _get_config_errors() {
    $errors = array();
    if (!file_exists(VARPATH)) {
      $error = new stdClass();
      $error->message = "Missing: " . VARPATH;
      $error->instructions[] = "mkdir " . VARPATH;
      $error->instructions[] = "chmod 777 " . VARPATH;
      $errors[] = $error;
    } else if (!is_writable(VARPATH)) {
      $error = new stdClass();
      $error->message = "Not writable: " . VARPATH;
      $error->instructions[] = "chmod 777 " . VARPATH;
      $errors[] = $error;
    }

    $db_php = VARPATH . "database.php";
    if (!file_exists($db_php)) {
      $error = new stdClass();
      $error->message = "Missing: $db_php";
      $error->instructions[] = "cp kohana/config/database.php $db_php";
      $error->instructions[] = "chmod 644 $db_php";
      $error->message2 = "Then edit this file and enter your database configuration settings.";
      $errors[] = $error;
    } else if (!is_readable($db_php)) {
      $error = new stdClass();
      $error->message = "Not readable: $db_php";
      $error->instructions[] = "chmod 644 $db_php";
      $error->message2 = "Then edit this file and enter your database configuration settings.";
      $errors[] = $error;
    } else {
      $old_handler = set_error_handler(array("Welcome_Controller", "_error_handler"));
      try {
        Database::instance()->connect();
      } catch (Exception $e) {
        $error = new stdClass();
        $error->message = "Database error: {$e->getMessage()}";
        $db_name = Kohana::config("database.default.connection.database");
        if (strchr($error->message, "Unknown database")) {
          $error->instructions[] = "mysqladmin -uroot create $db_name";
        } else {
          $error->instructions = array();
          $error->message2 = "Check " . VARPATH . "database.php";
        }
        $errors[] = $error;
      }
      set_error_handler($old_handler);
    }

    return $errors;
  }

  function _error_handler($x) {
  }

  function _create_directories() {
    foreach (array("logs") as $dir) {
      @mkdir(VARPATH . "$dir");
    }
  }

  /**
   * Create an array of all the modules that are install or available and the version number
   * @return array(moduleId => version)
   */
  private function _read_modules() {
    $modules = array();
    try {
      $installed = ORM::factory("module")->find_all();
      foreach ($installed as $installed_module) {
        $modules[$installed_module->name] = $installed_module->version;
      }

      foreach (glob(MODPATH . "*/helpers/*_installer.php") as $file) {
        if (empty($modules[basename(dirname(dirname($file)))])) {
          $modules[basename(dirname(dirname($file)))] = 0;
        }
      }
    } catch (Exception $e) {
      // The database may not be installed
    }
    return $modules;
  }
}
