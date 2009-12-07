<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
abstract class Database extends Database_Core {
  protected $_table_names;

  /**
   * Parse the query string and convert any strings of the form `\([a-zA-Z0-9_]*?)\]
   * table prefix . $1
   */
  public function query($sql = '') {
    if (!empty($sql)) {
      $sql = $this->add_table_prefixes($sql);
    }
    return parent::query($sql);
  }

  public function add_table_prefixes($sql) {
    $prefix = $this->config["table_prefix"];
    if (strpos($sql, "SHOW TABLES") === 0) {
      /*
       * Don't ignore "show tables", otherwise we could have a infinite
       * @todo this may have to be changed if we support more than mysql
       */
      return $sql;
    } else if (strpos($sql, "CREATE TABLE") === 0) {
      // Creating a new table add it to the table cache.
      $open_brace = strpos($sql, "{") + 1;
      $close_brace = strpos($sql, "}", $open_brace);
      $name = substr($sql, $open_brace, $close_brace - $open_brace);
      $this->_table_names["{{$name}}"] = "{$prefix}$name";
    }

    if (!isset($this->_table_names)) {
      // This should only run once on the first query
      $this->_table_names =array();
      $len = strlen($prefix);
      foreach($this->list_tables() as $table_name) {
        if ($len > 0) {
          $naked_name = strpos($table_name, $prefix) !== 0 ?
            $table_name : substr($table_name, $len);
        } else {
          $naked_name = $table_name;
        }
        $this->_table_names["{{$naked_name}}"] = $table_name;
      }
    }

    return empty($this->_table_names) ? $sql : strtr($sql, $this->_table_names);
  }
}