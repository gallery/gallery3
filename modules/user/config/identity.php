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
/*
 * @package  Identity
 *
 * User settings, defined as arrays, or "groups". If no group name is
 * used when loading the cache library, the group named "default" will be used.
 *
 * Each group can be used independently, and multiple groups can be used at once.
 *
 * Group Options:
 *  driver        - User backend driver. Gallery comes with Gallery user driver.
 *  allow_updates - Flag to indicate that the back end allows updates.
 *  params        - Driver parameters, specific to each driver.
 */
$config["user"] = array (
  "driver"        => "gallery",
  "allow_updates" => true,
  "params"        => array(),
);
