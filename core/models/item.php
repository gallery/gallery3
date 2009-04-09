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
  private $view_restrictions = null;

  var $rules = array(
    "name" => "required|length[0,255]",
    "title" => "required|length[0,255]",
    "description" => "length[0,65535]"
  );

  /**
   * Add a set of restrictions to any following queries to restrict access only to items
   * viewable by the active user.
   * @chainable
   */
  public function viewable() {
    if (is_null($this->view_restrictions)) {
      if (user::active()->admin) {
        $this->view_restrictions = array();
      } else {
        foreach (user::group_ids() as $id) {
          // Separate the first restriction from the rest to make it easier for us to formulate
          // our where clause below
          if (empty($this->view_restrictions)) {
            $this->view_restrictions[0] = "view_$id";
          } else {
            $this->view_restrictions[1]["view_$id"] = access::ALLOW;
          }
        }
      }
    }
    switch (count($this->view_restrictions)) {
    case 0:
      break;

    case 1:
      $this->where($this->view_restrictions[0], access::ALLOW);
      break;

    default:
      $this->open_paren();
      $this->where($this->view_restrictions[0], access::ALLOW);
      $this->orwhere($this->view_restrictions[1]);
      $this->close_paren();
      break;
    }

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

  /**
   * Is this item a movie?
   * @return true if it's a movie
   */
  public function is_movie() {
    return $this->type == 'movie';
  }

  public function delete() {
    $original_path = $this->file_path();
    $original_resize_path = $this->resize_path();
    $original_thumb_path = $this->thumb_path();

    parent::delete();
    if (is_dir($original_path)) {
      @dir::unlink($original_path);
      @dir::unlink(dirname($original_resize_path));
      /*
       * The thumb path is a  path to .album.jpg not the actual directory.
       * So we need to first try to delete the path (may not exist) and then its directory.
       */
      @unlink($original_thumb_path);
      @dir::unlink(dirname($original_thumb_path));
    } else {
      @unlink($original_path);
      @unlink($original_resize_path);
      @unlink($original_thumb_path);
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
    if (!$target->is_album()) {
      throw new Exception("@todo INVALID_MOVE_TYPE $target->type");
    }

    if ($this->id == 1) {
      throw new Exception("@todo INVALID_SOURCE root album");
    }

    $original_path = $this->file_path();
    $original_resize_path = $this->resize_path();
    $original_thumb_path = $this->thumb_path();

    parent::move_to($target, true);
    $this->relative_path = null;

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
   * album: url::site("albums/2")
   * photo: url::site("photos/3")
   *
   * @param string $query the query string (eg "show=3")
   */
  public function url($query=array(), $full_uri=false) {
    $url = ($full_uri ? url::abs_site("{$this->type}s/$this->id")
            : url::site("{$this->type}s/$this->id"));
    if ($query) {
      $url .= "?$query";
    }
    return $url;
  }

  /**
   * album: /var/albums/album1/album2
   * photo: /var/albums/album1/album2/photo.jpg
   */
  public function file_path() {
    return VARPATH . "albums/" . $this->relative_path();
  }

  /**
   * album: http://example.com/gallery3/var/resizes/album1/
   * photo: http://example.com/gallery3/var/albums/album1/photo.jpg
   */
  public function file_url($full_uri=false) {
    return $full_uri ?
      url::abs_file("var/albums/" . $this->relative_path()) :
      url::file("var/albums/" . $this->relative_path());
  }

  /**
   * album: /var/resizes/album1/.thumb.jpg
   * photo: /var/albums/album1/photo.thumb.jpg
   */
  public function thumb_path() {
    $base = VARPATH . "thumbs/" . $this->relative_path();
    if ($this->is_photo()) {
      return $base;
    } else if ($this->is_album()) {
      return $base . "/.album.jpg";
    } else if ($this->is_movie()) {
      // Replace the extension with jpg
      return preg_replace("/...$/", "jpg", $base);
    }
  }

  /**
   * album: http://example.com/gallery3/var/resizes/album1/.thumb.jpg
   * photo: http://example.com/gallery3/var/albums/album1/photo.thumb.jpg
   */
  public function thumb_url($full_uri=false) {
    $base = ($full_uri ?
             url::abs_file("var/thumbs/" . $this->relative_path()) :
             url::file("var/thumbs/" . $this->relative_path()));
    if ($this->is_photo()) {
      return $base;
    } else if ($this->is_album()) {
      return $base . "/.album.jpg";
    } else if ($this->is_movie()) {
      // Replace the extension with jpg
      return preg_replace("/...$/", "jpg", $base);
    }
  }

  /**
   * album: /var/resizes/album1/.resize.jpg
   * photo: /var/albums/album1/photo.resize.jpg
   */
  public function resize_path() {
    return VARPATH . "resizes/" . $this->relative_path() .
      ($this->is_album() ? "/.album.jpg" : "");
  }

  /**
   * album: http://example.com/gallery3/var/resizes/album1/.resize.jpg
   * photo: http://example.com/gallery3/var/albums/album1/photo.resize.jpg
   */
  public function resize_url($full_uri=false) {
    return ($full_uri ?
            url::abs_file("var/resizes/" . $this->relative_path()) :
            url::file("var/resizes/" . $this->relative_path())) .
      ($this->is_album() ? "/.album.jpg" : "");
  }

  /**
   * Return the relative path to this item's file.
   * @return string
   */
  public function relative_path() {
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
    if ($column == "owner") {
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

  /**
   * @see ORM::__set()
   */
  public function __set($column, $value) {
    if ($column == "name") {
      // Clear the relative path as it is no longer valid.
      $this->relative_path = null;
    }
    parent::__set($column, $value);
  }
  
  /**
   * @see ORM::save()
   */
  public function save() {
    if (!empty($this->changed) && $this->changed != array("view_count" => "view_count")) {
      $this->updated = time();
      if (!$this->loaded) {
        $this->created = $this->updated;
        $r = ORM::factory("item")->select("MAX(weight) as max_weight")->find();
        $this->weight = $r->max_weight + 1;
        // Let albums have a weight of based on the largest -ve number so they come first.
        if ($this->is_album()) {
          $this->weight = -(0x7FFFFFFF - $this->weight;
        }
      }
    }
    return parent::save();
  }

  /**
   * Return the Item_Model representing the cover for this album.
   * @return Item_Model or null if there's no cover
   */
  public function album_cover() {
    if (!$this->is_album()) {
      return null;
    }

    if (empty($this->album_cover_item_id)) {
      return null;
    }

    try {
      return model_cache::get("item", $this->album_cover_item_id);
    } catch (Exception $e) {
      // It's possible (unlikely) that the item was deleted, if so keep going.
      return null;
    }
  }

  /**
   * Find the position of the given child id in this album.  The resulting value is 1-indexed, so
   * the first child in the album is at position 1.
   */
  public function get_position($child_id) {
    return ORM::factory("item")
      ->where("parent_id", $this->id)
      ->where("id <=", $child_id)
      ->orderby(array($this->sort_column => $this->sort_order))
      ->count_all();
  }

  /**
   * Return an <img> tag for the thumbnail.
   * @param array $extra_attrs  Extra attributes to add to the img tag
   * @param int (optional) $max Maximum size of the thumbnail (default: null)
   * @param boolean (optional) $micro_thumb Center vertically (default: false)
   * @return string
   */
  public function thumb_tag($extra_attrs=array(), $max=null, $micro_thumb=false) {
    list ($height, $width) = $this->_adjust_thumb_size($max);
    if ($micro_thumb && $max) {
      // The constant is divide by 2 to calcuate the file and 10 to convert to em
      $margin_top = ($max - $height) / 20;
      $extra_attrs["style"] = "margin-top: {$margin_top}em";
      $extra_attrs["title"] = $this->title;      
    }
    $attrs = array_merge($extra_attrs,
            array(
              "src" => $this->thumb_url(),
              "alt" => $this->title,
              "width" => $width,
              "height" => $height)
            );
    // html::image forces an absolute url which we don't want
    return "<img" . html::attributes($attrs) . "/>";
  }

  /**
   * Adjust the height based on the input maximum size or zero if not thumbnail
   * @param int $max  Maximum size of the thumbnail
   * @return array
   */
  private function _adjust_thumb_size($max) {
    $width = $this->thumb_width;
    $height = $this->thumb_height;

    if ($height) {
      if (isset($max)) {
        if ($width > $height) {
          $height = (int)($max * ($height / $width));
          $width = $max;
        } else {
          $width = (int)($max * ($width / $height));
          $height = $max;
        }
      }
    } else {
      // Missing thumbnail, can happen on albums with no photos yet.
      // @todo we should enforce a placeholder for those albums.
      $width = 0;
      $height = 0;
    }
    return array($height, $width);
  }
  
  /**
   * Return an <img> tag for the resize.
   * @param array $extra_attrs  Extra attributes to add to the img tag
   * @return string
   */
  public function resize_tag($extra_attrs) {
    $attrs = array_merge($extra_attrs,
            array("src" => $this->resize_url(),
              "alt" => $this->title,
              "width" => $this->resize_width,
              "height" => $this->resize_height)
            );
    // html::image forces an absolute url which we don't want
    return "<img" . html::attributes($attrs) . "/>";
  }

  public function movie_tag($extra_attrs) {
    $attrs = array_merge($extra_attrs,
            array("id" => "player",
              "style" => "display:block;width:400px;height:300px")
      );
    return html::anchor($this->file_url(true), "", $attrs) .
      "<script>flowplayer('player', '" .
      url::abs_file("lib/flowplayer-3.0.5.swf") .
      "'); </script>";
  }

  /**
   * Return all of the children of this node, ordered by the defined sort order.
   *
   * @chainable
   * @param   integer  SQL limit
   * @param   integer  SQL offset
   * @return array ORM
   */
  function children($limit=null, $offset=0) {
    return parent::children($limit, $offset, array($this->sort_column => $this->sort_order));
  }

  /**
   * Return all of the children of the specified type, ordered by the defined sort order.
   *
   * @param   integer  SQL limit
   * @param   integer  SQL offset
   * @param   string   type to return
   * @return object ORM_Iterator
   */
  function descendants($limit=null, $offset=0, $type=null) {
    return parent::descendants($limit, $offset, $type,
                               array($this->sort_column => $this->sort_order));
  }
}
