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
abstract class Gallery_Session extends Kohana_Session {
  protected $_abort_save = false;

  /**
   * Do not save this session.  This is a performance feature only.
   * Ref: http://dev.kohanaframework.org/issues/1801
   *
   * @return  void
   */
  public function abort_save() {
    $this->_abort_save = true;
  }

  /**
   * Skip Session::write() and return true if abort_save() has been called.
   */
  public function write() {
    return ($this->_abort_save ? true : parent::write());
  }
}
