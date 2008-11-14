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
/**
 * This abstract controller makes it easy to create a RESTful controller.  To use it, create a
 * subclass which defines the resource type and implements get/post/put/delete methods, like this:
 *
 * class Comment_Controller extends REST_Controller {
 *   protected $resource_type = "comment";  // this tells REST which model to use
 *
 *   public function _get(ORM $comment) {
 *     // Handle GET request
 *   }
 *
 *   public function _put(ORM $comment) {
 *     // Handle PUT request
 *   }
 *
 *   public function _post(ORM $comment) {
 *     // Handle POST request
 *   }
 *
 *   public function _delete(ORM $comment) {
 *     // Handle DELETE request
 *   }
 * }
 *
 * A request to http://example.com/gallery3/comment/3 will result in a call to
 * REST_Controller::dispatch(3) which will load up the comment associated with id 3.  If there's
 * no such comment, it returns a 404.  Otherwise, it will then delegate to
 * Comment_Controller::get() with the ORM instance as an argument.
 */
abstract class REST_Controller extends Controller {
  protected $resource_type = null;

  public function dispatch($id=null) {
    if ($this->resource_type == null) {
      throw new Exception("@todo ERROR_MISSING_RESOURCE_TYPE");
    }

    if ($id != null) {
      // @todo this needs security checks
      $resource = ORM::factory($this->resource_type, $id);
      if (!$resource->loaded) {
        return Kohana::show_404();
      }
    } else if (request::method() == "get") {
      // A null id and a request method of "get" just returns an empty form
      // @todo figure out how to handle the input without and id
      // @todo do we use put for create and post for update?
      $resource = null;
    } else {
      return Kohana::show_404();
    }
    /**
     * We're expecting to run in an environment that only supports GET/POST, so expect to tunnel
     * PUT/DELETE through POST.
     */
    if (request::method() == "get") {
      $this->_get($resource);

      if (Session::instance()->get("use_profiler", false)) {
        $profiler = new Profiler();
        print $profiler->render();
      }
      return;
    }

    switch ($this->input->post("_method")) {
    case "put":
      return $this->_put($resource);

    case "delete":
      return $this->_delete($resource);

    default:
      return $this->_post($resource);
    }
  }

  /**
   * Perform a GET request on this resource
   * @param ORM $resource the instance of this resource type
   */
  abstract public function _get($resource);

  /**
   * Perform a PUT request on this resource
   * @param ORM $resource the instance of this resource type
   */
  abstract public function _put($resource);

  /**
   * Perform a POST request on this resource
   * @param ORM $resource the instance of this resource type
   */
  abstract public function _post($resource);

  /**
   * Perform a DELETE request on this resource
   * @param ORM $resource the instance of this resource type
   */
  abstract public function _delete($resource);
}
