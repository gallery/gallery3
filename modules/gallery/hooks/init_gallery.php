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

// If var/database.php doesn't exist, then we assume that the Gallery is not properly installed
// and send users to the installer.
if (!file_exists(VARPATH . "database.php")) {
  url::redirect(url::abs_file("installer"));
}

// Simple and cheap test to make sure that the database config is ok.  Do this before we do
// anything else database related.
try {
  Database::instance()->connect();
} catch (Kohana_PHP_Exception $e) {
  print "Database configuration error.  Please check var/database.php";
  exit;
}

Event::add("system.ready", array("Gallery_I18n", "instance"));
Event::add("system.ready", array("module", "load_modules"));
Event::add("system.ready", array("gallery", "ready"));
Event::add("system.post_routing", array("url", "parse_url"));
Event::add("system.post_routing", array("gallery", "maintenance_mode"));
Event::add("system.post_routing", array("gallery", "private_gallery"));
Event::add("system.shutdown", array("gallery", "shutdown"));

// @todo once we convert to Kohana 2.4 this doesn't have to be here
set_error_handler(array("gallery_error", "error_handler"));

// Override the cookie if we have a session id in the URL.
// @todo This should probably be an event callback
$input = Input::instance();
if ($g3sid = $input->post("g3sid", $input->get("g3sid"))) {
  $_COOKIE["g3sid"] = $g3sid;
}

if ($user_agent = $input->post("user_agent", $input->get("user_agent"))) {
  $_SERVER["HTTP_USER_AGENT"] = $user_agent;
}
