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
  const ALLOW_PRIVATE_GALLERY = true;

  public function index() {
    $username = Input::instance()->post("user");
    $password = Input::instance()->post("password");

    if (empty($username) || Auth::too_many_failures($username)) {
      throw new Rest_Exception("Forbidden", 403);
    }

    $user = Identity::lookup_user_by_name($username);
    if (empty($user) || !Identity::is_correct_password($user, $password)) {
      Module::event("user_login_failed", $username);
      throw new Rest_Exception("Forbidden", 403);
    }

    Auth::login($user);

    Rest::reply(Rest::access_key());
  }

  public function reset_api_key_confirm() {
    $form = new Forge("rest/reset_api_key", "", "post", array("id" => "g-reset-api-key"));
    $group = $form->group("confirm_reset")->label(t("Confirm resetting your REST API key"));
    $group->submit("")->value(t("Reset"));
    $v = new View("rest/reset_api_key_confirm.html");
    $v->form = $form;
    print $v;
  }

  public function reset_api_key() {
    Access::verify_csrf();
    Rest::reset_access_key();
    Message::success(t("Your REST API key has been reset."));
    JSON::reply(array("result" => "success"));
  }

  public function __call($function, $args) {
    try {
      $input = Input::instance();
      $request = new stdClass();

      switch ($method = strtolower($input->server("REQUEST_METHOD"))) {
      case "get":
        $request->params = (object) $input->get();
        break;

      default:
        $request->params = (object) $input->post();
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

      $request->method = strtolower($input->server("HTTP_X_GALLERY_REQUEST_METHOD", $method));
      $request->access_key = $input->server("HTTP_X_GALLERY_REQUEST_KEY");

      if (empty($request->access_key) && !empty($request->params->access_key)) {
        $request->access_key = $request->params->access_key;
      }

      $request->url = URL::abs_current(true);
      if ($suffix = Kohana::config('core.url_suffix')) {
        $request->url = substr($request->url, 0, strlen($request->url) - strlen($suffix));
      }

      Rest::set_active_user($request->access_key);

      $handler_class = "Hook_Rest_" . Inflector::camelize($function, true);
      $handler_method = $request->method;

      if (!class_exists($handler_class) || !method_exists($handler_class, $handler_method)) {
        throw new Rest_Exception("Bad Request", 400);
      }

      $response = call_user_func(array($handler_class, $handler_method), $request);
      if ($handler_method == "post") {
        // post methods must return a response containing a URI.
        header("HTTP/1.1 201 Created");
        header("Location: {$response['url']}");
      }
      Rest::reply($response);
    } catch (ORM_Validation_Exception $e) {
      // Note: this is totally insufficient because it doesn't take into account localization.  We
      // either need to map the result values to localized strings in the application code, or every
      // client needs its own l10n string set.
      throw new Rest_Exception("Bad Request", 400, $e->validation->errors());
    } catch (Kohana_404_Exception $e) {
      throw new Rest_Exception("Not Found", 404);
    }
  }
}