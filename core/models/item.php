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
  private $relative_path = null;
  private $view_restrictions = array();

  var $rules = array();

  /**
   * Add a set of restrictions to any following queries to restrict access only to items
   * viewable by the active user.
   * @chainable
   */
  public function viewable() {
    if (empty($this->view_restrictions)) {
      foreach (user::group_ids() as $id) {
        $this->view_restrictions["view_$id"] = access::ALLOW;
      }
    }
    $this->where($this->view_restrictions);
    return $this;
  }

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

  public function delete() {
    $path = $this->file_path();
    parent::delete();
    // If there is no name, the path is invalid so don't try to delete
    if (!empty($this->name)) {
      if ($this->is_album()) {
        dir::unlink($path);
      } else {
        unlink($path);
      }
    }
  }

  /**
   * Move this item to the specified target.
   *
   * @chainable
   * @param   Item_Model $target  Target item (must be an album
   * @return  ORM_MTPP
   */
  function move_to($target) {
    $original_path = $this->file_path();
    $original_resize_path = $this->resize_path();
    $original_thumb_path = $this->thumb_path();

    parent::move_to($target, true);

    rename($original_path, $this->file_path());
    if ($this->is_album()) {
      rename(dirname($original_resize_path), dirname($this->resize_path()));
      rename(dirname($original_thumb_path), dirname($this->thumb_path()));
    } else {
      rename($original_resize_path, $this->resize_path());
      rename($original_thumb_path, $this->thumb_path());
    }

    return $this;
  }

  /**
   * album: /var/albums/album1/album2
   * photo: /var/albums/album1/album2/photo.jpg
   */
  public function file_path() {
    return VARPATH . "albums/" . $this->_relative_path();
  }

  /**
   * album: http://example.com/gallery3/var/resizes/album1/
   * photo: http://example.com/gallery3/var/albums/album1/photo.jpg
   */
  public function file_url($full_uri=false) {
    return $full_uri ?
      url::abs_file("var/albums/" . $this->_relative_path()) :
      url::file("var/albums/" . $this->_relative_path());
  }

  /**
   * album: /var/resizes/album1/.thumb.jpg
   * photo: /var/albums/album1/photo.thumb.jpg
   */
  public function thumb_path() {
    return VARPATH . "thumbs/" . $this->_relative_path() .
      ($this->type == "album" ? "/.album.jpg" : "");
  }

  /**
   * album: http://example.com/gallery3/var/resizes/album1/.thumb.jpg
   * photo: http://example.com/gallery3/var/albums/album1/photo.thumb.jpg
   */
  public function thumb_url($full_uri=true) {
    return ($full_uri ?
            url::abs_file("var/thumbs/" . $this->_relative_path()) :
            url::file("var/thumbs/" . $this->_relative_path()))  .
      ($this->type == "album" ? "/.album.jpg" : "");
  }

  /**
   * album: /var/resizes/album1/.resize.jpg
   * photo: /var/albums/album1/photo.resize.jpg
   */
  public function resize_path() {
    return VARPATH . "resizes/" . $this->_relative_path() .
      ($this->type == "album" ? "/.album.jpg" : "");
  }

  /**
   * album: http://example.com/gallery3/var/resizes/album1/.resize.jpg
   * photo: http://example.com/gallery3/var/albums/album1/photo.resize.jpg
   */
  public function resize_url($full_uri=true) {
    return ($full_uri ?
            url::abs_file("var/resizes/" . $this->_relative_path()) :
            url::file("var/resizes/" . $this->_relative_path())) .
      ($this->type == "album" ? "/.album.jpg" : "");
  }

  /**
   * Build a thumbnail for this item from the image provided with the
   * given width and height
   *
   * @chainable
   * @param string $filename the path to an image
   * @param integer $width the desired width of the thumb
   * @param integer $height the desired height of the thumb
   * @return ORM
   */
  public function set_thumb($filename, $width, $height) {
    Image::factory($filename)
      ->resize($width, $height, Image::AUTO)
      ->save($this->thumb_path());

    $dims = getimagesize($this->thumb_path());
    $this->thumb_width = $dims[0];
    $this->thumb_height = $dims[1];
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
      ->resize($width, $height, Image::AUTO)
      ->save($this->resize_path());

    $dims = getimagesize($this->resize_path());
    $this->resize_width = $dims[0];
    $this->resize_height = $dims[1];
    return $this;
  }

  /**
   * Return the relative path to this item's file.
   * @return string
   */
  private function _relative_path() {
    if (empty($this->relative_path)) {
      foreach ($this->parents() as $parent) {
        if ($parent->id > 1) {
          $paths[] = $parent->name;
        }
      }
      $paths[] = $this->name;
      $this->relative_path = implode($paths, "/");
    }
    return $this->relative_path;
  }

  /**
   * @see ORM::__get()
   */
  public function __get($column) {
    if (substr($column, -5) == "_edit") {
      $real_column = substr($column, 0, strlen($column) - 5);
      $editable = $this->type == "album" ?
        access::can("edit", $this) : access::can("edit", $this->parent());
      if ($editable) {
        return "<span class=\"gInPlaceEdit gEditField-{$this->id}-{$real_column}\">" .
          "{$this->$real_column}</span>";
      } else {
        return parent::__get($real_column);
      }
    } else if ($column == "owner") {
      // This relationship depends on an outside module, which may not be present so handle
      // failures gracefully.
      try {
        return model_cache::get("user", $this->owner_id);
      } catch (Exception $e) {
        return null;
      }
    } else {
      return parent::__get($column);
    }
  }
}
