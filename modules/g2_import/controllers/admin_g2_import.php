<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
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
class Admin_g2_import_Controller extends Admin_Controller {
  public function index() {
    g2_import::lower_error_reporting();
    if (g2_import::is_configured()) {
      g2_import::init();
    }

    $view = new Admin_View("admin.html");
    $view->page_title = t("Gallery 2 import");
    $view->content = new View("admin_g2_import.html");

    if (class_exists("GalleryCoreApi")) {
      $view->content->g2_stats = $g2_stats = g2_import::g2_stats();
      $view->content->g3_stats = $g3_stats = g2_import::g3_stats();
      $view->content->g2_sizes = g2_import::common_sizes();
      $view->content->g2_version = g2_import::version();

      // Don't count tags because we don't track them in g2_map
      $view->content->g2_resource_count =
        $g2_stats["users"] + $g2_stats["groups"] + $g2_stats["albums"] +
        $g2_stats["photos"] + $g2_stats["movies"] + $g2_stats["comments"];
      $view->content->g3_resource_count =
        $g3_stats["user"] + $g3_stats["group"] + $g3_stats["album"] +
        $g3_stats["item"] + $g3_stats["comment"] + $g3_stats["tag"];
    }

    $view->content->form = $this->_get_import_form();
    $view->content->version = "";
    $view->content->thumb_size = module::get_var("gallery", "thumb_size");
    $view->content->resize_size = module::get_var("gallery", "resize_size");

    if (g2_import::is_initialized()) {
      if ((bool)ini_get("eaccelerator.enable") || (bool)ini_get("xcache.cacher")) {
        message::warning(t("The eAccelerator and XCache PHP performance extensions are known to cause issues.  If you're using either of those and are having problems, please disable them while you do your import.  Add the following lines: <pre>%lines</pre> to gallery3/.htaccess and remove them when the import is done.", array("lines" => "\n\n  php_value eaccelerator.enable 0\n  php_value xcache.cacher off\n  php_value xcache.optimizer off\n\n")));
      }

      foreach (array("notification", "search", "exif") as $module_id) {
        if (module::is_active($module_id)) {
          message::warning(
            t("<a href=\"%url\">Deactivating</a> the <b>%module_id</b> module during your import will make it faster",
              array("url" => url::site("admin/modules"), "module_id" => $module_id)));
        }
      }
      if (module::is_active("akismet")) {
        message::warning(
          t("The Akismet module may mark some or all of your imported comments as spam.  <a href=\"%url\">Deactivate</a> it to avoid that outcome.",
            array("url" => url::site("admin/modules"))));
      }
    } else if (g2_import::is_configured()) {
      $view->content->form->configure_g2_import->embed_path->add_error("invalid", 1);
    }
    g2_import::restore_error_reporting();
    print $view;
  }

  public function save() {
    access::verify_csrf();
    g2_import::lower_error_reporting();

    $form = $this->_get_import_form();
    if ($form->validate()) {
      $embed_path = $form->configure_g2_import->embed_path->value;
      if (!is_file($embed_path) && file_exists("$embed_path/embed.php")) {
        $embed_path = "$embed_path/embed.php";
      }

      if (($g2_init_error = g2_import::is_valid_embed_path($embed_path)) == "ok") {
        message::success(t("Gallery 2 path saved"));
        module::set_var("g2_import", "embed_path", $embed_path);
        url::redirect("admin/g2_import");
      } else {
        $form->configure_g2_import->embed_path->add_error($g2_init_error, 1);
      }
    }

    $view = new Admin_View("admin.html");
    $view->content = new View("admin_g2_import.html");
    $view->content->form = $form;
    g2_import::restore_error_reporting();
    print $view;
  }

  public function autocomplete() {
    $directories = array();
    $path_prefix = Input::instance()->get("q");
    foreach (glob("{$path_prefix}*") as $file) {
      if (is_dir($file) && !is_link($file)) {
        $file = html::clean($file);
        $directories[] = $file;

        // If we find an embed.php, include it as well
        if (file_exists("$file/embed.php")) {
          $directories[] = "$file/embed.php";
        }
      }
    }

    ajax::response(implode("\n", $directories));
  }

  private function _get_import_form() {
    $embed_path = module::get_var("g2_import", "embed_path", "");
    $form = new Forge(
      "admin/g2_import/save", "", "post", array("id" => "g-admin-configure-g2-import-form"));
    $group = $form->group("configure_g2_import")->label(t("Configure Gallery 2 Import"));
    $group->input("embed_path")->label(t("Filesystem path to your Gallery 2 embed.php file"))
      ->value($embed_path);
    $group->embed_path->error_messages(
      "invalid", t("The path you entered is not a Gallery 2 installation."));
    $group->embed_path->error_messages(
      "broken", t("Your Gallery 2 install isn't working properly.  Please verify it!"));
    $group->embed_path->error_messages(
      "missing", t("The path you entered does not exist."));
    $group->submit("")->value($embed_path ? t("Change") : t("Continue"));
    return $form;
  }
}