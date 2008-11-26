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
class Tags_Controller extends REST_Controller {
  protected $resource_type = "tag";

  /**
   *  @see Rest_Controller::_index()
   */
  public function _index() {
    throw new Exception("@todo Tag_Controller::_index NOT IMPLEMENTED");
  }

  /**
   *  @see Rest_Controller::_form_add($parameters)
   */
  public function _form_add($parameters) {
    throw new Exception("@todo Tag_Controller::_form NOT IMPLEMENTED");
  }

  /**
   *  @see Rest_Controller::_form_edit($resource)
   */
  public function _form_edit($tag) {
    throw new Exception("@todo Tag_Controller::_form NOT IMPLEMENTED");
  }

  public function _show($tag) {
    Albums_Controller::_show($tag);
  }

  public function _create($tag) {
    throw new Exception("@todo Tag_Controller::_create NOT IMPLEMENTED");
  }

  public function _delete($tag) {
    throw new Exception("@todo Tag_Controller::_delete NOT IMPLEMENTED");
  }

  public function _update($tag) {
    throw new Exception("@todo Tag_Controller::_update NOT IMPLEMENTED");
  }
}
