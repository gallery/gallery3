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
  protected $has_one = array("owner" => "user");

  /**
   * Is this item an album?
   * @return true if it's an album
   */
  public function is_album() {
    return $this->type == 'album';
  }

  /**
   * Is this item a photo?
   * @return true if it's a photo
   */
  public function is_photo() {
    return $this->type == 'photo';
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
  public function thumbnail_url($index = FALSE, $protocol = FALSE) {
    if ($this->is_album()) {
      return $this->_relative_path(url::base($index, $protocol) . "var/resizes", "", "/.thumb.jpg");
    } else {
      return $this->_relative_path(url::base($index, $protocol) . "var/resizes", ".thumb", "");
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
  public function resize_url($index = FALSE, $protocol = FALSE) {
    if ($this->is_album()) {
      return $this->_relative_path(url::base($index, $protocol) . "var/resizes", "", "/.resize.jpg");
    } else {
      return $this->_relative_path(url::base($index, $protocol) . "var/resizes", ".resize", "");
    }
  }

  /**
   * Build a thumbnail for this item from the image provided with the
   * given width and height
   *
   * @chainable
   * @param string $filename the path to an image
   * @param integer $width the desired width of the thumbnail
   * @param integer $height the desired height of the thumbnail
   * @return ORM
   */
  public function set_thumbnail($filename, $width, $height) {
    Image::factory($filename)
      ->resize($width, $height, Image::WIDTH)
      ->save($this->thumbnail_path());

    $dims = getimagesize($this->thumbnail_path());
    $this->thumbnail_width = $dims[0];
    $this->thumbnail_height = $dims[1];
    return $this;
  }

  /**
   * Build a resize for this item from the image provided with the
   * given width and height
   *
   * @chainable
   * @param string $filename the path to an image
   * @param integer $width the desired width of the resize
   * @param integer $height the desired height of the resize
   * @return ORM
   */
  public function set_resize($filename, $width, $height) {
    Image::factory($filename)
      ->resize($width, $height, Image::WIDTH)
      ->save($this->resize_path());

    $dims = getimagesize($this->resize_path());
    $this->resize_width = $dims[0];
    $this->resize_height = $dims[1];
    return $this;
  }

  /**
   * Return the relative path to this item's file.
   * @param string $prefix prefix to the path (eg "/var" or "http://foo.com/var")
   * @param string $tag    a tag to specify before the extension (eg ".thumb", ".resize")
   * @param string $suffix suffix to add to end of the path
   * @return a path
   */
  private function _relative_path($prefix, $tag, $suffix) {
    $paths = array($prefix);
    foreach ($this->parents() as $parent) {
      if ($parent->id > 1) {
        $paths[] = $parent->name;
      }
    }
    $paths[] = $this->name;
    $path = implode($paths, "/");

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
   * @see ORM::__get()
   */
  public function __get($column) {
    if (substr($column, -5) == "_edit") {
      $real_column = substr($column, 0, strlen($column) - 5);
      return "<span class=\"gInPlaceEdit gEditField-{$this->id}-{$real_column}\">" .
        "{$this->$real_column}</span>";
    } else if ($column == "owner") {
      // This relationship depends on an outside module, which may not be present so handle
      // failures gracefully.
      try {
        return parent::__get($column);
      } catch (Exception $e) {
        return null;
      }
    } else {
      return parent::__get($column);
    }
  }
}
