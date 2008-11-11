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
abstract class REST_Controller extends Controller {
  protected $resource_type = null;

  public function dispatch($id) {
    if ($this->resource_type == null) {
      throw new Exception("@todo ERROR_MISSING_RESOURCE_TYPE");
    }

    // @todo this needs security checks
    $resource = ORM::factory($this->resource_type)->where("id", $id)->find();
    if (!$resource->loaded) {
      return Kohana::show_404();
    }

    /**
     * We're expecting to run in an environment that only supports GET/POST, so expect to tunnel
     * PUT/DELETE through POST.
     */
    if (request::method() == "get") {
      $this->get($resource);

      if (Session::instance()->get("use_profiler", false)) {
        $profiler = new Profiler();
        print $profiler->render();
      }
      return;
    }

    switch ($this->input->post("__action")) {
    case "put":
      return $this->put($resource);

    case "delete":
      return $this->delete($resource);

    default:
      return $this->post($resource);
    }
  }

  /**
   * Perform a GET request on this resource
   * @param ORM $resource the instance of this resource type
   */
  abstract public function get($resource);

  /**
   * Perform a PUT request on this resource
   * @param ORM $resource the instance of this resource type
   */
  abstract public function put($resource);

  /**
   * Perform a POST request on this resource
   * @param ORM $resource the instance of this resource type
   */
  abstract public function post($resource);

  /**
   * Perform a DELETE request on this resource
   * @param ORM $resource the instance of this resource type
   */
  abstract public function delete($resource);
}
