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
class Rest_Controller_Rest extends Controller {
  public $allow_private_gallery = true;

  public function action_index() {
    // Check login using "user" and "password" fields in POST.  Fire a 403 Forbidden if it fails.
    if (!Validation::factory($this->request->post())
      ->rule("user", "Auth::validate_login", array(":validation", ":data", "user", "password"))
      ->check()) {
      throw new Rest_Exception("Forbidden", 403);
    }

    Rest::reply(Rest::access_key(), $this->response);
  }

  public function __call($function, $args) {
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
        throw new Rest_Exception("Bad Request", 400);
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
      throw new Rest_Exception("Bad Request", 400, null, $e->errors());
    } catch (HTTP_Exception_404 $e) {
      throw new Rest_Exception("Not Found", 404);
    }
  }
}