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
class RestAPI_Controller_Rest extends Controller {
  // REST response used by Controller_Rest::after() to generate the Response body.
  public $rest_response = array();

  // REST resource object.  This is set in Controller_Rest::before().
  public $rest_object;

  /**
   * Override Controller::check_auth() since REST doesn't have pages for login or reauth redirects.
   * We check maintenance mode here, and handle REST authentication in Controller_Rest::before().
   *
   * NOTE: this doesn't extend Controller::check_auth(), but rather *replaces* it with
   * its restful counterpart (i.e. parent::check_auth() is never called).
   *
   * @see  Controller::check_auth(), which is replaced by this implementation
   */
  public function check_auth($auth) {
    if (Module::get_var("gallery", "maintenance_mode", 0)) {
      throw Rest_Exception::factory(403);
    }

    if (!$this->request->arg_optional(0) && ($this->request->method() == HTTP_Request::POST)) {
      // Request is POST and has no args - check login using "user" and "password" fields in POST.
      if (!Validation::factory($this->request->post())
        ->rule("user", "Auth::validate_login", array(":validation", ":data", "user", "password"))
        ->check()) {
        throw Rest_Exception::factory(403);
      }

      // Success - set the access key.
      $this->request->headers("X-Gallery-Request-Key", RestAPI::access_key());
    }

    return $auth;
  }

