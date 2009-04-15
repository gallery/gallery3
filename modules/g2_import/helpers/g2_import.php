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

  static function import_group($i) {
    $map = g2(GalleryCoreApi::fetchGroupNames(1, $i - 1));
    $g2_group_id = current(array_keys($map));
    $g2_group = g2(GalleryCoreApi::loadEntitiesById($g2_group_id));
    if ($g2_group->getGroupType() != GROUP_NORMAL) {
      return;
    }

    try {
      $group = group::create($g2_group->getGroupName());
    } catch (Exception $e) {
      // @todo For now we assume this is a "duplicate group" exception
      // which we will ignore.
    }
  }

  static function import_user($i) {
    $map = g2(GalleryCoreApi::fetchUsersForGroup(GROUP_EVERYBODY, 1, $i - 1));
    $g2_user_id = current(array_keys($map));
    if (g2(GalleryCoreApi::isAnonymousUser($g2_user_id))) {
      return;
    }

    $g2_user = g2(GalleryCoreApi::loadEntitiesById($g2_user_id));
    try {
      $user = user::create($g2_user->getUserName(), $g2_user->getFullName(), "");
      $user->hashed_password = $g2_user->getHashedPassword();
      $user->email = $g2_user->getEmail();
      $user->language = $g2_user->getLanguage();
      $user->save();
    } catch (Exception $e) {
      // @todo For now we assume this is a "duplicate user" exception
      // which we will ignore.
    }
  }
}
