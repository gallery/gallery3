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

return array(
  // Default language locale name(s).
  // First item must be a valid i18n directory name, subsequent items are alternative locales
  // for OS's that don't support the first (e.g. Windows). The first valid locale in the array
  // will be used.
  // @see http://php.net/setlocale
  "language" => array("en_US", "English_United States"),

  // Locale timezone.  Set in 'Advanced' settings, falling back to the server's zone.
  // @see http://php.net/timezones
  "timezone" => (file_exists(VARPATH . "database.php") ?
                 Module::get_var("gallery", "timezone", date_default_timezone_get()) :
                 // Gallery3 is not installed yet -- don't make Module::get_var() calls.
                 date_default_timezone_get()),

  // The locale of the built-in localization messages (locale of strings in translate() calls).
  // This can't be changed easily, unless all localization strings are replaced in all source files
  // as well.
  // Although the actual root is "en_US", the configured root is "en" that all en locales inherit
  // the built-in strings.
  "root_locale" => "en");