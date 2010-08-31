<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
class Form_Uploadify_Core extends Form_Input {
  protected $data = array(
    "name" => false,
    "type"  => "UNKNOWN",
    "url" => "",
    "text" => "");

  public function __construct($name) {
    parent::__construct($name);
    $this->data["script_data"] = array(
      "g3sid" => Session::instance()->id(),
      "user_agent" => Input::instance()->server("HTTP_USER_AGENT"),
      "csrf" => access::csrf_token());
  }

  public function album(Item_Model $album) {
    $this->data["album"] = $album;
    return $this;
  }

  public function script_data($key, $value) {
    $this->data["script_data"][$key] = $value;
  }

  public function render() {
    $v = new View("form_uploadify.html");
    $v->album = $this->data["album"];
    $v->script_data = $this->data["script_data"];
    $v->simultaneous_upload_limit = module::get_var("gallery", "simultaneous_upload_limit");
    $v->movies_allowed = (bool) movie::find_ffmpeg();
    $v->suhosin_session_encrypt = (bool) ini_get("suhosin.session.encrypt");
    return $v;
  }

  public function validate() {
    return true;
  }
}