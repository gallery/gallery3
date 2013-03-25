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
class Gallery_Hook_GalleryTheme {
  static function head($theme) {
    $session = Session::instance();
    $buf = "";
    $buf .= $theme->css("gallery.css");
    if ($session->get("debug")) {
      $buf .= $theme->css("debug.css");
    }

    if (Module::is_active("rss")) {
      if ($item = $theme->item()) {
        if ($item->is_album()) {
          $buf .= rss::feed_link("gallery/album/{$item->id}");
        } else {
          $buf .= rss::feed_link("gallery/album/{$item->parent()->id}");
        }
      } else if ($tag = $theme->tag()) {
        $buf .= rss::feed_link("tag/tag/{$tag->id}");
      }
    }

    if (count(Locales::installed())) {
      // Needed by the languages block
      $buf .= $theme->script("jquery.cookie.js");
    }

    if ($session->get("l10n_mode", false)) {
      $buf .= $theme->css("l10n_client.css")
        . $theme->script("jquery.cookie.js")
        . $theme->script("l10n_client.js");
    }

    // Add MediaElementJS library
    $buf .= $theme->script("mediaelementjs/mediaelement.js");
    $buf .= $theme->script("mediaelementjs/mediaelementplayer.js");
    $buf .= $theme->css("mediaelementjs/mediaelementplayer.css");
    $buf .= $theme->css("uploadify/uploadify.css");
    return $buf;
  }

  static function admin_head($theme) {
    $buf = $theme->css("gallery.css");
    $buf .= $theme->script("gallery.panel.js");
    $session = Session::instance();
    if ($session->get("debug")) {
      $buf .= $theme->css("debug.css");
    }

    if ($session->get("l10n_mode", false)) {
      $buf .= $theme->css("l10n_client.css");
      $buf .= $theme->script("jquery.cookie.js");
      $buf .= $theme->script("l10n_client.js");
    }
    return $buf;
  }

  static function page_bottom($theme) {
    $session = Session::instance();
    if (Gallery::show_profiler()) {
      Profiler::enable();
      $profiler = new Profiler();
      $profiler->render();
    }
    $content = "";
    if ($session->get("l10n_mode", false)) {
      $content .= Controller_L10n_Client::l10n_form();
    }

    if ($session->get_once("after_install")) {
      $content .= new View("gallery/welcome_message_loader.html");
    }

    if (Identity::active_user()->admin && UpgradeChecker::should_auto_check()) {
      $content .= '<script type="text/javascript">
        $.ajax({url: "' . URL::site("admin/upgrade_checker/check_now?csrf=" .
                                    Access::csrf_token()) . '"});
        </script>';
    }
    return $content;
  }

  static function admin_page_bottom($theme) {
    $session = Session::instance();
    if (Gallery::show_profiler()) {
      Profiler::enable();
      $profiler = new Profiler();
      $profiler->render();
    }

    // Redirect to the root album when the admin session expires.
    $content = '<script type="text/javascript">
      var adminReauthCheck = function() {
        $.ajax({url: "' . URL::site("admin?reauth_check=1") . '",
                dataType: "json",
                success: function(data){
                  if ("location" in data) {
                    document.location = data.location;
                  }
                }});
      };
      setInterval("adminReauthCheck();", 60 * 1000);
      </script>';

    if (UpgradeChecker::should_auto_check()) {
      $content .= '<script type="text/javascript">
        $.ajax({url: "' . URL::site("admin/upgrade_checker/check_now?csrf=" .
                                    Access::csrf_token()) . '"});
        </script>';
    }

    if ($session->get("l10n_mode", false)) {
      $content .= "\n" . Controller_L10n_Client::l10n_form();
    }
    return $content;
  }

  static function credits() {
    $version_string = SafeString::of_safe_html(
      '<bdo dir="ltr">Gallery ' . Gallery::version_string() . '</bdo>');
    return "<li class=\"g-first\">" .
      t(Module::get_var("gallery", "credits"),
        array("url" => "http://galleryproject.org",
              "gallery_version" => $version_string)) .
      "</li>";
  }

  static function admin_credits() {
    return Hook_GalleryTheme::credits();
  }

  static function body_attributes() {
    if (Locales::is_rtl()) {
      return 'class="rtl"';
    }
  }
}