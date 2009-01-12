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
class Install_Mysql_Driver {
  private $_link;
  private $_server;
  private $_user;
  
  public function __construct($server, $user, $password) {
    $this->_link = @mysql_connect($server, $user, $password);
    if (!$this->_link) {
      throw new Exception(mysql_error());
    }
    $this->_server = $server;
    $this->_user = $user;
  }

  public function __destruct() {
    if (!empty($this->_link)) {
      @mysql_close($this->_link);
    }
  }

  public function list_dbs() {
    $db_list = mysql_list_dbs($this->_link);
    $databases = array();
    while ($row = mysql_fetch_object($db_list)) {
      $databases[$row->Database] = 1;
    }
    return $databases;
  }

  public function get_access_rights($dbname) {
    $select = "SELECT PRIVILEGE_TYPE " .
              "  FROM `information_schema`.`schema_privileges`" .
              " WHERE `GRANTEE` = '\\'{$this->_user}\\'@\\'{$this->_server}\\''" .
              "   AND `TABLE_SCHEMA` = '$dbname';";
    $privileges = mysql_query($select, $this->_link);
    $permissions = array();
    if ($privileges) {
      while ($row = mysql_fetch_assoc($privileges)) {
        $permissions[strtolower($row["PRIVILEGE_TYPE"])] = 1;
      }
    }
    return $permissions;
  }
  
}
