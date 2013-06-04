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
      $key = ($this->request->method == HTTP_Request::GET) ?
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
    if ($method = $this->request->headers("x-gallery-request-method")) {
      // Set the X-Gallery-Request-Method header as the method.
      $this->request->method(strtoupper($method));
    } else {
      // Leave the method as detected by the Request object, but get a local copy.
      $method = $this->request->method();
    }

    // If the method is not one of GET, POST, PUT, or DELETE, fire a 405 Method Not Allowed.
    if (!in_array($method, Rest::$allowed_methods)) {
      throw Rest_Exception::factory(405);
    }

    // If the method is not defined for this resource, fire a 400 Bad Request.
    if (!method_exists($this, "action_" . strtolower($method))) {
      throw Rest_Exception::factory(400, array("method" => "invalid"));
    }

    // Set the action as the method.
    $this->request->action(strtolower($method));

    // If using POST or PUT, check for and process any uploads, storing them along with the other
    // request-related parameters in $this->request->post().
    // Example: $_FILES["file"], if valid, will be processed and stored to produce something like:
    //   $this->request->post("file") = array(
    //     "name"     => "foobar.jpg",
    //     "tmp_name" => "/path/to/gallery3/var/tmp/uniquified_temp_filename.jpg",
    //     "size"     => 1234,
    //     "type"     => "image/jpeg",
    //     "error"    => UPLOAD_ERR_OK
    //   );
    if (isset($_FILES) && in_array($method, array(
        HTTP_Request::POST,
        HTTP_Request::PUT))) {
      foreach ($_FILES as $key => $file_array) {
        // If $this->request->post() already has an element of the same name or the upload
        // failed validation, fire a 400 Bad Request.
        if ($this->request->post($key) || (!$path = Upload::save($file_array))) {
          throw Rest_Exception::factory(400, array($key => t("Upload failed")));
        }

        $file_array["tmp_name"] = $path;
        $this->request->post($key, $file_array);
        System::delete_later($path);
      }
    }

    // Process the "entity" and "members" parameters, if specified.
    $param_func = ($method == HTTP_Request::GET) ? "query" : "post";
    foreach (array("entity", "members") as $key) {
      $value = $this->request->$param_func($key);
      if (isset($value)) {
        $this->request->$param_func($key, json_decode($value));
      }
    }
  }

  /**
   * Overload Controller::after() to process the Response object for REST.
   */
  public function after() {
    // Get the data and output format, which will default to json unless we've used
    // the GET method and specified the "output" query parameter.
    $data = $this->response->body();
    $output = Arr::get($this->request->query(), "output", "json");

    // Reformat the response body based on the output format
    switch ($output) {
    case "json":
      $this->response->headers("content-type", "application/json; charset=" . Kohana::$charset);
      $this->response->body(json_encode($data));
      break;

    case "jsonp":
      if (!$callback = $this->request->query("callback")) {
        throw Rest_Exception::factory(400, array("callback" => "missing"));
      }

      if (!preg_match('/^[$A-Za-z_][0-9A-Za-z_]*$/', $callback)) {
        throw Rest_Exception::factory(400, array("callback" => "invalid"));
      }

      $this->response->headers("content-type", "application/javascript; charset=" . Kohana::$charset);
      $this->response->body("$callback(" . json_encode($data) . ")");
      break;

    case "html":
      $html = !$data ? t("Empty response") : preg_replace(
        "#([\w]+?://[\w]+[^ \'\"\n\r\t<]*)#ise", "'<a href=\"\\1\" >\\1</a>'",
        var_export($data, true));

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
   * @todo: the stanzas below are left over from 3.0.x's Controller_Rest::__call(), and
   * haven't yet been re-implemented.  Once finished, delete this.

      if (($handler_class == "Hook_Rest_Data") && isset($request->params->m)) {
        // Set the cache buster value as the etag, use to check if cache needs refreshing.
        // This is easiest to do at the controller level, hence why it's here.
        $this->check_cache($request->params->m);
      }

      if ($handler_method == "post") {
        // post methods must return a response containing a URI.
        $this->response->status(201)->headers("Location", $response["url"]);
      }
   */
}
