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

  // REST response used by Controller_Rest::after() to generate the Response body.  Since
  // the default action_get() sets this and since POST/PUT/DELETE typically have no output,
  // most resources don't need to access this directly.
  // @see  Controller_Rest::after()
  public $rest_response = array();

  // REST resource type and id.  These are set in Controller_Rest::before().
  public $rest_type;
  public $rest_id;

  // Default REST query parameters.  These can be altered as needed in each resource class.
  public static $default_params = array(
    "start" => 0,
    "num" => 100,
    "expand_members" => false,
    "type" => null,
    "access_key" => null,
    "output" => "json"
  );

  /**
   * Get the REST access key (if provided), attempt to login the user, and check auth.
   * The only two possible results are a successful login or a 403 Forbidden.  Because
   * of this, the $auth variable is simply passed through without modification.
   *
   * NOTE: this doesn't extend Controller::check_auth(), but rather *replaces* it with
   * its restful counterpart (i.e. parent::check_auth() is never called).
   *
   * @see  Controller::check_auth(), which is replaced by this implementation
   * @see  Controller::auth_for_private_gallery()
   * @see  Controller::auth_for_maintenance_mode()
   * @see  Rest::set_active_user()
   */
  public function check_auth($auth) {
    // Get the access key (if provided)
    $key = $this->request->headers("x-gallery-request-key");
    if (empty($key)) {
      $key = ($this->request->method() == HTTP_Request::GET) ?
              $this->request->query("access_key") : $this->request->post("access_key");
    }

    // Attempt to login the user.  This will fire a 403 Forbidden if unsuccessful.
    Rest::set_active_user($key);

    // Check for maintenance mode or private gallery restrictions.  Since there is no
    // redirection to login/reauthenticate screen in REST, fire a 403 Forbidden if found.
    if ($this->auth_for_maintenance_mode() || $this->auth_for_private_gallery()) {
      throw Rest_Exception::factory(403);
    }

    return $auth;
  }

  /**
   * Overload Controller::before() to process the Request object for REST.
   */
  public function before() {
    parent::before();

    // Check if the X-Gallery-Request-Method header is defined.
    // @todo: consider checking other common REST method overrides, such as
    // X-HTTP-Method (Microsoft), X-HTTP-Method-Override (Google/GData), X-METHOD-OVERRIDE, etc.
    if ($method = strtoupper($this->request->headers("x-gallery-request-method"))) {
      // Set the X-Gallery-Request-Method header as the method.
      $this->request->method($method);
    } else {
      // Leave the method as detected by the Request object, but get a local copy.
      $method = $this->request->method();
    }

    // If the method is not one of GET, POST, PUT, or DELETE, fire a 405 Method Not Allowed.
    if (!in_array($method, Rest::$allowed_methods)) {
      throw Rest_Exception::factory(405);
    }

    // Set the action as the method.
    $this->request->action(strtolower($method));

    // Get the REST type and id (note: strlen("Controller_Rest_") --> 16).
    $this->rest_type = Inflector::convert_class_to_module_name(substr(get_class($this), 16));
    $this->rest_id = $this->request->arg_optional(0);

    // Process some additional fields, depending on the method.
    switch ($method) {
    case HTTP_Request::GET:
      // Process the "types" list, if specified.
      if ($types = $this->request->query("types")) {
        $types = explode(",", trim($types, ","));
        // If one or more of the types is invalid, fire a 400 Bad Request.
        if (array_diff($types, array("album", "photo", "movie"))) {
          throw Rest_Exception::factory(400, array("types" => "invalid"));
        }
        $this->request->query("types", $types);
      }
      break;

    case HTTP_Request::PUT:
    case HTTP_Request::POST:
      // Check for and process any uploads, storing them along with the other
      // request-related parameters in $this->request->post().
      // Example: $_FILES["file"], if valid, will be processed and stored to produce something like:
      //   $this->request->post("file") = array(
      //     "name"     => "foobar.jpg",
      //     "tmp_name" => "/path/to/gallery3/var/tmp/uniquified_temp_filename.jpg",
      //     "size"     => 1234,
      //     "type"     => "image/jpeg",
      //     "error"    => UPLOAD_ERR_OK
      //   );
      if (isset($_FILES)) {
        foreach ($_FILES as $key => $file_array) {
          // If $this->request->post() already has an element of the same name or the upload
          // failed validation, fire a 400 Bad Request.
          if ($this->request->post($key) || (!$path = Upload::save($file_array))) {
            throw Rest_Exception::factory(400, array($key => "upload_failed"));
          }

          $file_array["tmp_name"] = $path;
          $this->request->post($key, $file_array);
          System::delete_later($path);
        }
      }
      // Process the "entity", "members", and "relationships" parameters, if specified.
      foreach (array("entity", "members", "relationships") as $key) {
        $value = $this->request->post($key);
        if (isset($value)) {
          $this->request->post($key, json_decode($value));
        }
      }
      break;
    }
  }

  /**
   * Overload Controller::after() to process the Response object for REST.
   */
  public function after() {
    // Get the output format, which will default to json unless we've used
    // the GET method and specified the "output" query parameter.
    $output = Arr::get($this->request->query(), "output", "json");

    // Format $this->rest_response into the Response body based on the output format
    switch ($output) {
    case "json":
      $this->response->headers("content-type", "application/json; charset=" . Kohana::$charset);
      $this->response->body(json_encode($this->rest_response));
      break;

    case "jsonp":
      if (!$callback = $this->request->query("callback")) {
        throw Rest_Exception::factory(400, array("callback" => "missing"));
      }

      if (!preg_match('/^[$A-Za-z_][0-9A-Za-z_]*$/', $callback)) {
        throw Rest_Exception::factory(400, array("callback" => "invalid"));
      }

      $this->response->headers("content-type", "application/javascript; charset=" . Kohana::$charset);
      $this->response->body("$callback(" . json_encode($this->rest_response) . ")");
      break;

    case "html":
      $html = !$this->rest_response ? t("Empty response") : preg_replace(
        "#([\w]+?://[\w]+[^ \'\"\n\r\t<]*)#ise", "'<a href=\"\\1\" >\\1</a>'",
        var_export($this->rest_response, true));

      $this->response->headers("content-type", "text/html; charset=" . Kohana::$charset);
      $this->response->body("<pre>$html</pre>");

      // @todo: the profiler needs to be updated for K3.
      if (Gallery::show_profiler()) {
        Profiler::enable();
        $profiler = new Profiler();
        $profiler->render();
      }
      break;

    default:
      throw Rest_Exception::factory(400, array("output" => "invalid"));
    }

    parent::after();
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
          throw Rest_Exception::factory(400, $e->errors(), null, $e->getPrevious());
        } else {
          throw Rest_Exception::factory(500, $e->getMessage(), null, $e->getPrevious());
        }
      }
      throw $e;
    }
  }

  /**
   * GET a typical REST response.  This generates the REST response following Gallery's
   * standard format, and expands members if specified.
   *
   * While some resources are different enough to warrant their own action_get() function,
   * (e.g. data, tree, registry), most resources can use this default implementation.
   */
  public function action_get() {
    $this->check_method();

    if (Arr::get($this->request->query(), "expand_members",
        static::$default_params["expand_members"])) {
      $members = Rest::get_members($this->rest_type, $this->rest_id, $this->request->query());
      if (!isset($members)) {
        // A null members array means the resource has no members function - fire a 400 Bad Request.
        throw Rest_Exception::factory(400, array("expand_members" => "not_a_collection"));
      }

      foreach ($members as $key => $member) {
        $this->rest_response[$key] = Rest::get_resource($member);
      }
    } else {
      $this->rest_response =
        Rest::get_resource($this->rest_type, $this->rest_id, $this->request->query());
    }
  }

  /**
   * PUT a typical REST resource.  As needed, this runs put_entity() and put_members() for
   * the resource, as well as put_members() for the resource's relationships.
   */
  public function action_put() {
    $this->check_method();

    if (Arr::get($this->request->post(), "entity")) {
      Rest::put_entity($this->rest_type, $this->rest_id, $this->request->post());
    }

    if (Arr::get($this->request->post(), "members")) {
      Rest::put_members($this->rest_type, $this->rest_id, $this->request->post());
    }

    $put_rels = $this->request->post("relationships");
    if (isset($put_rels)) {
      $actual_rels = Rest::relationships($this->rest_type, $this->rest_id);
      foreach ($put_rels as $r_key => $r_params) {
        if (empty($actual_rels[$r_key])) {
          // The resource doesn't have the relationship type specified - fire a 400 Bad Request.
          throw Rest_Exception::factory(400, array("relationships" => "invalid"));
        }

        Rest::put_members($actual_rels[$r_key][0], Arr::get($actual_rels[$r_key], 1), $r_params);
      }
    }
  }

  /**
   * POST a typical REST resource.  As needed, this runs post_entity() and post_members() for
   * the resource, as well as post_members() for the resource's relationships, as well as setting
   * the response body as array("url" => $url).
   *
   * By default, a successful POST returns a 201 response with a "Location" header.  However,
   * if a post_entity() function decides that the resource already exists, they can override this
   * by adding a *fourth* element to the returned array that's false (e.g. posting a tag that
   * already exists should return array("tag", 123, null, false)).
   */
  public function action_post() {
    $this->check_method();

    if (Arr::get($this->request->post(), "entity")) {
      $result = Rest::post_entity($this->rest_type, $this->rest_id, $this->request->post());
    }

    if (Arr::get($this->request->post(), "members")) {
      Rest::post_members($this->rest_type, $this->rest_id, $this->request->post());
    }

    $post_rels = $this->request->post("relationships");
    if (isset($post_rels)) {
      $actual_rels = Rest::relationships($this->rest_type, $this->rest_id);
      foreach ($post_rels as $r_key => $r_params) {
        if (empty($actual_rels[$r_key])) {
          // The resource doesn't have the relationship type specified - fire a 400 Bad Request.
          throw Rest_Exception::factory(400, array("relationships" => "invalid"));
        }

        Rest::post_members($actual_rels[$r_key][0], Arr::get($actual_rels[$r_key], 1), $r_params);
      }
    }

    if (!empty($result)) {
      // Set the response body.
      $url = Rest::url($result);
      $this->rest_response = array("url" => $url);
    }

    if (Arr::get($result, 3, true)) {
      // New resource - set the status and headers.
      $this->response->status(201);
      $this->response->headers("location", $url);
    }
  }

  /**
   * DELETE a typical REST resource.
   */
  public function action_delete() {
    $this->check_method();

    Rest::delete($this->rest_type, $this->rest_id, $this->request->post());
  }

  /**
   * Check if a method is defined for this resource.  This is called by the standard
   * implementations of action_get(), action_post(), etc., and fires a 400 Bad Request
   * if they shouldn't be used.  If a resource implements their own actions, this
   * function is (intentionally) not called.
   */
  public function check_method() {
    $method = $this->request->method();

    switch ($method) {
    case HTTP_Request::GET:
    case HTTP_Request::POST:
    case HTTP_Request::PUT:
      if (!method_exists($this, strtolower($method) . "_entity") &&
          !method_exists($this, strtolower($method) . "_members")) {
        throw Rest_Exception::factory(400, array("method" => "invalid"));
      }
      break;

    case HTTP_Request::DELETE:
      if (!method_exists($this, strtolower($method))) {
        throw Rest_Exception::factory(400, array("method" => "invalid"));
      }
      break;
    }
  }
}
