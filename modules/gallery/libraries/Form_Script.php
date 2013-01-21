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
class Form_Script_Core extends Forge {
  protected $data = array(
    "name" => false,
    "type"  => "script",
    "url" => "",
    "text" => "");

  public function __construct($name) {
    // Set dummy data so we don"t get errors
    $this->attr["action"] = "";
    $this->attr["method"] = "post";
    $this->data["name"] = $name;
  }

  public function __get($key) {
    return isset($this->data[$key]) ? $this->data[$key] : null;
  }

  /**
   * Sets url attribute
   */
  public function url($url) {
    $this->data["url"] = $url;

    return $this;
  }

  public function text($script_text) {
    $this->data["text"] = $script_text;

    return $this;
  }

  public function render($template="forge_template", $custom=false) {
    $script = array();
    if (!empty($this->data["url"])) {
      $script[] = html::script($this->data["url"]);
    }

    if (!empty($this->data["text"])) {
      $script[] = "<script type=\"text/javascript\">\n{$this->data['text']}\n</script>\n";
    }

    return implode("\n", $script);
  }

}