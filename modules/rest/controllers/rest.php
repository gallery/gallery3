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
    $request = (object)Input::instance()->get();
    if (empty($request->user) || empty($request->password)) {
      Rest_Exception::trigger(403, "Forbidden", "No user or password supplied");
    }

    $user = identity::lookup_user_by_name($request->user);
    if (empty($user)) {
      Rest_Exception::trigger(403, "Forbidden", "User '{$request->user}' not found");
      return;
    }

    if (!identity::is_correct_password($user, $request->password)) {
      Rest_Exception::trigger(403, "Forbidden", "Invalid password for '{$request->user}'.");
      return;
    }

    $key = ORM::factory("user_access_token")
      ->where("user_id", "=", $user->id)
      ->find();
    if (!$key->loaded()) {
      $key->user_id = $user->id;
      $key->access_key = md5($user->name . rand());
      $key->save();
      Kohana_Log::add("alert",  Kohana::debug($key->as_array()));
    }
    print rest::success(array("token" => $key->access_key));
  }

  public function __call($function, $args) {
    $request = rest::normalize_request($args);
    try {
      if (rest::set_active_user($request->access_token)) {
        $handler_class = "{$function}_rest";
        $handler_method = $request->method;

        if (!method_exists($handler_class, $handler_method)) {
          Rest_Exception::trigger(501, "Not implemented", "$handler_class::$handler_method");
        }

        print call_user_func(array($handler_class, $handler_method), $request);
      }
    } catch (Rest_Exception $e) {
      $e->sendHeaders();
    } catch (Exception $e) {
      Kohana_Log::add("error", $e->__toString());
      header("HTTP/1.1 500 Internal Error");
    }
  }
}