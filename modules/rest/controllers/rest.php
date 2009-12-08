<?php defined("SYSPATH") or die("No direct script access.");/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
  public function access_key() {
    $request = json_decode($this->input->post("request"));
    if (empty($request->user) || empty($request->password)) {
      print rest::forbidden("No user or password supplied");
      return;
    }

    $user = identity::lookup_user_by_name($request->user);
    if (empty($user)) {
      print rest::forbidden("User '{$request->user}' not found");
      return;
    }

    if (!identity::is_correct_password($user, $request->password)) {
      print rest::forbidden("Invalid password for '{$request->user}'.");
      return;
    }
    $key = ORM::factory("user_access_token")
      ->where("user_id", $user->id)
      ->find();
    if (!$key->loaded) {
      $key->user_id = $user->id;
      $key->access_key = md5($user->name . rand());
      $key->save();
      Kohana::log("alert",  Kohana::debug($key->as_array()));
    }
    print rest::success(array("token" => $key->access_key));
  }

  public function __call($function, $args) {
    $access_token = $this->input->get("request_key");
    $request = $this->input->post("request", null);

    if (empty($access_token)) {
      print rest::forbidden("No access token supplied.");
      return;
    }

    try {
      $key = ORM::factory("user_access_token")
        ->where("access_key", $access_token)
        ->find();

      if (!$key->loaded) {
        print rest::forbidden("Invalid key: $access_token");
        return;
      }

      $user = identity::lookup_user($key->user_id);
      if (empty($user)) {
        print rest::forbidden("User not found: {$key->user_id}");
        return;
      }

      if (!empty($request)) {
        $method = strtolower($this->input->server("HTTP_X_HTTP_METHOD_OVERRIDE", "POST"));
        $request = json_decode($request);
      } else {
        print rest::invalid_request("Empty Request");
        return;
      }


      if (empty($args[0])) {
        print rest::invalid_request("Resource not supplied");
        return;
      }

      $handler_class = "{$function}_rest";
      $handler_method = "{$method}_{$args[0]}";

      if (!method_exists($handler_class, $handler_method)) {
        print rest::not_implemented("$handler_class::$handler_method is not implemented");
        return;
      }

      identity::set_active_user($user);

      print call_user_func(array($handler_class, $handler_method), $request);
    } catch (Exception $e) {
      print rest::internal_error($e);
    }
  }

}