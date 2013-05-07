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
class Recaptcha_Recaptcha {
  static function check_config() {
    $public_key = Module::get_var("recaptcha", "public_key");
    $private_key = Module::get_var("recaptcha", "private_key");
    if (empty($public_key) || empty($private_key)) {
      SiteStatus::warning(
        t("reCAPTCHA is not quite ready!  Please configure the <a href=\"%url\">reCAPTCHA Keys</a>",
          array("url" => HTML::mark_clean(URL::site("admin/recaptcha")))),
        "recaptcha_config");
    } else {
      SiteStatus::clear("recaptcha_config");
    }
  }

  /**
   * Verify that the recaptcha key is valid.
   * @param string $private_key
   * @return boolean
   */
  static function verify_key($private_key) {
    $remote_ip = $_SERVER["REMOTE_ADDR"];
    $response = self::_http_post("api-verify.recaptcha.net", "/verify",
                                 array("privatekey" => $private_key,
                                       "remoteip" => $remote_ip,
                                       "challenge" => "right",
                                       "response" => "wrong"));

    if ($response[1] == "false\ninvalid-site-private-key") {
      // This is the only thing I can figure out how to verify.
      // See http://recaptcha.net/apidocs/captcha for possible return values
      return false;
    }
    return true;
  }

  /**
   * Form validation call back for captcha validation
   * @param string $form
   * @return string error message or null
   */
  static function is_recaptcha_valid($challenge, $response, $private_key) {
    $remote_ip = $_SERVER["REMOTE_ADDR"];

    // discard spam submissions
    if (empty($challenge) || empty($response)) {
      return  "incorrect-captcha-sol";
    }

    $response = self::_http_post("api-verify.recaptcha.net", "/verify",
                              array("privatekey" => $private_key,
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
  protected static function _encode(array $data){
    $req = array();
    foreach ($data as $key => $value){
      $req[] = "$key=" . urlencode(stripslashes($value));
    }
    return implode("&", $req);
  }

  /**
   * Submits an HTTP POST to a reCAPTCHA server
   * @todo: redo/simplify this with a sub-request.
   *
   * @param string $host
   * @param string $path
   * @param array $data
   * @param int port
   * @return array response
   */
  protected static function _http_post($host, $path, $data, $port = 80) {
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
      throw new Gallery_Exception("Could not open socket");
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
