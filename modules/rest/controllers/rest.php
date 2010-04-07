<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
class Rest_Controller extends Controller {
  public function index() {
    $username = Input::instance()->post("user");
    $password = Input::instance()->post("password");

    if (empty($username) || auth::too_many_failures($username)) {
      throw new Rest_Exception("Forbidden", 403);
    }

    $user = identity::lookup_user_by_name($username);
    if (empty($user) || !identity::is_correct_password($user, $password)) {
      module::event("user_login_failed", $username);
      throw new Rest_Exception("Forbidden", 403);
    }

    auth::login($user);

    $key = rest::get_access_key($user->id);
    rest::reply($key->access_key);
  }

  public function __call($function, $args) {
    $input = Input::instance();
    $request = new stdClass();

    switch ($method = strtolower($input->server("REQUEST_METHOD"))) {
    case "get":
      $request->params = (object) $input->get();
      break;

    default:
      $request->params = (object) $input->post();
      if (isset($_FILES["file"])) {
        $request->file = upload::save("file");
      }
      break;
    }

    if (isset($request->params->entity)) {
      $request->params->entity = json_decode($request->params->entity);
    }
    if (isset($request->params->members)) {
      $request->params->members = json_decode($request->params->members);
    }

    $request->method = strtolower($input->server("HTTP_X_GALLERY_REQUEST_METHOD", $method));
    $request->access_key = $input->server("HTTP_X_GALLERY_REQUEST_KEY");

    if (empty($request->access_key) && !empty($request->params->access_key)) {
      $request->access_key = $request->params->access_key;
    }

    $request->url = url::abs_current(true);

    rest::set_active_user($request->access_key);

    $handler_class = "{$function}_rest";
    $handler_method = $request->method;

    if (!method_exists($handler_class, $handler_method)) {
      throw new Rest_Exception("Bad Request", 400);
    }

    try {
      rest::reply(call_user_func(array($handler_class, $handler_method), $request));
    } catch (ORM_Validation_Exception $e) {
      foreach ($e->validation->errors() as $key => $value) {
        $msgs[] = "$key: $value";
      }
      throw new Rest_Exception("Bad Request: " . join(", ", $msgs), 400);
    }
  }
}