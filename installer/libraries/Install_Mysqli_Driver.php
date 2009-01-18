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

// Convienence wrapper around the Php mysqli class
class Install_Mysqli_Driver {
  private $_mysqli;
  private $_server;
  private $_user;

  public function __construct($server, $user, $password) {
    $this->_mysqli = @mysqli_connect($server, $user, $password);
    if (!$this->_mysqli) {
      throw new Exception(mysqli_connect_error());
    }
    $this->_server = $server;
    $this->_user = $user;
  }

  public function __destruct() {
    if (!empty($this->_mysqli)) {
      @$this->_mysqli->close();
      $this->_mysqli = null;
    }
  }

  public function list_dbs() {
    $db_list = $this->_mysqli->query("SHOW DATABASES");
    $databases = array();
    if ($db_list) {
      while ($row = $db_list->fetch_row()) {
        $databases[$row[0]] = 1;
      }
    }
    return $databases;
  }

  public function get_access_rights($dbname) {
    $select = "SELECT PRIVILEGE_TYPE " .
              "  FROM `information_schema`.`schema_privileges`" .
              " WHERE `GRANTEE` = '\\'{$this->_user}\\'@\\'{$this->_server}\\''" .
              "   AND `TABLE_SCHEMA` = '$dbname';";
    print $select;
    $privileges = $this->_mysqli->query($select);
    $permissions = array();
    if ($privileges) {
      while ($row = $privileges->fetch_row()) {
        $permissions[strtolower($row[0])] = 1;
      }
    }
    return $permissions;
  }

  public function select_db($dbname) {
    $this->_mysqli->select_db($dbname);
  }

  public function list_tables($dbname) {
    $select = "SHOW TABLES FROM $dbname;";
    $db_tables = $this->_mysqli->query($select);
    $tables = array();
    if ($db_tables) {
      while ($row = $db_tables->fetch_row()) {
        $tables[strtolower($row[0])] = 1;
      }
    }
    return $tables;
  }
}

