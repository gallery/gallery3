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
class rest_Core {
  /**
   * Request failed
   */
  static function fail($log_message=null) {
    if (!empty($log_message)) {
      Kohana_Log::add("info", $log_message);
    }
    // We don't need to save the session for this request
    Session::abort_save();
    return json_encode(array("status" => "ERROR", "message" => (string)$message));
  }

  /**
   * Success
   */
  static function success($response_data=array(), $message=null) {
    $response = array("status" => "OK");
    if (!empty($message)) {
      $response["message"] = (string)$message;
    }
    $response = array_merge($response, $response_data);

    // We don't need to save the session for this request
    Session::abort_save();
    return json_encode($response);
  }

  /**
   * Validation Error
   */
  static function validation_error($error_data) {
    $response = array("status" => "VALIDATE_ERROR");
    $response = array_merge($response, array("fields" => $error_data));

    // We don't need to save the session for this request
    Session::abort_save();
    return json_encode($response);
  }


  static function normalize_request($args=array()) {
    $input = Input::instance();
    $method = strtolower($input->server("REQUEST_METHOD"));
    $request = new stdClass();
    foreach (array_keys($input->get()) as $key) {
      $request->$key = $input->get($key);
    }
    if ($method != "get") {
      foreach (array_keys($input->post()) as $key) {
        $request->$key = $input->post($key);
      }
      foreach (array_keys($_FILES) as $key) {
        $request->$key = $_FILES[$key];
      }
    }

    $request->method = strtolower($input->server("HTTP_X_GALLERY_REQUEST_METHOD", $method));
    $request->access_token = $input->server("HTTP_X_GALLERY_REQUEST_KEY");
    $request->arguments = $args;  // Let the rest handler figure out what the arguments mean

    return $request;
  }

  static function set_active_user($access_token) {
    if (empty($access_token)) {
      $user = identity::guest();
    } else {
      $key = ORM::factory("user_access_token")
        ->where("access_key", "=", $access_token)
        ->find();

      if ($key->loaded()) {
        $user = identity::lookup_user($key->user_id);
        if (empty($user)) {
          Rest_Exception::trigger(403, "Forbidden", $log_message,
                                  "User not found: {$key->user_id}");
        }
      } else {
        Rest_Exception::trigger(403, "Forbidden", $log_message,
                                "Invalid user access token supplied: {$key->user_id}");
      }
    }
    identity::set_active_user($user);
    return true;
  }
}
