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
function g2() {
  $args = func_get_arg(0);
  $ret = array_shift($args);
  if ($ret) {
    Kohana::log("error", "Gallery2 function failed with: " . $ret->getAsText());
    throw new Exception("@todo G2_FUNCTION_FAILED");
  }
  if (count($args) == 1) {
    return $args[0];
  } else {
    return $args;
  }
}

class g2_import_Core {
  public static $init = false;
  public static $map = array();

  private static $current_g2_item = null;

  static function is_configured() {
    return module::get_var("g2_import", "embed_path");
  }

  static function is_initialized() {
    return g2_import::$init;
  }

  static function init() {
    if (g2_import::$init) {
      return;
    }

    $embed_path = module::get_var("g2_import", "embed_path");
    if (empty($embed_path)) {
      throw new Exception("@todo G2_IMPORT_NOT_CONFIGURED");
    }

    g2_import::$init = g2_import::init_embed($embed_path);
  }

  static function is_valid_embed_path($embed_path) {
    return file_exists($embed_path) && g2_import::init_embed($embed_path);
  }

  static function init_embed($embed_path) {
    require($embed_path);
    if (!class_exists("GalleryEmbed")) {
      return false;
    }

    $ret = GalleryEmbed::init();
    if ($ret) {
      return false;
    }

    $admin_group_id = g2(GalleryCoreApi::getPluginParameter("module", "core", "id.adminGroup"));
    $admins = g2(GalleryCoreApi::fetchUsersForGroup($admin_group_id, 1));
    $admin_id = current(array_flip($admins));
    $admin = g2(GalleryCoreApi::loadEntitiesById($admin_id));
    $GLOBALS["gallery"]->setActiveUser($admin);

    return true;
  }

  static function version() {
    $core = g2(GalleryCoreApi::loadPlugin("module", "core"));
    $versions = $core->getInstalledVersions();
    return $versions["gallery"];
  }

  static function stats() {
    GalleryCoreApi::requireOnce("modules/comment/classes/GalleryCommentHelper.class");

    $root_album_id = g2(GalleryCoreApi::getDefaultAlbumId());
    $stats["users"] = g2(GalleryCoreApi::fetchUserCount());
    $stats["groups"] = g2(GalleryCoreApi::fetchGroupCount());
    $stats["albums"] = g2(GalleryCoreApi::fetchItemIdCount("GalleryAlbumItem"));
    $stats["photos"] = g2(GalleryCoreApi::fetchItemIdCount("GalleryPhotoItem"));
    $stats["movies"] = g2(GalleryCoreApi::fetchItemIdCount("GalleryMovieItem"));
    list (, $stats["comments"]) = g2(GalleryCommentHelper::fetchAllComments($root_album_id, 1));
    return $stats;
  }

  static function import_group(&$queue) {
    $g2_group_id = array_shift($queue);
    if (self::map($g2_group_id)) {
      return;
    }

    $g2_group = g2(GalleryCoreApi::loadEntitiesById($g2_group_id));
    switch ($g2_group->getGroupType()) {
    case GROUP_NORMAL:
      $group = group::create($g2_group->getGroupName());
      break;

    case GROUP_ALL_USERS:
      $group = group::registered_users();
      break;

    case GROUP_SITE_ADMINS:
      break;  // This is not a group in G3

    case GROUP_EVERYBODY:
      $group = group::everybody();
      break;
    }

    if (isset($group)) {
      self::set_map($g2_group->getId(), $group->id);
    }
  }

  static function import_user(&$queue) {
    $g2_user_id = array_shift($queue);
    if (self::map($g2_user_id)) {
      return;
    }

    if (g2(GalleryCoreApi::isAnonymousUser($g2_user_id))) {
      self::set_map($g2_user_id, user::guest()->id);
      return;
    }

    $g2_admin_group_id =
      g2(GalleryCoreApi::getPluginParameter("module", "core", "id.adminGroup"));
    $g2_user = g2(GalleryCoreApi::loadEntitiesById($g2_user_id));
    $g2_groups = g2(GalleryCoreApi::fetchGroupsForUser($g2_user->getId()));

    try {
      $user = user::create($g2_user->getUsername(), $g2_user->getfullname(), "");
    } catch (Exception $e) {
      // @todo For now we assume this is a "duplicate user" exception
      $user = user::lookup_by_name($g2_user->getUsername());
    }

    $user->hashed_password = $g2_user->getHashedPassword();
    $user->email = $g2_user->getEmail();
    $user->locale = $g2_user->getLanguage();
    foreach ($g2_groups as $g2_group_id => $g2_group_name) {
      if ($g2_group_id == $g2_admin_group_id) {
        $user->admin = true;
      } else {
        $user->add(ORM::factory("group", self::map($g2_group_id)));
      }
    }
    $user->save();

    self::set_map($g2_user->getId(), $user->id);
  }

