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

// Admin controllers are not available, except via /admin
$config["^admin_.*"] = null;

// Redirect /form/add/admin/controller and /form/edit/admin/controller to
// admin/controller/form_(add|edit)/parms. provides the same as below for admin pages
$config["^form/(edit|add)/admin/(\w+)/(.*)$"] = "admin/$2/form_$1/$3";

// Redirect /form/add and /form/edit to the module/form_(add|edit)/parms.
$config["^form/(edit|add)/(\w+)/(.*)$"] = "$2/form_$1/$3";

// Default page is the root album
$config["_default"] = "albums";
