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
class upgrade_checker_Core {
  const CHECK_URL = "http://galleryproject.org/versioncheck/gallery3";
  const AUTO_CHECK_INTERVAL = 604800;  // 7 days in seconds

  /**
   * Return the last version info blob retrieved from the Gallery website or
   * null if no checks have been performed.
   */
  static function version_info() {
    return unserialize(Cache::instance()->get("upgrade_checker_version_info"));
  }

  /**
   * Return true if auto checking is enabled.
   */
  static function auto_check_enabled() {
    return (bool)module::get_var("gallery", "upgrade_checker_auto_enabled");
  }

  /**
   * Return true if it's time to auto check.
   */
  static function should_auto_check() {
    if (upgrade_checker::auto_check_enabled() && random::int(1, 100) == 1) {
      $version_info = upgrade_checker::version_info();
      return (!$version_info ||
              (time() - $version_info->timestamp) > upgrade_checker::AUTO_CHECK_INTERVAL);
    }
    return false;
  }

  /**
   * Fech version info from the Gallery website.
   */
  static function fetch_version_info() {
    $result = new stdClass();
    try {
      list ($status, $headers, $body) = remote::do_request(upgrade_checker::CHECK_URL);
      if ($status == "HTTP/1.1 200 OK") {
        $result->status = "success";
        foreach (explode("\n", $body) as $line) {
          if ($line) {
            list($key, $val) = explode("=", $line, 2);
            $result->data[$key] = $val;
          }
        }
      } else {
        $result->status = "error";
      }
    } catch (Exception $e) {
      Kohana_Log::add("error",
                      sprintf("%s in %s at line %s:\n%s", $e->getMessage(), $e->getFile(),
                              $e->getLine(), $e->getTraceAsString()));
    }
    $result->timestamp = time();
    Cache::instance()->set("upgrade_checker_version_info", serialize($result),
                           array("upgrade"), 86400 * 365);
  }

  /**
   * Check the latest version info blob to see if it's time for an upgrade.
   */
  static function get_upgrade_message() {
    $version_info = upgrade_checker::version_info();
    if ($version_info) {
      if (gallery::RELEASE_CHANNEL == "release") {
        if (version_compare($version_info->data["release_version"], gallery::VERSION, ">")) {
          return t("A newer version of Gallery is available! <a href=\"%upgrade-url\">Upgrade now</a> to version %version",
                   array("version" => $version_info->data["release_version"],
                         "upgrade-url" => $version_info->data["release_upgrade_url"]));
        }
      } else {
        $branch = gallery::RELEASE_BRANCH;
        if (isset($version_info->data["branch_{$branch}_build_number"]) &&
            version_compare($version_info->data["branch_{$branch}_build_number"],
                            gallery::build_number(), ">")) {
          return t("A newer version of Gallery is available! <a href=\"%upgrade-url\">Upgrade now</a> to version %version (build %build on branch %branch)",
                   array("version" => $version_info->data["branch_{$branch}_version"],
                         "upgrade-url" => $version_info->data["branch_{$branch}_upgrade_url"],
                         "build" => $version_info->data["branch_{$branch}_build_number"],
                         "branch" => $branch));
        }
      }
    }
  }
}