  /**
   * Overload Controller::before() to process the Request object for REST.
   */
  public function before() {
    parent::before();

    // Check if the X-Gallery-Request-Method header is defined.
    if ($method = strtoupper($this->request->headers("X-Gallery-Request-Method"))) {
      // Set the X-Gallery-Request-Method header as the method.
      $this->request->method($method);
    } else {
      // Leave the method as detected by the Request object, but get a local copy.
      $method = $this->request->method();
    }

    // If the method is not allowed, fire a 405 Method Not Allowed.
    if (!in_array($method, RestAPI::$allowed_methods)) {
      throw Rest_Exception::factory(405);
    }

    // Set the action as the method.
    $this->request->action(strtolower($method));

    // If we have an OPTIONS request, we're done here.  This intentionally skips login.
    if ($method == HTTP_Request::OPTIONS) {
      return;
    }

    // Get the access key (if provided - key is empty for guest access).
    $key = $this->request->headers("X-Gallery-Request-Key");
    if (empty($key)) {
      $key = ($this->request->method() == HTTP_Request::GET) ?
              $this->request->query("access_key") : $this->request->post("access_key");
    }

    // Disallow JSONP output with access keys (public access only) or if blocked by configuration.
    if ((strtolower($this->request->query("output")) == "jsonp") &&
        ($key || !Module::get_var("rest", "allow_jsonp_output", true))) {
      throw Rest_Exception::factory(403);
    }

    // Attempt to login the user.  This will fire a 403 Forbidden if unsuccessful.
    RestAPI::set_active_user($key);

    // Process the "Origin" header if sent (not required).
    if (($method != HTTP_Request::OPTIONS) &&
        ($origin = $this->request->headers("Origin")) &&
        RestAPI::approve_origin($origin)) {
      $this->response->headers("Access-Control-Allow-Origin", $origin);
    }

    // Process some additional fields, depending on the method.
    switch ($method) {
    case HTTP_Request::GET:
      // Process the "type" list, if specified.
      if ($types = $this->request->query("type")) {
        $types = explode(",", trim($types, ","));
        // If one or more of the types is invalid, fire a 400 Bad Request.
        if (array_diff($types, array("album", "photo", "movie"))) {
          throw Rest_Exception::factory(400, array("type" => "invalid"));
        }
        $this->request->query("type", $types);
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

      // Process the "entity" parameter, if specified.
      $entity = $this->request->post("entity");
      if (isset($entity)) {
        $entity = json_decode($entity);  // as object
        $this->request->post("entity", $entity);
      }

      // Process the "members" parameter, if specified.
      $members = $this->request->post("members");
      if (isset($members)) {
        $members = json_decode($members, true);  // as assoc array
        foreach ($members as $key => $member) {
          $members[$key] = Rest::resolve($member);
          if (!$members[$key]) {
            throw Rest_Exception::factory(400, array("members" => "invalid"));
          }
        }
        $this->request->post("members", $members);
      }

      // Process the "relationships" parameter, if specified.
      $relationships = $this->request->post("relationships");
      if (isset($relationships)) {
        $relationships = json_decode($relationships, true);  // as assoc array (since no entity)
        foreach ($relationships as $type => $relationship) {
          $members = Arr::get($relationship, "members");
          if (isset($members)) {
            foreach ($members as $key => $member) {
              $members[$key] = Rest::resolve($member);
              if (!$members[$key]) {
                throw Rest_Exception::factory(400, array("relationships" => "invalid_members"));
              }
            }
            $relationships[$type]["members"] = $members;
          }
        }
        $this->request->post("relationships", $relationships);
      }

      break;
    }

    // Build the main REST object
    $type   = $this->request->arg_optional(0);
    $id     = $this->request->arg_optional(1);
    $params = ($method == HTTP_Request::GET) ? $this->request->query() : $this->request->post();

    // If the resource type is empty (i.e. login), change the action.
    if (empty($type)) {
      $this->request->action("show_access_key");
      return;
    }

    $type = Inflector::convert_module_to_class_name($type);
    if (in_array($type, array("Item", "Tag", "Comment"))) {
      // Re-route singular item/tag/comment URLs from 3.0
      $type .= "s";
    }

    if (!class_exists("Rest_$type")) {
      throw Rest_Exception::factory(400, array("resource_type" => "invalid"));
    }

    $this->rest_object = Rest::factory($type, $id, $params);
  }

  /**
   * Overload Controller::after() to process the Response object for REST.
   */
  public function after() {
    // Get the output format, which will default to json unless we've used
    // the GET method and specified the "output" query parameter.
    $output = strtolower(Arr::get($this->request->query(), "output", "json"));

    // Format $this->rest_response into the Response body based on the output format
    switch ($output) {
    case "json":
      $this->response->headers("Content-Type", "application/json; charset=" . Kohana::$charset);
      $this->response->body(json_encode($this->rest_response));
      break;

    case "jsonp":
      if (!$callback = $this->request->query("callback")) {
        throw Rest_Exception::factory(400, array("callback" => "missing"));
      }

      if (!preg_match('/^[$A-Za-z_][0-9A-Za-z_]*$/', $callback)) {
        throw Rest_Exception::factory(400, array("callback" => "invalid"));
      }

      $this->response->headers("Content-Type", "application/javascript; charset=" . Kohana::$charset);
      $this->response->body("$callback(" . json_encode($this->rest_response) . ")");
      break;

    case "html":
      $html = !$this->rest_response ? t("Empty response") : preg_replace(
        "#([\w]+?://[\w]+[^ \'\"\n\r\t<]*)#ise", "'<a href=\"\\1\" >\\1</a>'",
        var_export($this->rest_response, true));

      $this->response->headers("Content-Type", "text/html; charset=" . Kohana::$charset);
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
   * to a Rest_Exception (which, itself, returns an HTTP_Exception) and run RestAPI::init().
   *
   * @see  Controller::execute()
   * @see  Gallery_Controller::execute()
   */
  public function execute() {
    RestAPI::init();

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
    if (Arr::get($this->rest_object->params, "expand_members",
        $this->rest_object->default_params["expand_members"])) {
      $members = method_exists($this->rest_object, "get_members") ?
        $this->rest_object->get_members() : null;

      if (!isset($members)) {
        // A null members array means the resource is not a collection - fire a 400 Bad Request.
        throw Rest_Exception::factory(400, array("expand_members" => "not_a_collection"));
      }

      foreach ($members as $key => $member) {
        $this->rest_response[$key] = $member->get_response();
      }
    } else {
      $this->rest_response = $this->rest_object->get_response();
    }

    if (!isset($this->rest_response)) {
      throw Rest_Exception::factory(400, array("method" => "invalid"));
    }
  }

  /**
   * PUT a typical REST resource.  As needed, this runs put_entity() and put_members() for
   * the resource, as well as put_members() for the resource's relationships.
   */
  public function action_put() {
    $entity        = $this->request->post("entity");
    $members       = $this->request->post("members");
    $relationships = $this->request->post("relationships");

    if (isset($entity)) {
      if (!method_exists($this->rest_object, "put_entity")) {
        throw Rest_Exception::factory(400, array("method" => "invalid"));
      }
      $this->rest_object->put_entity();
    }

    if (isset($members)) {
      if (!method_exists($this->rest_object, "put_members")) {
        throw Rest_Exception::factory(400, array("method" => "invalid"));
      }
      $this->rest_object->put_members();
    }

    if (isset($relationships)) {
      $actual_relationships = $this->rest_object->relationships();
      foreach ($relationships as $key => $params) {
        if (empty($actual_relationships[$key])) {
          // The resource doesn't have the relationship type specified - fire a 400 Bad Request.
          throw Rest_Exception::factory(400, array("relationships" => "invalid"));
        }

        $relationship->params = $params;
        if (!method_exists($relationship, "put_members")) {
          throw Rest_Exception::factory(400, array("method" => "invalid"));
        }
        $relationship->put_members();
      }
    }

    $this->rest_response = $this->rest_object->put_response();
  }

  /**
   * POST a typical REST resource.  As needed, this runs post_entity() and post_members() for
   * the resource, as well as post_members() for the resource's relationships.
   *
   * By default, a successful POST returns a 201 response with a "Location" header.  However,
   * if a post_entity() function decides that the resource already exists, they can override this
   * by changing the resource's "created" property back to false.
   */
  public function action_post() {
    $entity        = $this->request->post("entity");
    $members       = $this->request->post("members");
    $relationships = $this->request->post("relationships");

    try {
      if (isset($entity)) {
        if (!method_exists($this->rest_object, "post_entity")) {
          throw Rest_Exception::factory(400, array("method" => "invalid"));
        }

        $this->rest_object->created = true;
        $this->rest_object->post_entity();
      }

      if (isset($members)) {
        if (!method_exists($this->rest_object, "post_members")) {
          throw Rest_Exception::factory(400, array("method" => "invalid"));
        }
        $this->rest_object->post_members();
      }

      if (isset($relationships)) {
        $actual_relationships = $this->rest_object->relationships();
        foreach ($relationships as $key => $params) {
          if (empty($actual_relationships[$key])) {
            // The resource doesn't have the relationship type specified - fire a 400 Bad Request.
            throw Rest_Exception::factory(400, array("relationships" => "invalid"));
          }

          $relationship->params = $params;
          if (!method_exists($relationship, "post_members")) {
            throw Rest_Exception::factory(400, array("method" => "invalid"));
          }
          $relationship->post_members();
        }
      }
    } catch (Exception $e) {
      if ($this->rest_object->created) {
        // The entity created a new resource, but the members/relationships failed.  This
        // means that the request is bad, so we need to delete the newly-created resource.
        // We temporarily change to an admin to ensure that we have delete access.
        // @todo: find a more elegant way to handle not having delete access.
        $user = Identity::active_user();
        Identity::set_active_user(Identity::admin_user());
        $this->rest_object->delete();
        Identity::set_active_user($user);
      }

      throw $e;
    }

    if ($this->rest_object->created) {
      // New resource - set the status and headers.
      $this->response->status(201);
      $this->response->headers("Location", $this->rest_object->url());
    }

    $this->rest_response = $this->rest_object->post_response();
  }

  /**
   * DELETE a typical REST resource.
   */
  public function action_delete() {
    if (!method_exists($this->rest_object, "delete")) {
      throw Rest_Exception::factory(400, array("method" => "invalid"));
    }

    $this->rest_object->delete();
    $this->rest_response = $this->rest_object->delete_response();
  }

  /**
   * Send an OPTIONS response for a CORS preflight request.  This action should *not*
   * ever be overriden in REST resource classes.
   * @see  http://www.w3.org/TR/cors
   */
  public function action_options() {
    $origin =  $this->request->headers("Origin");                          // required
    $method =  $this->request->headers("Access-Control-Request-Method");   // required
    $headers = $this->request->headers("Access-Control-Request-Headers");  // optional

    $allow_origin = RestAPI::approve_origin($origin);
    $allow_method = ($method && in_array(strtoupper($method), RestAPI::$allowed_methods));
    $allow_headers = true;
    if (!empty($headers)) {
      $allowed_headers = array_map("strtolower", RestAPI::$allowed_headers);
      $headers = explode(",", $headers);
      foreach ($headers as $header) {
        if (!in_array(strtolower(trim($header)), $allowed_headers)) {
          $allow_headers = false;
        }
      }
    }

    if (!$allow_origin || !$allow_method || !$allow_headers) {
      throw Rest_Exception::factory(403);
    }

    // CORS preflight passed - send response (headers only, no body).
    $this->response->headers("Access-Control-Allow-Origin",   $allow_origin);
    $this->response->headers("Access-Control-Allow-Methods",  RestAPI::$allowed_methods);
    $this->response->headers("Access-Control-Allow-Headers",  RestAPI::$allowed_headers);
    $this->response->headers("Access-Control-Expose-Headers", RestAPI::$exposed_headers);
    $this->response->headers("Access-Control-Max-Age",        RestAPI::$preflight_max_age);
  }

  /**
   * Show the access key.  If we get here with GET, then guest access was allowed and
   * this shows an empty key with status 200.  If we get here with another method, then
   * the user successfully logged in and this shows their key.
   */
  public function action_show_access_key() {
    $this->rest_response = (string)RestAPI::access_key();
  }
}
