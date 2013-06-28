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
class RestAPI_Rest_Batch extends Rest {
  /**
   * This read-only resource runs a batch of REST requests and returns them in a single
   * response.  This is useful for embedding, allowing multiple calls to be made in a
   * single roundtrip.
   *
   * GET returns an array of GET responses for multiple REST URLs
   *   urls=url1,url2,url3
   *     Specified list of REST URLs.  If successful, the corresponding array element
   *     will be the URL's response.  If not, it will be an array with its HTTP response
   *     code (e.g. array("error" => 404)).
   */
  public function get_response() {
    $urls = explode(",", trim(Arr::get($this->params, "urls")));
    if (empty($urls)) {
      throw Rest_Exception::factory(400, array("urls" => "invalid"));
    }

    $results = array();
    foreach ($urls as $url) {
      $rest = RestAPI::resolve($url);
      if (!$rest || ($rest->type == "Data")) {
        $results[] = array("error" => 400);
      } else {
        try {
          $results[] = $rest->get_response();
        } catch (Exception $e) {
          // Provide a (greatly) simplified error response.  Unlike in Controller_Rest::execute(),
          // we don't need to worry about ORM_Validation_Exception errors since this is GET.
          $results[] = array("error" => (($e instanceof HTTP_Exception) ? $e->getCode() : 500));
        }
      }
    }

    return $results;
  }
}