  static function import_album(&$queue) {
    // The queue is a set of nested associative arrays where the key is the album id and the
    // value is an array of similar arrays.  We'll do a breadth first tree traversal using the
    // queue to keep our state.  Doing it breadth first means that the parent will be created by
    // the time we get to the child.

    // Dequeue the current album  and enqueue its children
    list($g2_album_id, $children) = each($queue);
    unset($queue[$g2_album_id]);
    foreach ($children as $key => $value) {
      $queue[$key] = $value;
    }

    if (self::map($g2_album_id)) {
      return;
    }

    // Load the G2 album item, and figure out its parent in G3.
    $g2_album = g2(GalleryCoreApi::loadEntitiesById($g2_album_id));
    if ($g2_album->getParentId() == null) {
      return;
    }
    $parent_album = ORM::factory("item", self::map($g2_album->getParentId()));

    $album = album::create(
      $parent_album,
      $g2_album->getPathComponent(),
      $g2_album->getTitle(),
      self::extract_description($g2_album),
      self::map($g2_album->getOwnerId()));

    $album->view_count = g2(GalleryCoreApi::fetchItemViewCount($g2_album_id));
    $album->created = $g2_album->getCreationTimestamp();

    // @todo support "keywords", "originationTimestamp", and "random" sort orders.
    $order_map = array(
      "originationTimestamp" => "captured",
      "creationTimestamp" => "created",
      "description" => "description",
      "modificationTimestamp" => "updated",
      "orderWeight" => "weight",
      "pathComponent" => "name",
      "summary" => "description",
      "title" => "title",
      "viewCount" => "view_count");
    $direction_map = array(
      ORDER_ASCENDING => "asc",
      ORDER_DESCENDING => "desc");
    if (array_key_exists($g2_order = $g2_album->getOrderBy(), $order_map)) {
      $album->sort_column = $order_map[$g2_order];
      $album->sort_order = $direction_map[$g2_album->getOrderDirection()];
    }
    $album->save();

    self::set_map($g2_album_id, $album->id);

    // @todo import keywords as tags
    // @todo import album highlights
  }

  static function import_item(&$queue) {
    $g2_item_id = array_shift($queue);

    if (self::map($g2_item_id)) {
      return;
    }

    self::$current_g2_item = $g2_item = g2(GalleryCoreApi::loadEntitiesById($g2_item_id));
    $parent = ORM::factory("item", self::map($g2_item->getParentId()));
    switch ($g2_item->getEntityType()) {
    case "GalleryPhotoItem":
      $item = photo::create(
        $parent,
        g2($g2_item->fetchPath()),
        $g2_item->getPathComponent(),
        $g2_item->getTitle(),
        self::extract_description($g2_item),
        self::map($g2_item->getOwnerId()));
      break;

    case "GalleryMovieItem":
      // @todo we should transcode other types into FLV
      if (in_array($g2_item->getMimeType(), array("video/mp4", "video/x-flv"))) {
        $item = movie::create(
          $parent,
          g2($g2_item->fetchPath()),
          $g2_item->getPathComponent(),
          $g2_item->getTitle(),
          self::extract_description($g2_item),
          self::map($g2_item->getOwnerId()));
      }
      break;

    default:
      // Ignore
      break;
    }

    if (isset($item)) {
      self::set_map($g2_item_id, $item->id);
    }
  }

  // If the thumbnails and resizes created for the Gallery2 photo match the dimensions of the
  // ones we expect to create for Gallery3, then copy the files over instead of recreating them.
  static function copy_matching_thumbnails_and_resizes($item) {
    // Precaution: if the Gallery2 item was watermarked, or we have the Gallery3 watermark module
    // active then we'd have to do something a lot more sophisticated here.  For now, just skip
    // this step in those cases.
    if (module::is_installed("watermark")) {
      return;
    }

    // For now just do the copy for photos and movies.  Albums are tricky because we're may not
    // yet be setting their album cover properly.
    // @todo implement this for albums also
    if (!$item->is_movie() && !$item->is_photo()) {
      return;
    }

    $g2_item_id = self::$current_g2_item->getId();
    $derivatives = g2(GalleryCoreApi::fetchDerivativesByItemIds(array($g2_item_id)));

    $target_thumb_size = module::get_var("core", "thumb_size");
    $target_resize_size = module::get_var("core", "resize_size");
    foreach ($derivatives[$g2_item_id] as $derivative) {
      if ($derivative->getPostFilterOperations()) {
        // Let's assume for now that this is a watermark operation, which we can't handle.
        continue;
      }

      if ($derivative->getDerivativeType() == DERIVATIVE_TYPE_IMAGE_THUMBNAIL &&
          $item->thumb_dirty &&
          ($derivative->getWidth() == $target_thumb_size ||
           $derivative->getHeight() == $target_thumb_size)) {
        copy(g2($derivative->fetchPath()), $item->thumb_path());
        $item->thumb_height = $derivative->getHeight();
        $item->thumb_width = $derivative->getWidth();
        $item->thumb_dirty = false;
      }

      if ($derivative->getDerivativeType() == DERIVATIVE_TYPE_IMAGE_RESIZE &&
          $item->resize_dirty &&
          ($derivative->getWidth() == $target_resize_size ||
           $derivative->getHeight() == $target_resize_size)) {
        copy(g2($derivative->fetchPath()), $item->resize_path());
        $item->resize_height = $derivative->getHeight();
        $item->resize_width = $derivative->getWidth();
        $item->resize_dirty = false;
      }
    }
    $item->save();
  }

