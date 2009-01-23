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
class recaptcha_Core {
  /** 
   * The reCAPTCHA server URL"s 
   */ 
  const API_SERVER = "http://api.recaptcha.net"; 
  const API_SECURE_SERVER = "https://api-secure.recaptcha.net"; 
  const VERIFY_SERVER = "api-verify.recaptcha.net"; 

  /** 
   * RecaptchaOptions 
   */ 
  private $options = array(); 

  static function get_configure_form() {
    $form = new Forge("admin/recaptcha", "", "post");
    $group = $form->group("configure_recaptcha")
      ->label(t("Configure Recaptcha"));
    $group->hidden("orig_public_key")
      ->value(module::get_var());
    $group->input("public_key")
      ->label(t("Public Key"))
      ->value(module::get_var("recaptcha", "public_key"))
      ->rules("required|length[40]");
    $group->public_key->error_messages("invalid", t("The public key you provided is invalid."));
    $group->input("private_key")
      ->label(t("Private Key"))
      ->value(module::get_var("recaptcha", "private_key"))
      ->rules("required|length[40]");
    $group->private_key->error_messages("invalid", t("The private key you provided is invalid."));
    $group->submit("")->value(t("Save"));
    $site_domain = urlencode(stripslashes($_SERVER["HTTP_HOST"]));
    $form->recaptcha_site = self::API_SERVER;
    $form->recaptcha_ssl_site = self::API_SECURE_SERVER;
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
   * Gets the challenge HTML (javascript and non-javascript version). 
   * This is called from the browser, and the resulting reCAPTCHA HTML widget 
   * is embedded within the HTML form it was called from. 
   * @param string $pubkey The public key to use in the challenge  
   * @param string $error The error given by reCAPTCHA (optional, default is null) 
   * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)
   * @param string $lang Any supported language code 
   * @return string - The HTML to be embedded in the user"s form. 
   */ 
  static function get_challenge_html($pubkey, $error = NULL, $use_ssl = false) { 
    if (empty($pubkey)) { 
      throw new Exception("@todo NEED KEY <a href=\"http://recaptcha.net/api/getkey\">" .
                          "http://recaptcha.net/api/getkey</a>"); 
    } 

    $lang = Kohana::config("locale.root_locale");
    $server = $use_ssl ? self::API_SECURE_SERVER : self::API_SERVER; 
    $errorpart = ""; 
    if ($error) { 
      $errorpart = "&amp;error=". $error; 
    }
    return (count(self::$options) > 0 ? "<script type=\"text/javascript\">" . 
            "var RecaptchaOptions = {lang:'$lang'};</script>" :  "") .
      "<script type=\"text/javascript\" src=\"$server/challenge?k=" . 
      "{$pubkey}$errorpart \"></script>" . $noscript; 
  }
  
  /** 
   * Form validation call back for captcha validation
   * @param string $form
   * @return true if valid, false if not
   */ 
  static function is_recaptcha_valid($form, $private_key=null) { 
    $input = Input::instance();

    if (empty($private_key)) {
      $private_key = module::get_var("recaptcha", "private_key");
    }
    Kohana::log("debug", $private_key);
    $remoteip = $_SERVER["REMOTE_ADDR"] ;
    $challenge = $input->post("recaptcha_challenge_field", "", true);
    $response = $input->post("recaptcha_response_field", "", true);

    //discard spam submissions 
    if (empty($challenge) || empty($response)) { 
      $form->captcha_error = "incorrect-captcha-sol";
      return false;
    } 
    $response = self::_http_post(self::VERIFY_SERVER, "/verify", 
                              array ("privatekey" => $private_key, 
                                     "remoteip" => $remoteip, 
                                     "challenge" => $challenge, 
                                     "response" => $response)); 

    $answers = explode ("\n", $response [1]); 
    if (trim ($answers [0]) == "true") { 
      return true; 
    } else {
      $form->captcha_error = $answers[1];
      Kohana::log("debug", print_r($answers, 1));
      return false; 
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
