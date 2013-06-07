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
Route::set("rest", "<directory>(/<controller>(/<args>))", array("directory" => "rest"))
  ->filter(function($route, $params, $request) {
      // If we're here, we know that we're in REST mode, even if we have an unmatched
      // route (e.g. "gallery3/rest/nonexistent_resource") - initialize the REST API.
      Rest::init();
      return $params;
    })
  ->defaults(array(
      "controller" => "access_key"
    ));
