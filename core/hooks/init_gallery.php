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

if (!file_exists(VARPATH . "database.php")) {
  header("Location: ../installer");
  exit();
}

Event::add("system.ready", array("I18n", "instance"));
Event::add("system.post_routing", array("theme", "load_themes"));
Event::add("system.ready", array("module", "load_modules"));
Event::add("system.post_routing", array("url", "parse_url"));
Event::add("system.shutdown", array("module", "shutdown"));
Event::add("system.post_routing", array("core", "maintenance_mode"));

// Override the cookie if we have a session id in the URL.
// @todo This should probably be an event callback
$input = Input::instance();
if ($g3sid = $input->post("g3sid", $input->get("g3sid"))) {
  $_COOKIE["g3sid"] = $g3sid;
}

if ($user_agent = $input->post("user_agent", $input->get("user_agent"))) {
  Kohana::$user_agent = $user_agent;
}
