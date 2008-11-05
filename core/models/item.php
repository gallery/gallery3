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

  private function _relative_path($prefix, $tag, $suffix) {
    $paths = array($prefix);
    foreach ($this->parents() as $parent) {
      if ($parent->id > 1) {
        $paths[] = $parent->name;
      }
    }
    $path = implode($paths, "/");
    if (!$this->saved) {
      $path .= $this->name;
    }

    if ($tag) {
      $pi = pathinfo($path);
      $path = "{$pi['dirname']}/{$pi['filename']}{$tag}.{$pi['extension']}";
    }

    if ($suffix) {
      $path .= $suffix;
    }
    return $path;
  }

  /**
   * album: /var/albums/album1/album2
   * photo: /var/albums/album1/album2/photo.jpg
   */
  public function file_path() {
    if ($this->is_album()) {
      return $this->_relative_path(VARPATH . "albums", "", "");
    } else {
      return $this->_relative_path(VARPATH . "albums", "", "");
    }
  }

  /**
   * album: /var/resizes/album1/.thumb.jpg
   * photo: /var/albums/album1/photo.thumb.jpg
   */
  public function thumbnail_path() {
    if ($this->is_album()) {
      return $this->_relative_path(VARPATH . "resizes", "", "/.thumb.jpg");
    } else {
      return $this->_relative_path(VARPATH . "resizes", ".thumb", "");
    }
  }

  /**
   * album: http://example.com/gallery3/var/resizes/album1/.thumb.jpg
   * photo: http://example.com/gallery3/var/albums/album1/photo.thumb.jpg
   */
  public function thumbnail_url() {
    if ($this->is_album()) {
      return $this->_relative_path(url::base() . "var/resizes", "", "/.thumb.jpg");
    } else {
      return $this->_relative_path(url::base() . "var/resizes", ".thumb", "");
    }
  }

  /**
   * album: /var/resizes/album1/.resize.jpg
   * photo: /var/albums/album1/photo.resize.jpg
   */
  public function resize_path() {
    if ($this->is_album()) {
      return $this->_relative_path(VARPATH . "resizes", "", "/.resize.jpg");
    } else {
      return $this->_relative_path(VARPATH . "resizes", ".resize", "");
    }
  }

  /**
   * album: http://example.com/gallery3/var/resizes/album1/.resize.jpg
   * photo: http://example.com/gallery3/var/albums/album1/photo.resize.jpg
   */
  public function resize_url() {
    if ($this->is_album()) {
      return $this->_relative_path(url::base() . "var/resizes", "", "/.resize.jpg");
    } else {
      return $this->_relative_path(url::base() . "var/resizes", ".resize", "");
    }
  }
}
