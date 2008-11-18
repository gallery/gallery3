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
class Slideshow_Controller extends REST_Controller {
  // @todo this should be retrieved from the slideshow configuration
  public static $LIMIT = 10;

  public function _show($parent, $output_format) {
    $offset = $this->input->get("offset", 0);
    $children = array();
    // @todo actually fill the array
    switch ($output_format) {
    case "json":
      print json_encode($children);
      break;
    case "rss":
      $view = new View("slideshow_feed.rss");
      $view->item = $parent;
      $view->children = $children;
      break;
    default:
      throw new Exception("@todo Unsupported output format: $output_format");
    }
  }

  /**
   * Override the get_output_format.  We want to restrict the check to only $_GET and set the 
   * default to rss.
   * @return string
   */
  protected function get_output_format() {
    return $this->input->get("_format", "rss");
  }
}