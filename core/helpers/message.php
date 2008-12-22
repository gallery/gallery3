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
class message_Core {
  public function add($msg, $severity=log::INFO) {
    $session = Session::instance();
    $status = $session->get("messages");
    $status[] = array($msg, $severity);
    $session->set("messages", $status);
  }

  public function get() {
    $messages = Session::instance()->get_once("messages", array());
    if ($messages) {
      $buf = "<ul id=\"gMessages\">";
      foreach ($messages as $msg) {
        $buf .= "<li class=\"" . self::severity_class($msg[1]) . "\">$msg[0]</li>";
      }
      return $buf .= "</ul>";
    }
  }

  public function severity_class($severity) {
    switch($severity) {
    case log::INFO:
      return "gInfo";

    case log::WARNING:
      return "gWarning";

    case log::ERROR:
      return "gError";
    }
  }
}
