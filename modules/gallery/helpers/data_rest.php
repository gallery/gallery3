<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
 * This resource returns the raw contents of Item_Model data files.  It's analogous to the
 * file_proxy controller, but it uses the REST authentication model.
 */
class data_rest_Core {
  static function get($request) {
    $item = rest::resolve($request->url);
    access::required("view", $item);

    $p = $request->params;
    if (!isset($p->size) || !in_array($p->size, array("thumb", "resize", "full"))) {
      throw new Rest_Exception("Bad Request", 400, array("errors" => array("size" => "invalid")));
    }

    switch ($p->size) {
    case "thumb":
      $file = $item->thumb_path();
      break;

    case "resize":
      $file = $item->resize_path();
      break;

    case "full":
      $file = $item->file_path();
      break;
    }

    if (!file_exists($file)) {
      throw new Kohana_404_Exception();
    }

    // Note: this code is roughly duplicated in data_rest, so if you modify this, please look to
    // see if you should make the same change there as well.
    //
    // We don't have a cache buster in the url, so don't set cache headers here.
    // We don't need to save the session for this request
    Session::instance()->abort_save();

    if ($item->is_album() && !$item->album_cover_item_id) {
      // No thumbnail.  Return nothing.
      // @todo: what should we do here?
      return;
    }

    // Dump out the image.  If the item is a movie, then its thumbnail will be a JPG.
    if ($item->is_movie() && $p->size == "thumb") {
      header("Content-Type: image/jpeg");
    } else if ($item->is_album()) {
      header("Content-Type: " . $item->album_cover()->mime_type);
    } else {
      header("Content-Type: {$item->mime_type}");
    }
    Kohana::close_buffers(false);

    if (isset($p->encoding) && $p->encoding == "base64") {
      print base64_encode(file_get_contents($file));
    } else {
      readfile($file);
    }

    // We must exit here to keep the regular REST framework reply code from adding more bytes on
    // at the end or tinkering with headers.
    exit;
  }

  static function resolve($id) {
    $item = ORM::factory("item", $id);
    if (!access::can("view", $item)) {
      throw new Kohana_404_Exception();
    }
    return $item;
  }

  static function url($item, $size) {
    return url::abs_site("rest/data/{$item->id}?size=$size");
  }
}
