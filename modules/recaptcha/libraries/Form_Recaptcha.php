<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
class Form_Recaptcha_Core extends Form_Input {
  private $_error = null;

  protected $data = array(
    'name'  => '',
    'value' => '',
  );

  public function __construct($name) {
    parent::__construct($name);
    $this->error_messages("incorrect-captcha-sol",
                          t("The values supplied to reCAPTCHA are incorrect."));
    $this->error_messages("invalid-site-private-key", t("The site private key is incorrect."));
  }

  public function render() {
    $public_key = module::get_var("recaptcha", "public_key");
    if (empty($public_key)) {
      throw new Exception("@todo NEED KEY <a href=\"http://recaptcha.net/api/getkey\">" .
                          "http://recaptcha.net/api/getkey</a>");
    }

    $view = new View("form_recaptcha.html");
    $view->public_key = $public_key;
    return $view;
  }

  /**
   * Validate this input based on the set rules.
   *
   * @return  bool
   */
  public function validate() {
    $input = Input::instance();
    $challenge = $input->post("recaptcha_challenge_field", "", true);
    $response = $input->post("recaptcha_response_field", "", true);
    if (!empty($challenge)) {
      $this->_error = recaptcha::is_recaptcha_valid(
        $challenge, $response, module::get_var("recaptcha", "private_key"));
      if (!empty($this->_error)) {
        $this->add_error($this->_error, 1);
      }
    }
    $this->is_valid = empty($this->_error);
    return empty($this->_error);
  }

}