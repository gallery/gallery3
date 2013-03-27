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
class Tag_Name_Controller extends Controller {
  public function __call($function, $args) {
    $tag_name = $function;
    $tag = ORM::factory("tag")->where("name", "=", $tag_name)->find();
    if (!$tag->loaded()) {
      // No matching tag was found. If this was an imported tag, this is probably a bug.
      // If the user typed the URL manually, it might just be wrong
      throw new Kohana_404_Exception();
    }

    url::redirect($tag->abs_url());
  }

}
