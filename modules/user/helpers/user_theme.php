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
class user_theme_Core {
  static function head($theme) {
    if (count(locales::installed())) {
      // Needed by the languages block
      $theme->script("jquery.cookie.js");
    }
    return "";
  }

  static function header_top($theme) {
    if ($theme->page_type != "login") {
      $view = new View("login.html");
      $view->user = user::active();
      return $view->render();
    }
  }

  static function sidebar_blocks($theme) {
    $locales = locales::installed();
    foreach ($locales as $locale => $display_name) {
      $locales[$locale] = SafeString::of_safe_html($display_name);
    }
    if (count($locales) > 1) {
      $block = new Block();
      $block->css_id = "gUserLanguageBlock";
      $block->title = t("Language Preference");
      $block->content = new View("user_languages_block.html");
      $block->content->installed_locales =
        array_merge(array("" => t("« none »")), $locales);
      $block->content->selected = (string) user::cookie_locale();
      return $block;
    }
  }
}
