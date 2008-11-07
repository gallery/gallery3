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
    $this->template->syscheck->modules = array();
    $this->template->album_count = 0;
    $this->template->photo_count = 0;
    $this->template->deepest_photo = null;
    try {
      $old_handler = set_error_handler(array("Welcome_Controller", "_error_handler"));
      $this->template->syscheck->modules = $this->_read_modules();
      $this->template->album_count = ORM::factory("item")->where("type", "album")->count_all();
      $this->template->photo_count = ORM::factory("item")->where("type", "photo")->count_all();
      $this->template->deepest_photo = ORM::factory("item")
        ->where("type", "photo")->orderby("level", "desc")->find();
    } catch (Exception $e) {
    }
    set_error_handler($old_handler);

    $this->_create_directories();
  }

  function install($module_name) {
    call_user_func(array("{$module_name}_installer", "install"));
    url::redirect("welcome");
  }

  function uninstall($module_name) {
    if ($module_name == "core") {
      // We have to uninstall all other modules first, else their tables, etc don't
      // get cleaned up.
      foreach (ORM::factory("module")->find_all() as $module) {
        if ($module->name != "core" && $module->version) {
          call_user_func(array("{$module->name}_installer", "uninstall"));
        }
      }
    }
    call_user_func(array("{$module_name}_installer", "uninstall"));
    url::redirect("welcome");
  }

  function mptt() {
    $this->auto_render = false;
    $items = ORM::factory("item")->orderby("id")->find_all();
    $data = "digraph G {\n";
    foreach ($items as $item) {
      $data .= "  $item->parent_id -> $item->id\n";
      $data .= "  $item->id [label=\"$item->id <$item->left, $item->right>\"]\n";
    }
    $data .= "}\n";

    if ($this->input->get("type") == "text") {
      print "<pre>$data";
    } else {
      $proc = proc_open("/usr/bin/dot -Tsvg",
                        array(array("pipe", "r"),
                              array("pipe", "w")),
                        $pipes,
                        VARPATH . "tmp");
      fwrite($pipes[0], $data);
      fclose($pipes[0]);

      header("Content-Type: image/svg+xml");
      print(stream_get_contents($pipes[1]));
      fclose($pipes[1]);
      proc_close($proc);
    }
  }

  function add($count) {
    srand(time());
    $parents = ORM::factory("item")->where("type", "album")->find_all()->as_array();
    for ($i = 0; $i < $count; $i++) {
      $parent = $parents[array_rand($parents)];
      if (!rand(0, 10)) {
        $parents[] = album::create($parent->id, "rnd_" . rand(), "Rnd $i", "rnd $i")
          ->set_thumbnail(DOCROOT . "core/tests/test.jpg", 200, 150)
          ->save();
      } else {
        photo::create($parent->id, DOCROOT . "themes/default/images/thumbnail.jpg",
                      "thumbnail.jpg", "rnd_" . rand(), "sample thumbnail");
      }

      if (!($i % 100)) {
        set_time_limit(30);
      }
    }
    url::redirect("welcome");
  }

  public function profiler() {
    Session::instance()->set("use_profiler", $this->input->get("use_profiler", false));
    url::redirect("welcome");
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
      $error->message = "Missing: $db_php <br/> Run the following commands...";
      $error->instructions[] = "cp " . DOCROOT . "kohana/config/database.php $db_php";
      $error->instructions[] = "chmod 644 $db_php";
      $error->message2 = "...then edit this file and enter your database configuration settings.";
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
