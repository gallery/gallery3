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
class Login_Form_Core extends Forge {
  protected $error_message;

  public static function factory() {
    return new Login_Form();
  }

  public function __construct() {
    parent::__construct("login.html");
    $this->legend="Login";
    $this->input("username")->rules("required|length[4,32]");
    $this->password("password")->rules("required|length[5,40]");
    $this->submit("Login");
  }
  
  public function render($template = 'login.html', $custom = true) {
    $form = parent::render($template, $custom);
    $form->error_message = $this->error_message;
    return $form;
  }

  public function __set($key, $value) {
    $this->$key = $value;
  }
}