  static function common_sizes() {
    global $gallery;
    foreach (array("resize" => DERIVATIVE_TYPE_IMAGE_RESIZE,
                   "thumb" => DERIVATIVE_TYPE_IMAGE_THUMBNAIL) as $type => $g2_enum) {
      $results = g2($gallery->search(
        "SELECT COUNT(*) AS c, [GalleryDerivativeImage::width] " .
        "FROM [GalleryDerivativeImage], [GalleryDerivative] " .
        "WHERE [GalleryDerivativeImage::id] = [GalleryDerivative::id] " .
        "  AND [GalleryDerivative::derivativeType] = ? " .
        "  AND [GalleryDerivativeImage::width] >= [GalleryDerivativeImage::height] " .
        "GROUP BY [GalleryDerivativeImage::width] " .
        "ORDER by c DESC",
        array($g2_enum),
        array("limit" => array(1))));
      $row = $results->nextResult();
      $sizes[$type] = array("size" => $row[1], "count" => $row[0]);

      $results = g2($gallery->search(
        "SELECT COUNT(*) AS c, [GalleryDerivativeImage::height] " .
        "FROM [GalleryDerivativeImage], [GalleryDerivative] " .
        "WHERE [GalleryDerivativeImage::id] = [GalleryDerivative::id] " .
        "  AND [GalleryDerivative::derivativeType] = ? " .
        "  AND [GalleryDerivativeImage::height] >= [GalleryDerivativeImage::width] " .
        "GROUP BY [GalleryDerivativeImage::height] " .
        "ORDER by c DESC",
        array($g2_enum),
        array("limit" => array(1))));
      $row = $results->nextResult();
      // Compare the counts.  If the best fitting height does not match the best fitting width,
      // then pick the one with the largest count.  Otherwise, sum them.
      if ($sizes[$type]["size"] != $row[1]) {
        if ($row[0] > $sizes[$type]["count"]) {
          $sizes[$type] = array("size" => $row[1], "count" => $row[0]);
        }
      } else {
        $sizes[$type]["count"] += $row[0];
      }

      $results = g2($gallery->search(
        "SELECT COUNT(*) FROM [GalleryDerivative] WHERE [GalleryDerivative::derivativeType] = ?",
        array($g2_enum)));
      $row = $results->nextResult();
      $sizes[$type]["total"] = $row[0];
    }

    return $sizes;
  }

  static function extract_description($g2_item) {
    // If the summary is a subset of the description just import the description, else import both.
    $g2_summary = $g2_item->getSummary();
    $g2_description = $g2_item->getDescription();
    if (!$g2_summary ||
        $g2_summary == $g2_description ||
        strstr($g2_description, $g2_summary) !== false) {
      $description = $g2_description;
    } else {
      $description = $g2_summary . " " . $g2_description;
    }
    return $description;
  }

  static function get_item_ids($min_id) {
    global $gallery;

    $ids = array();
    $results = g2($gallery->search(
      "SELECT [GalleryItem::id] " .
      "FROM [GalleryEntity], [GalleryItem] " .
      "WHERE [GalleryEntity::id] = [GalleryItem::id] " .
      "AND [GalleryEntity::entityType] IN ('GalleryPhotoItem', 'GalleryMovieItem') " .
      "AND [GalleryItem::id] > ? " .
      "ORDER BY [GalleryItem::id] ASC",
      array($min_id),
      array("limit" => array("count" => 100))));
    while ($result = $results->nextResult()) {
      $ids[] = $result[0];
    }
    return $ids;
  }

  static function map($g2_id) {
    if (!array_key_exists($g2_id, self::$map)) {
      $g2_map = ORM::factory("g2_map")->where("g2_id", $g2_id)->find();
      self::$map[$g2_id] = $g2_map->loaded ? $g2_map->g3_id : null;
    }

    return self::$map[$g2_id];
  }

  static function set_map($g2_id, $g3_id) {
    $g2_map = ORM::factory("g2_map");
    $g2_map->g3_id = $g3_id;
    $g2_map->g2_id = $g2_id;
    $g2_map->save();
    self::$map[$g2_id] = $g3_id;
  }
}
