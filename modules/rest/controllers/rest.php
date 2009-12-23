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
    $request = (object)$this->input->get();
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
    $request = $this->_normalize_request($args);
    try {
      if ($this->_set_active_user($request->access_token)) {
        $handler_class = "{$function}_rest";
        $handler_method = $request->method;

        if (!method_exists($handler_class, $handler_method)) {
          print rest::not_implemented("$handler_class::$handler_method is not implemented");
          return;
        }

        print call_user_func(array($handler_class, $handler_method), $request);
      }
    } catch (Exception $e) {
      print rest::internal_error($e->__toString());
    }
  }

  private function _normalize_request($args=array()) {
    $method = strtolower($this->input->server("REQUEST_METHOD"));
    $request = new stdClass();
    foreach (array_keys($this->input->get()) as $key) {
      $request->$key = $this->input->get($key);
    }
    if ($method != "get") {
      foreach (array_keys($this->input->post()) as $key) {
        $request->$key = $this->input->post($key);
      }
      foreach (array_keys($_FILES) as $key) {
        $request->$key = $_FILES[$key];
      }
    }

    $request->method = strtolower($this->input->server("HTTP_X_GALLERY_REQUEST_METHOD", $method));
    $request->access_token = $this->input->server("HTTP_X_GALLERY_REQUEST_KEY");
    $request->arguments = $args;  // Let the rest handler figure out what the arguments mean

    return $request;
  }

  private function _set_active_user($access_token) {
    if (empty($access_token)) {
      $user = identity::guest();
    } else {
      $key = ORM::factory("user_access_token")
        ->where("access_key", $access_token)
        ->find();

      if ($key->loaded) {
        $user = identity::lookup_user($key->user_id);
        if (empty($user)) {
          print rest::forbidden("User not found: {$key->user_id}");
          return false;;
        }
      } else {
        print rest::forbidden("Invalid user access token supplied: {$key->user_id}");
        return false;
      }
    }
    identity::set_active_user($user);
    return true;
  }
}