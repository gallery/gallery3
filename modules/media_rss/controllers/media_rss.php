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
class Media_rss_Controller extends REST_Controller {
  // @todo this should be retrieved from the slideshow configuration
  public static $LIMIT = 10;

  protected $resource_type = "item";

  /**
   *  @see Rest_Controller::_index()
   */
  public function _index() {
    throw new Exception("@todo Item_Controller::_index NOT IMPLEMENTED");
  }

  /**
   *  @see Rest_Controller::_create()
   */
  public function _create($resource) {
    throw new Exception("@todo Item_Controller::_create NOT IMPLEMENTED");
  }
  
  public function _show($parent, $output_format) {
    if ($output_format != "mediarss") {
      throw new Exception("@todo Unsupported output format: $output_format");
    }

    $offset = $this->input->get("offset", 0);

    $view = new View("media.rss");
    $view->item = $parent;

    // @todo create a descendent child method on ORM_MTPP to get all of the children
//    $view->children = $parent->descendents();
  }

  /**
   *  @see Rest_Controller::_update()
   */
  public function _update($resource) {
    throw new Exception("@todo Item_Controller::_update NOT IMPLEMENTED");
  }

  /**
   *  @see Rest_Controller::_delete()
   */
  public function _delete($resource) {
    throw new Exception("@todo Item_Controller::_delete NOT IMPLEMENTED");
  }

  /**
   *  @see Rest_Controller::_form_add()
   */
  public function _form_add($parameter) {
    throw new Exception("@todo Item_Controller::_form_add NOT IMPLEMENTED");
  }
    /**
   *  @see Rest_Controller::_form_edit()
   */
  public function _form_edit($resource) {
    throw new Exception("@todo Item_Controller::_form_edit NOT IMPLEMENTED");
  }
  
  protected function get_output_format() {
    return "mediarss";
  }
}