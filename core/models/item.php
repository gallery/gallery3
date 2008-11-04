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
class Item_Model extends ORM_MPTT {
  protected $children = 'items';

  public function is_album() {
    return $this->type == 'album';
  }

  public function is_photo() {
    return $this->type == 'photo';
  }

  private function _get_path() {
    $paths = array();
    foreach ($this->parents() as $parent) {
      if ($parent->id > 1) {
        $paths[] = $parent->name;
      }
    }
    $path = implode($paths, "/");
    if (!$this->saved) {
      $path .= $this->name;
    }
    return $path;
  }

  public function path() {
    return VARPATH . "albums/{$this->_get_path()}";
  }

  public function thumbnail_path() {
    if ($this->is_album()) {
      return VARPATH . "thumbnails/{$this->_get_path()}";
    } else {
      $pi = pathinfo(VARPATH . "thumbnails/{$this->_get_path()}");
      return "{$pi['dirname']}/{$pi['filename']}_thumb.{$pi['extension']}";
    }
  }

  public function resize_path() {
    if ($this->is_album()) {
      return VARPATH . "thumbnails/{$this->_get_path()}";
    } else {
      $pi = pathinfo(VARPATH . "thumbnails/{$this->_get_path()}");
      return "{$pi['dirname']}/{$pi['filename']}_resize.{$pi['extension']}";
    }
  }
}
