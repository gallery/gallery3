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
class user_event_Core {
  /**
   * Initialization.
   */
  static function gallery_ready() {
    user::load_user();
    self::set_request_locale();
  }

  static function admin_menu($menu, $theme) {
    $menu->add_after("appearance_menu",
                     Menu::factory("link")
                     ->id("users_groups")
                     ->label(t("Users/Groups"))
                     ->url(url::site("admin/users")));
  }

  static function set_request_locale() {
    // 1. Check the session specific preference (cookie)
    $locale = user::cookie_locale();
    // 2. Check the user's preference
    if (!$locale) {
      $locale = user::active()->locale;
    }
    // 3. Check the browser's / OS' preference
    if (!$locale) {
      $locale = locales::locale_from_http_request();
    }
    // If we have any preference, override the site's default locale
    if ($locale) {
      I18n::instance()->locale($locale);
    }
  }
}
