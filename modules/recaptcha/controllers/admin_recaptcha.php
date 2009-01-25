<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class Admin_Recaptcha_Controller extends Admin_Controller {
  public function index() {
    $form = recaptcha::get_configure_form();
    $old_public_key = module::get_var("recaptcha", "public_key");
    $old_private_key = module::get_var("recaptcha", "private_key");
    if (request::method() == "post") {

      $valid_key = $form->validate();
      if ($valid_key) {
        $valid_key = recaptcha::is_recaptcha_valid($form,
                                                   $form->configure_recaptcha->private_key->value);
        if (empty($valid_key) && $form->captcha_error == "invalid-site-private-key") {
          $form->configure_recaptcha->private_key->add_error("invalid", 1);
          unset($form->captcha_error);
        }
      }
      if ($valid_key) {
        $new_public_key = $form->configure_recaptcha->public_key->value;
        $new_private_key = $form->configure_recaptcha->private_key->value;

        $this->_update_key("public_key", $old_public_key, $new_public_key);
        $this->_update_key("private_key", $old_private_key, $new_private_key);

        $add_recaptcha_to = array();
        foreach ($form->configure_recaptcha->activated_forms->value as $name) {
          $add_recaptcha_to[$name] = 1;
        }
        module::set_var("recaptcha", "form_list", serialize($add_recaptcha_to));
        log::success(t("Recaptcha active forms have changed."));

        message::success(t("Recaptcha Configured"));
        recaptcha::check_config();
      }
    } else {
      $valid_key = !empty($old_public_key) && !empty($old_private_key);
    }

    recaptcha::check_config();
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_recaptcha.html");
    $view->content->valid_key = $valid_key;
    $view->content->form = $form;
    print $view;
  }

  private function _update_key($type, $old_key, $new_key) {
    $changed = true;
    if ($old_key && !$new_key) {
      log::success(sprintf(t("Your Recaptcha %s has been cleared."), strtr($type, "_", " ")));
    } else if ($old_key && $new_key && $old_key != $new_key) {
      log::success(sprintf(t("Your Recaptcha %s has been changed."), strtr($type, "_", " ")));
    } else if (!$old_key && $new_key) {
      log::success(sprintf(t("Your Recaptcha %s has been saved."), strtr($type, "_", " ")));
    } else {
      $changed = false;
    }
    if ($changed) {
      module::set_var("recaptcha", $type, $new_key);
    }
  }

  public function gethtml($public_key, $error=null) {
    $http_request  = "GET /challenge?k=$public_key HTTP/1.0\r\n"; 
    $response = ""; 
    if( false == ( $fs = @fsockopen("api.recaptcha.net", 80, $errno, $errstr, 10) ) ) { 
      throw new Exception("@todo COULD NOT OPEN SOCKET"); 
    }
    $errorpart = empty($error) ? "" : "&error=$error";
    fputs($fs, "GET /challenge?k=$public_key&ajax=1$errorpart HTTP/1.0\r\n");
    fputs($fs, "Host: api.recaptcha.net\r\n");
    fputs($fs, "Connection: Close\r\n\r\n");
    while (!feof($fs)) { 
      $response .= fgets($fs, 1160); // One TCP-IP packet
    }
    fclose($fs);
    $response = explode("\r\n\r\n", $response, 2); 

    if (strpos($response[1], "document.write") === 0) {
      header("HTTP/1.1 400 BAD REQUEST");
      if (preg_match("#.*\'(.*)\'#", $response[1], $matches)) {
        $msg = $matches[1];
      } else {
        $msg = _t("Unable to determine error message");
      }
      print $msg;
    } else {
      header("HTTP/1.1 200 OK");
      print json_encode(array("result" => "success", "script" => $response[1]));
    }
  }
}
