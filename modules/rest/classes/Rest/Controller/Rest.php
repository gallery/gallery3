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
/**
 * The base class for Gallery's REST API.  All REST resources should be extensions of this class.
 *
 * Note: Kohana includes custom headers from the $_SERVER array in HTTP::request_headers(),
 * so it's sufficient to look in $this->request->headers().
 */
abstract class Rest_Controller_Rest extends Controller {
  public $allow_private_gallery = true;

  public $uploads = array();
  public $entity = array();
  public $members = array();

  public function check_auth($auth) {
    // Get the access key (if provided) and attempt to login the user.
    $key = $this->request->headers("x-gallery-request-key");
    if (empty($key)) {
      $key = ($this->request->method == HTTP_Request::GET) ?
              $this->request->query("access_key") : $this->request->post("access_key");
    }

    Rest::set_active_user($key);

    return parent::check_auth($auth);
  }

  public function before() {
    parent::before();

    // If the X-Gallery-Request-Method header is defined, use it as the method.
    // Otherwise, the method detected by the Request object will be retained.
    if ($method = $this->request->headers("x-gallery-request-method")) {
      $this->request->method(strtoupper($method));
    }

    // If the method is not one of GET, POST, PUT, or DELETE, fire a 405 Method Not Allowed.
    if (!in_array($this->request->method(), array(
        HTTP_Request::GET,
        HTTP_Request::POST,
        HTTP_Request::PUT,
        HTTP_Request::DELETE))) {
      throw HTTP_Exception::factory(405);
    }

    // Set the action as the method.
    $this->request->action(strtolower($this->request->method()));

    // If using POST or PUT, check for and process any uploads, storing them in $this->uploads.
    // Example: $_FILES["file"] will be stored in $this->uploads["file"], and will have uploaded
    // filename $this->uploads["file"]["name"] and temp path $this->uploads["file"]["tmp_name"].
    if (isset($_FILES) && in_array($this->request->method(), array(
        HTTP_Request::POST,
        HTTP_Request::PUT))) {
      foreach ($_FILES as $key => $file_array) {
        if (!$file_array["tmp_name"] = Upload::save($file_array)) {
          // Upload failed validation - fire a 400 Bad Request.
          throw HTTP_Exception::factory(400);
        }

        $this->uploads[$key] = $file_array;
        System::delete_later($path);
      }
    }

    // Process the entity and members parameters, if specified.
    foreach (array("entity", "members") as $key) {
      $value = ($this->request->method == HTTP_Request::GET) ?
                $this->request->query($key) : $this->request->post($key);
      if (isset($value)) {
        $this->$key = json_decode($value);
      }
    }
  }

  /**
   * Overload Controller::execute() to translate any Exception that isn't already an HTTP_Exception
   * to a Rest_Exception (which, itself, returns an HTTP_Exception).
   *
   * @see  Controller::execute()
   * @see  Gallery_Controller::execute()
   */
  public function execute() {
    try {
      return parent::execute();
    } catch (Exception $e) {
      if (!($e instanceof HTTP_Exception)) {
        if ($e instanceof ORM_Validation_Exception) {
          $code = 400;
          $message = $e->errors();
        } else {
          $code = 500;
          $message = $e->getMessage();
        }

        $e = Rest_Exception::factory($code, $message, null, $e->getPrevious());
      }

      throw $e;
    }
  }

  public function gallery_30x_call($function, $args) {
    try {
      $request = new stdClass();

      switch ($method = strtolower($_SERVER["REQUEST_METHOD"])) {
      case "get":
        $request->params = (object) $this->request->query();
        break;

      default:
        $request->params = (object) $this->request->post();
        if (isset($_FILES["file"])) {
          $request->file = Upload::save("file");
          System::delete_later($request->file);
        }
        break;
      }

      if (isset($request->params->entity)) {
        $request->params->entity = json_decode($request->params->entity);
      }
      if (isset($request->params->members)) {
        $request->params->members = json_decode($request->params->members);
      }

      $request->method = strtolower(Arr::get($_SERVER, "HTTP_X_GALLERY_REQUEST_METHOD", $method));
      $request->access_key = $_SERVER["HTTP_X_GALLERY_REQUEST_KEY"];

      if (empty($request->access_key) && !empty($request->params->access_key)) {
        $request->access_key = $request->params->access_key;
      }

      $request->url = $this->request->url(true) . URL::query();

      Rest::set_active_user($request->access_key);

      $handler_class = "Hook_Rest_" . Inflector::convert_module_to_class_name($function);
      $handler_method = $request->method;

      if (!class_exists($handler_class) || !method_exists($handler_class, $handler_method)) {
        throw Rest_Exception::factory(400);
      }

      if (($handler_class == "Hook_Rest_Data") && isset($request->params->m)) {
        // Set the cache buster value as the etag, use to check if cache needs refreshing.
        // This is easiest to do at the controller level, hence why it's here.
        $this->check_cache($request->params->m);
      }

      $response = call_user_func(array($handler_class, $handler_method), $request);
      if ($handler_method == "post") {
        // post methods must return a response containing a URI.
        $this->response->status(201)->headers("Location", $response["url"]);
      }
      Rest::reply($response, $this->response);
    } catch (ORM_Validation_Exception $e) {
      // Note: this is totally insufficient because it doesn't take into account localization.  We
      // either need to map the result values to localized strings in the application code, or every
      // client needs its own l10n string set.
      throw Rest_Exception::factory(400, $e->errors());
    } catch (HTTP_Exception_404 $e) {
      throw Rest_Exception::factory(404);
    }
  }
}
