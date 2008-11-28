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
class Rearrange_Controller extends REST_Controller {
  protected $resource_type = "item";

  public function _show($item) {
    throw new Exception("@todo Rearrange_Controller::_show NOT IMPLEMENTED");
  }

  public function _index() {
    // @todo: represent this in different formats
    $root = ORM::factory("item", 1);
    $this->_show($root);
  }

  public function _form_add($item_id) {
    throw new Exception("@todo Rearrange_Controller::_form_add NOT IMPLEMENTED");
  }

  public function _form_edit($tag) {
    throw new Exception("@todo Rearrange_Controller::_form_edit NOT IMPLEMENTED");
  }

  public function _create($tag) {
    throw new Exception("@todo Rearrange_Controller::_create NOT IMPLEMENTED");
  }

  public function _delete($tag) {
    throw new Exception("@todo Rearrange_Controller::_delete NOT IMPLEMENTED");
  }

  public function _update($tag) {
    throw new Exception("@todo Rearrange_Controller::_update NOT IMPLEMENTED");
  }
}
