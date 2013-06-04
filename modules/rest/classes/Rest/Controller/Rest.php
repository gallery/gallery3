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
      throw Rest_Exception::factory(405);
    }

    // Set the action as the method.
    $this->request->action(strtolower($this->request->method()));

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
    if (isset($_FILES) && in_array($this->request->method(), array(
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
    $param_func = ($this->request->method == HTTP_Request::GET) ? "query" : "post";
    foreach (array("entity", "members") as $key) {
      $value = $this->request->$param_func($key);
      if (isset($value)) {
        $this->request->$param_func($key) = json_decode($value);
      }
    }
  }

  public function after() {
    // Get the data and output format, which will default to json unless we've used
    // the GET method and specified the "output" query parameter.
    $data = $this->response->body();
    $output = Arr::get($this->request->query(), "output", "json");

    // Reformat the response body based on the output format
    switch ($output) {
    case "json":
      $this->headers("content-type", "application/json; charset=" . Kohana::$charset);
      $this->response->body(json_encode($data));
      break;

    case "jsonp":
      if (!$callback = $this->request->query("callback")) {
        throw Rest_Exception::factory(400, array("callback" => "missing"));
      }

      if (!preg_match('/^[$A-Za-z_][0-9A-Za-z_]*$/', $callback)) {
        throw Rest_Exception::factory(400, array("callback" => "invalid"));
      }

      $this->headers("content-type", "application/javascript; charset=" . Kohana::$charset);
      $this->response->body("$callback(" . json_encode($data) . ")");
      break;

    case "html":
      $html = !$data ? t("Empty response") : preg_replace(
        "#([\w]+?://[\w]+[^ \'\"\n\r\t<]*)#ise", "'<a href=\"\\1\" >\\1</a>'",
        var_export($data, true));

      $this->headers("content-type", "text/html; charset=" . Kohana::$charset);
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
