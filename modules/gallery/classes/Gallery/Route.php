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
class Gallery_Route extends Kohana_Route {
  // Default <args> regex - similar to Route::REGEX_SEGMENT, except it allows commas and slashes.
  public static $default_regex = array("args" => "[^.;?\\n]++");

  /**
   * Overload Route::set() to add our default regex and automatically remove underscores
   * from <controller>.  We remove underscores so that "file_proxy" will be routed to "FileProxy"
   * (at Controllers/FileProxy.php) instead of "File_Proxy" (at Controllers/File/Proxy.php).  If a
   * directory is desired, use the <directory> tag (i.e. how "admin" and "rest" routes work).
   *
   * @see  Route::set()
   * @see  Route::matches() - processes <controller> with route, defaults, ucwords, and filters.
   */
  public static function set($name, $uri=null, $regex=null) {
    // Add default regex, which a route can override if desired.
    $regex = isset($regex) ? array_merge(static::$default_regex, $regex) : static::$default_regex;

    // Build route.
    $route = parent::set($name, $uri, $regex);

    // Add filter to remove underscores in <controller>.  This runs *before* any other filter
    // that a route may define.
    $route->filter(function($route, $params, $request) {
      if (isset($params["controller"])) {
        $params["controller"] = str_replace("_", "", $params["controller"]);
      }
      return $params;
    });

    return $route;
  }
}
