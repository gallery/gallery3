<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
/**
 * PHP Mail Configuration parameters
 * from        => email address that appears as the from address
 * line-length => word wrap length (PHP documentations suggest no larger tha 70 characters
 * reply-to    => what goes into the reply to header
 */
$config["ranges"] = array(
  "Digibug1" => array("low" => "65.249.152.0", "high" => "65.249.159.255"),
  "Digibug2" => array("low" => "208.122.55.0", "high" => "208.122.55.255")
);
