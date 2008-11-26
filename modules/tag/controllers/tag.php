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
class Tag_Controller extends REST_Controller {
  protected $resource_type = "tag";

  /**
   *  @see Rest_Controller::_index()
   */
  public function _index() {
    throw new Exception("@todo Comment_Controller::_form NOT IMPLEMENTED");
  }

  /**
   *  @see Rest_Controller::_form_add($parameters)
   */
  public function _form_add($parameters) {
    throw new Exception("@todo Comment_Controller::_form NOT IMPLEMENTED");
  }

  /**
   *  @see Rest_Controller::_form_edit($resource)
   */
  public function _form_edit($tag) {
    throw new Exception("@todo Comment_Controller::_form NOT IMPLEMENTED");
  }

  public function _show($tag) {
    throw new Exception("@todo Tag_Controller::_show NOT IMPLEMENTED");
  }

  public function _create($tag) {
    // @todo Productionize this code
    // 1) Add security checks
    throw new Exception("@todo Tag_Controller::_create NOT IMPLEMENTED");
      }

  public function _delete($tag) {
    // @todo Production this code
    // 1) Add security checks
    throw new Exception("@todo Tag_Controller::_delete NOT IMPLEMENTED");
  }

  public function _update($tag) {
    // @todo Productionize this
    // 1) Figure out how to do the right validation here.  Validate the form input and apply it to
    //    the model as appropriate.
    // 2) Figure out how to dispatch according to the needs of the client.  Ajax requests from
    //    jeditable will want the changed field back, and possibly the whole item in json.
    //
    // For now let's establish a simple protocol where the client passes in a __return parameter
    // that specifies which field it wants back from the item.  Later on we can expand that to
    // include a data format, etc.

    throw new Exception("@todo Tag_Controller::_update NOT IMPLEMENTED");
  }
}
