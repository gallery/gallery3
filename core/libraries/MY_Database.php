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
class Database extends Database_Core {
  public function open_paren() {
    $this->where[] = "(";
    return $this;
  }

  public function close_paren() {
    // Search backwards for the last opening paren and resolve it
    $i = count($this->where) - 1;
    $this->where[$i] .= ")";
    while (--$i >= 0) {
      if ($this->where[$i] == "(") {
        // Remove the paren from the where clauses, and add it to the right of the operator of the
        // next where clause.  If removing the paren makes the next where clause the first element
        // in the where list, then the operator shouldn't be there.  It's there because we
        // calculate whether or not we need an operator based on the number of where clauses, and
        // the open paren seems like a where clause even though it isn't.
        array_splice($this->where, $i, 1);
        $this->where[$i] = preg_replace("/^(AND|OR) /", $i ? "\\1 (" : "(", $this->where[$i]);
        return $this;
      }
    }

    throw new Kohana_Database_Exception('database.missing_open_paren');
  }

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
    return preg_replace("#{([a-zA-Z0-9_]+)}#", "{$prefix}$1", $sql);
  }
}