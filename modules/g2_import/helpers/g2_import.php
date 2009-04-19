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
    list (, $stats["comments"]) = g2(GalleryCommentHelper::fetchAllComments($root_album_id, 1));
    return $stats;
  }

  static function import_group(&$queue, &$map) {
    $g2_group_id = array_shift($queue);
    if (array_key_exists($g2_group_id, $map)) {
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

    $map[$g2_group->getId()] = $group->id;
    $g2_map = ORM::factory("g2_map");
    $g2_map->g3_id = $group->id;
    $g2_map->g2_id = $g2_group->getId();
    $g2_map->save();
  }

  static function import_user(&$queue, &$map) {
    $g2_user_id = array_shift($queue);
    if (array_key_exists($g2_user_id, $map)) {
      return;
    }

    if (g2(GalleryCoreApi::isAnonymousUser($g2_user_id))) {
      return;
    }

    $g2_admin_group_id =
      g2(GalleryCoreApi::getPluginParameter("module", "core", "id.adminGroup"));;
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
        $user->add(ORM::factory("group", $map[$g2_group_id]));
      }
    }
    $user->save();

    $map[$g2_user->getId()] = $user->id;
    $g2_map = ORM::factory("g2_map");
    $g2_map->g3_id = $user->id;
    $g2_map->g2_id = $g2_user->getId();
    $g2_map->save();
  }

  static function import_album(&$queue, &$map) {
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

    if (array_key_exists($g2_album_id, $map)) {
      return;
    }

    // Load the G2 album item, and figure out its parent in G3.
    $g2_album = g2(GalleryCoreApi::loadEntitiesById($g2_album_id));
    if ($g2_album->getParentId() == null) {
      return;
    }
    $parent_album = ORM::factory("item", $map[$g2_album->getParentId()]);

    // If the summary is a subset of the description just import the description, else import both.
    $g2_summary = $g2_album->getSummary();
    $g2_description = $g2_album->getDescription();
    if (!$g2_summary ||
        $g2_summary == $g2_description ||
        strstr($g2_description, $g2_summary) !== false) {
      $description = $g2_description;
    } else {
      $description = $g2_summary . " " . $g2_description;
    }

    $album = album::create(
      $parent_album,
      $g2_album->getPathComponent(),
      $g2_album->getTitle(),
      $description,
      $map[$g2_album->getOwnerId()]);

    $album->view_count = g2(GalleryCoreApi::fetchItemViewCount($g2_album_id));
    $album->created = $g2_album->getCreationTimestamp();
    $album->save();

    $map[$g2_album->getId()] = $album->id;
    $g2_map = ORM::factory("g2_map");
    $g2_map->g3_id = $album->id;
    $g2_map->g2_id = $g2_album->getId();
    $g2_map->save();

    // @todo import origination timestamp
    // @todo import sort order
    // @todo import keywords as tags
  }
}
