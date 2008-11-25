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
 * This class implements Gallery's specific needs for Atom feeds.
 *
 */
class Gallery_Atom_Feed_Core extends Atom_Feed {
  function __construct() {
    parent::__construct("feed");

    /* Set feed ID and self link. */
    $this->id(html::specialchars(url::get_absolute_url()));
    $this->link()
      ->rel("self")
      ->href(url::get_absolute_url());
  }

  public function link() {
    return $this->add_child("Gallery_Atom_Link", "link");
  }
}
