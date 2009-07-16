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
class gallery_theme_Core {
  static function head($theme) {
    $session = Session::instance();
    $buf = "";
    if ($session->get("debug")) {
      $theme->css("modules/gallery/css/debug.css");
    }
    if (($theme->page_type == "album" || $theme->page_type == "photo")
        && access::can("edit", $theme->item())) {
      $theme->css("modules/gallery/css/quick.css");
      $theme->script("modules/gallery/js/quick.js");
    }

    if (module::is_active("rss")) {
      if ($item = $theme->item()) {
        $buf .= rss::feed_link("gallery/album/{$item->id}");
      } else if ($tag = $theme->tag()) {
        $buf .= rss::feed_link("tag/tag/{$tag->id}");
      }
    }

    if ($session->get("l10n_mode", false)) {
      $theme->css("modules/gallery/css/l10n_client.css");
      $theme->script("lib/jquery.cookie.js");
      $theme->script("modules/gallery/js/l10n_client.js");
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
    if ($session->get("debug")) {
      $theme->css("modules/gallery/css/debug.css");
    }

    if ($session->get("l10n_mode", false)) {
      $theme->css("modules/gallery/css/l10n_client.css");
      $theme->script("lib/jquery.cookie.js");
      $theme->script("modules/gallery/js/l10n_client.js");
    }
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
      t(module::get_var("gallery", "credits"),
        array("url" => "http://gallery.menalto.com", "version" => gallery::VERSION)) .
      "</li>";
  }

  static function admin_credits() {
    return gallery_theme::credits();
  }
}