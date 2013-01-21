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
class InPlaceEdit_Core {
  private $rules = array();
  private $messages = array();
  private $callback = array();
  private $initial_value;
  private $action = "";
  private $errors;
  private $form;

  static function factory($initial_value) {
    $instance = new InPlaceEdit();
    $instance->initial_value = $initial_value;
    $instance->form = array("input" => $initial_value);
    $instance->errors = array("input" => "");

    return $instance;
  }

  public function action($action) {
    $this->action = $action;
    return $this;
  }

  public function rules($rules) {
    $this->rules += $rules;
    return $this;
  }

  public function messages($messages) {
    $this->messages += $messages;
    return $this;
  }

  public function callback($callback) {
    $this->callback = $callback;
    return $this;
  }

  public function validate() {
    $post = Validation::factory($_POST);

    if (!empty($this->callback)) {
      $post->add_callbacks("input", $this->callback);
    }

    foreach ($this->rules as $rule) {
      $post->add_rules("input", $rule);
    }

    $valid = $post->validate();
    $this->form = array_merge($this->form, $post->as_array());
    $this->errors = array_merge($this->errors, $post->errors());
    return $valid;
  }

  public function render() {
    $v = new View("in_place_edit.html");
    $v->action = $this->action;
    $v->form = $this->form;
    $v->errors = $this->errors;
    foreach ($v->errors as $key => $error) {
      if (!empty($error)) {
        $v->errors[$key] = $this->messages[$error];
      }
    }
    return $v->render();
  }

  public function value() {
    return $this->form["input"];
  }
}