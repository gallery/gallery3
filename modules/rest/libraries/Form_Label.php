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
class Form_Label_Core extends Form_Input {
  protected $data = array(
    "type"  => "label",
    "text" => "");

  public function __construct($label) {
    $this->data["text"] = $label;
  }

  public function __get($key) {
    return isset($this->data[$key]) ? $this->data[$key] : null;
  }

  // In this element we never want print any html so make sure
  // render and ultimately html_element only return the empty string
  public function render() {
    return $this->html_element();
  }

  public function html_element() {
    $data = $this->data;
    unset($data["text"]);
    return "<p " . html::attributes() . ">{$this->data['text']}</p>";
  }

} // End Form Script