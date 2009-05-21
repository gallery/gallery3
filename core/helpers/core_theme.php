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
class core_theme_Core {
  static function head($theme) {
    $session = Session::instance();
    $buf = "";
    if ($session->get("debug")) {
      $buf .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" .
        url::file("core/css/debug.css") . "\" />";
    }
    if (($theme->page_type == "album" || $theme->page_type == "photo")
        && access::can("edit", $theme->item())) {
      $buf .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" .
        url::file("core/css/quick.css") . "\" />";
      $buf .= html::script("core/js/quick.js");
    }
    if ($theme->page_type == "photo" && access::can("view_full", $theme->item())) {
      $buf .= "<script type=\"text/javascript\" >" .
              "  var fullsize_detail = { " .
              "    close: \"" . url::file("core/images/ico-close.png") . "\", " .
              "    url: \"" . $theme->item()->file_url() . "\", " .
              "    width: " . $theme->item()->width . ", " .
              "    height: " . $theme->item()->height . "};" .
              "</script>";
      $buf .= html::script("core/js/fullsize.js");
    }

    if ($session->get("l10n_mode", false)) {
      $buf .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" .
        url::file("core/css/l10n_client.css") . "\" />";
      $buf .= html::script("lib/jquery.cookie.js");
      $buf .= html::script("core/js/l10n_client.js");
    }

    return $buf;
  }

  static function resize_top($theme, $item) {
    if (access::can("edit", $item)) {
      $edit_link = url::site("quick/pane/$item->id?page_type=photo");
      return "<div class=\"gQuick\" href=\"$edit_link\">";
    }
  }

  static function resize_bottom($theme, $item) {
    if (access::can("edit", $item)) {
      return "</div>";
    }
  }

  static function thumb_top($theme, $child) {
    if (access::can("edit", $child)) {
      $edit_link = url::site("quick/pane/$child->id?page_type=album");
      return "<div class=\"gQuick\" href=\"$edit_link\">";
    }
  }

  static function thumb_bottom($theme, $child) {
    if (access::can("edit", $child)) {
      return "</div>";
    }
  }

  static function admin_head($theme) {
    $session = Session::instance();
    $buf = "";
    if ($session->get("debug")) {
      $buf .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" .
        url::file("core/css/debug.css") . "\" />";
    }

    if ($session->get("l10n_mode", false)) {
      $buf .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" .
        url::file("core/css/l10n_client.css") . "\" />";
      $buf .= html::script("lib/jquery.cookie.js");
      $buf .= html::script("core/js/l10n_client.js");
    }

    return $buf;
  }

  static function page_bottom($theme) {
    $session = Session::instance();
    if ($session->get("profiler", false)) {
      $profiler = new Profiler();
      $profiler->render();
    }
    if ($session->get("l10n_mode", false)) {
      return L10n_Client_Controller::l10n_form();
    }

    if ($session->get("after_install")) {
      $session->delete("after_install");
      return new View("after_install_loader.html");
    }
  }

  static function admin_page_bottom($theme) {
    $session = Session::instance();
    if ($session->get("profiler", false)) {
      $profiler = new Profiler();
      $profiler->render();
    }
    if ($session->get("l10n_mode", false)) {
      return L10n_Client_Controller::l10n_form();
    }
  }

  static function credits() {
    return "<li class=\"first\">" .
      t("Powered by <a href=\"%url\">Gallery %version</a>",
        array("url" => "http://gallery.menalto.com",
              "version" => module::get_var("core", "version"))) .
      "</li>";
  }
}