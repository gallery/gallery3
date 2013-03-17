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
class Gallery_Menu_Element {
  public $label;
  public $url;
  public $css_id;
  public $css_class;
  public $id;
  public $type;

  public function __construct($type) {
    $this->type = $type;
  }

  /**
   * Set the id
   * @chainable
   */
  public function id($id) {
    $this->id = $id;
    return $this;
  }

  /**
   * Set the label
   * @chainable
   */
  public function label($label) {
    // Guard against developers who forget to internationalize label strings
    if (!($label instanceof SafeString)) {
      $label = new SafeString($label);
    }

    $this->label = $label;
    return $this;
  }

  /**
   * Set the url
   * @chainable
   */
  public function url($url) {
    $this->url = $url;
    return $this;
  }

  /**
   * Set the css id
   * @chainable
   */
  public function css_id($css_id) {
    $this->css_id = $css_id;
    return $this;
  }

  /**
   * Set the css class
   * @chainable
   */
  public function css_class($css_class) {
    $this->css_class = $css_class;
    return $this;
  }

  /**
   * Specifiy a view for this menu item
   * @chainable
   */
  public function view($view) {
    $this->view = $view;
    return $this;
  }

}
