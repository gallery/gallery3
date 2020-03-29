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
class Database_Mysql extends Database_Mysql_Core {

  public function connect()
  {
    if ($this->connection)
      return;

    if (!function_exists('mysql_connect')) {
      $msg = 'You configured your DB to use the "mysql" module but the PHP mysql module doesn\'t appear to be installed. If you are upgrading to PHP 7 you should update var/database.php to use the mysqli module instead of mysql.';
      print $msg;
      throw new Kohana_Exception($msg);
    }

    return parent::connect();
  }
}
