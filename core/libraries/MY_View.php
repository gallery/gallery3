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
class View extends View_Core {
  /**
   * Override View_Core::__construct so that we can set the csrf value into all views.
   *
   * @see View_Core::__construct
   */
  public function __construct($name = NULL, $data = NULL, $type = NULL) {
    parent::__construct($name, $data, $type);
    $this->set_global("csrf", access::csrf_token());
  }
  
  /**
   * Override View_Core::render so that we trap errors stemming from bad PHP includes and show a
   * visible stack trace to help developers.
   *
   * @see View_Core::render
   */
  public function render($print=false, $renderer=false) {
    try {
      return parent::render($print, $renderer);
    } catch (Exception $e) {
      if (!IN_PRODUCTION) {
        print $e->getTraceAsString();
        return $e->getMessage();
      }

      Kohana::Log('error', $e->getTraceAsString());
      Kohana::Log('debug', $e->getMessage());
      return "";
    }
  }
}
