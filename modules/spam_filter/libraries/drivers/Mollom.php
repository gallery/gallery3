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
class Mollom_Driver extends SpamFilter_Driver {
  public function check_comment($comment_data) {
    return true;
  }

  public function submit_spam($comment_data) {
    return $response[1] == "true";
  }

  public function submit_ham($comment_data) {
  }

  public function get_statistics() {
    throw new Exception("@todo GET_STATISTICS NOT IMPLEMENTED");
  }

  public function get_admin_fields($post) {
    $view = new View("spam_filter_admin_mollom.html");
    $view->private_key = empty($post) ? module::get_var("spam_filter", "private_key") :
      $post->private_key;
    $view->public_key = empty($post) ? module::get_var("spam_filter", "public_key") :
      $post->private_key;

    $view->errors = $post ? $post->errors() : null;
    return $view;
  }

  public function get_validation_rules($post) {
    $post->add_rules("private_key", "required");
    $post->add_rules("public_key", "required");
    $post->add_callbacks("private_key", array($this, "validate_key"));
  }

  public function validate_key(Validation $array, $field) {
    // @todo verify key values
    Kohana::log("debug", "Mollom::validate_key");
    Kohana::log("debug", print_r($array, 1));
    Kohana::log("debug", "field: $field");
  }

  public function set_api_data($post) {
    module::set_var("spam_filter", "private_key", $post->private_key);
    module::set_var("spam_filter", "public_key", $post->public_key);
  }

  private function _build_request($function, $host,$comment_data) {
    return "";
  }
}