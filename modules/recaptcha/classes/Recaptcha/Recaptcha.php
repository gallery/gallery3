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
  const HOME_URL    = "http://www.google.com/recaptcha";
  const VERIFY_URL  = "http://www.google.com/recaptcha/api/verify";
  const GET_KEY_URL = "http://www.google.com/recaptcha/admin/create";
  const INVALID_KEY = "invalid-site-private-key";
  const INVALID_SOL = "incorrect-captcha-sol";

  /**
   * Check to see if we have the reCAPTCHA keys set, then set/clear the site message as needed.
   */
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
   * Validate that the reCAPTCHA private key is valid.
   *
   * @see http://developers.google.com/recaptcha/docs/verify
   * @param string $private_key
   * @return boolean
   */
  static function validate_key($private_key) {
    // The string "gallery_test" has no special meaning to reCAPTCHA, so this validation
    // attempt will certainly fail.  We're just checking to see *why* it fails.
    $code = static::get_recaptcha_response("gallery_test", "gallery_test", $private_key);
    return ($code != static::INVALID_KEY);
  }

  /**
   * Get a response from reCAPTCHA, and return null if valid or the error code if not.
   *
   * @see http://developers.google.com/recaptcha/docs/verify
   * @param string $form
   * @return string error message or null
   */
  static function get_recaptcha_response($challenge, $response, $private_key) {
    // discard spam submissions
    if (empty($challenge) || empty($response)) {
      return static::INVALID_SOL;
    }

    $response = Request::factory(static::VERIFY_URL)
                  ->method(Request::POST)
                  ->post(array(
                      "challenge" => $challenge,
                      "response" => $response,
                      "privatekey" => $private_key,
                      "remoteip" => $_SERVER["REMOTE_ADDR"]
                    ))
                  ->execute()->body();

    $response = explode("\n", $response);
    if (trim($response[0]) == "true") {
      return null;
    } else {
      return trim($response[1]);
    }
  }
}
