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
abstract class Database extends Database_Core {
  protected $_table_names;

  /**
   * Kohana 2.4 introduces a new connection parameter.  If it's not specified, make sure that we
   * define it here to avoid an error later on.
   *
   * @todo: add an upgrade path to modify var/database.php so that we can avoid doing this at
   *        runtime.
   */
  protected function __construct(array $config) {
    if (!isset($config["connection"]["params"])) {
      $config["connection"]["params"] = null;
    }
    parent::__construct($config);
    if (gallery::show_profiler()) {
      $this->config['benchmark'] = true;
    }
  }

  /**
   * Parse the query string and convert any strings of the form `\([a-zA-Z0-9_]*?)\]
   * table prefix . $1
   */
  public function query($sql) {
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
      // Creating a new table; add it to the table cache.
      $open_brace = strpos($sql, "{") + 1;
      $close_brace = strpos($sql, "}", $open_brace);
      $name = substr($sql, $open_brace, $close_brace - $open_brace);
      $this->_table_names["{{$name}}"] = "`{$prefix}$name`";
    } else if (strpos($sql, "RENAME TABLE") === 0) {
      // Renaming a table; add it to the table cache.
      // You must use the form "TO {new_table_name}" exactly for this to work.
      $open_brace = strpos($sql, "TO {") + 4;
      $close_brace = strpos($sql, "}", $open_brace);
      $name = substr($sql, $open_brace, $close_brace - $open_brace);
      $this->_table_names["{{$name}}"] = "`{$prefix}$name`";
    }

    if (!isset($this->_table_names)) {
      // This should only run once on the first query
      $this->_table_names = array();
      foreach($this->list_tables() as $table_name) {
        $this->_table_names["{{$table_name}}"] = "`{$prefix}{$table_name}`";
      }
    }

    return strtr($sql, $this->_table_names);
  }

  /**
   * This is used by the unit test code to switch the active database connection.
   */
  static function set_default_instance($db) {
    self::$instances["default"] = $db;
  }

  /**
   * Escape LIKE queries, add wildcards.  In MySQL queries using LIKE, _ and % characters are
   * treated as wildcards similar to ? and *, respectively.  Therefore, we need to escape _, %,
   * and \ (the escape character itself).
   */
  static function escape_for_like($value) {
    // backslash must go first to avoid double-escaping
    return addcslashes($value, '\_%');
  }
}