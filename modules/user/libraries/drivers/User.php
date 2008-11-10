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
/**
 * This is the generalized wrapper to provide user management.  The actual user management 
 * fucntionality is implemented by a driver module.  This will insulate gallery3 user management
 * from various CMS implementations.
 *
 */
interface User_Driver {
  /**
   * Performs the installation steps that a specific driver requires
   */
  public function install();

  /**
   * Performs the un install steps that a specific driver requires
   */
  public function uninstall();

  public function create_user($name, $display_name, $password, $email=null);

  public function update_user($id, $name, $display_name, $password, $email=null);

  public function get_user($id);

  public function get_user_by_name($name);

  public function delete_user($id);

  public function create_group($group_name);

  public function rename_group($id, $new_name);

  public function get_group($id);

  public function get_group_by_name($group_name);

  public function delete_group($id);

  public function add_user_to_group($group_id, $user_id);

  public function remove_user_from_group($group_id, $user_id);
}