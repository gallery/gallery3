<?php
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

// Convienence wrapper around the Php mysqli class
class Install_Mysqli_Driver {
  private $mysqli;
  
  public function __construct($server, $user, $password) {
    $this->mysqli = @mysqli_connect($server, $user, $password);
    if (!$this->mysqli) {
      throw new Exception(mysqli_connect_error());
    }
  }

  public function __destruct() {
    if (!empty($this->mysqli)) {
      @$this->mysqli->close();
      $this->mysqli = null;
    }
  }
  
  public function list_dbs() {
    $db_list = $this->mysqli->query("SHOW DATABASES");
    $databases = array();
    if ($db_list) {
      while ($row = $db_list->fetch_row()) {
        $databases[$row[0]] = 1;
      }
    }
    return $databases;
  }
}

