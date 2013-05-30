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
Route::set("tag", "tag(/<tag_url>)",
           array("tag_url" => "[A-Za-z0-9-_/]++")) // Ref: Model_Tag::valid_slug, Route::REGEX_SEGMENT
  ->defaults(array(
      "controller" => "tag",
      "action" => "show"
    ));

// This route is for Gallery 3.0.x tag_name URLs, and will fire a 301 redirect to the canonical URL.
Route::set("tag_name", "tag_name/<args>",
           array("args" => "[^.,;?\\n]++"))
  ->defaults(array(
      "controller" => "tag",
      "action" => "find_by_name"
    ));
