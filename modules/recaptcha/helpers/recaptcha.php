<?php defined("SYSPATH") or die("No direct script access.");
/**
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
class recaptcha_Core {
  static function get_configure_form() {
    $form = new Forge("admin/recaptcha", "", "post", array("id" => "gConfigureRecaptchaForm"));
    $group = $form->group("configure_recaptcha")
      ->label(t("Configure Recaptcha"));
    $group->input("public_key")
      ->label(t("Public Key"))
      ->value(module::get_var("recaptcha", "public_key"));
    $group->public_key->error_messages("invalid", t("The public key you provided is invalid."));
    $group->input("private_key")
      ->label(t("Private Key"))
      ->value(module::get_var("recaptcha", "private_key"));
    $group->private_key->error_messages("invalid", t("The private key you provided is invalid."));

    $group->submit("")->value(t("Save"));
    $site_domain = urlencode(stripslashes($_SERVER["HTTP_HOST"]));
    $form->get_key_url = "http://recaptcha.net/api/getkey?domain=$site_domain&app=Gallery3";
    return $form;
  }

  static function check_config() {
    $public_key = module::get_var("recaptcha", "public_key");
    $private_key = module::get_var("recaptcha", "private_key");
    if (empty($public_key) || empty($private_key)) {
      site_status::warning(
        t("Recaptcha is not quite ready!  Please configure the <a href=\"%url\">Recaptcha Keys</a>",
          array("url" => url::site("admin/recaptcha"))),
        "recaptcha_config");
    } else {
      site_status::clear("recaptcha_config");
    }
  }

  /**
   * Verify that the recaptcha key is valid.
   * @param string $private_key
   * @return boolean
   */
  static function verify_key($private_key) {
    $remote_ip = Input::instance()->server("REMOTE_ADDR");
    $response = self::_http_post("api-verify.recaptcha.net", "/verify",
                                 array("privatekey" => $private_key,
                                       "remoteip" => $remote_ip,
                                       "challenge" => "right",
                                       "response" => "wrong"));

    $answers = explode("\n", $response[1]);
    if (trim($answers[0]) == "true") {
      return null;
    } else {
      return $answers[1];
    }
  }

  /**
   * Form validation call back for captcha validation
   * @param string $form
   * @return string error message or null
   */
  static function is_recaptcha_valid($challenge, $response, $private_key) {
    $input = Input::instance();
    $remote_ip = $input->server("REMOTE_ADDR");

    //discard spam submissions
    if (empty($challenge) || empty($response)) {
      return  "incorrect-captcha-sol";
    }

    $response = self::_http_post("api-verify.recaptcha.net", "/verify",
                              array ("privatekey" => $private_key,
                                        "remoteip" => $remote_ip,
                                     "challenge" => $challenge,
                                     "response" => $response));

    $answers = explode ("\n", $response [1]);
    if (trim ($answers [0]) == "true") {
      return null;
    } else {
      return $answers[1];
    }
  }

  /**
   * Encodes the given data into a query string format
   * @param $data - array of string elements to be encoded
   * @return string - encoded request
   */
  private static function _encode(array $data){
    $req = array();
    foreach ($data as $key => $value){
      $req[] = "$key=" . urlencode(stripslashes($value));
    }
    return implode("&", $req);
  }

  /**
   * Submits an HTTP POST to a reCAPTCHA server
   * @param string $host
   * @param string $path
   * @param array $data
   * @param int port
   * @return array response
   */
  private static function _http_post($host, $path, $data, $port = 80) {
    $req = self::_encode($data);
    $http_request  = "POST $path HTTP/1.0\r\n";
    $http_request .= "Host: $host\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
    $http_request .= "Content-Length: " . strlen($req) . "\r\n";
    $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
    $http_request .= "\r\n";
    $http_request .= $req;
    $response = "";
    if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
      throw new Exception("@todo COULD NOT OPEN SOCKET");
    }
    fwrite($fs, $http_request);
    while (!feof($fs)) {
      $response .= fgets($fs, 1160); // One TCP-IP packet
    }
    fclose($fs);
    $response = explode("\r\n\r\n", $response, 2);
    return $response;
  }
}
