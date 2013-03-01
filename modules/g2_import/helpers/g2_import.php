<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
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

class g2_import_Core {
  public static $init = false;
  public static $map = array();
  public static $g2_base_url = null;

  private static $current_g2_item = null;
  private static $error_reporting = null;

  static function is_configured() {
    return module::get_var("g2_import", "embed_path");
  }

  static function is_initialized() {
    return g2_import::$init == "ok";
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
    $mod_path = VARPATH . "modules/g2_import/" . md5($embed_path);
    if (file_exists($mod_path)) {
      dir::unlink($mod_path);
    }
    return g2_import::init_embed($embed_path);
  }

  /**
   * Initialize the embedded Gallery 2 instance.  Call this before any other Gallery 2 calls.
   *
   * Return values:
   *  "ok"      - the Gallery 2 install is fine
   *  "missing" - the embed path does not exist
   *  "invalid" - the install path is not a valid Gallery 2 code base
   *  "broken"  - the embed path is correct, but the Gallery 2 install is broken
   */
  static function init_embed($embed_path) {
    if (!is_file($embed_path)) {
      return "missing";
    }

    try {
     // Gallery 2 defines a class called Gallery.  So does Gallery 3.  They don't get along.  So do
     // a total hack here and copy over a few critical files (embed.php, main.php, bootstrap.inc
     // and Gallery.class) and munge them so that we can rename the Gallery class to be
     // G2_Gallery.   Is this retarded?  Why yes it is.
     //
     // Store the munged files in a directory that's the md5 hash of the embed path so that
     // multiple import sources don't interfere with each other.

     $mod_path = VARPATH . "modules/g2_import/" . md5($embed_path);
     if (!file_exists($mod_path) || !file_exists("$mod_path/embed.php")) {
       @dir::unlink($mod_path);
       mkdir($mod_path);

       $config_dir = dirname($embed_path);
       if (filesize($embed_path) > 200) {
         // Regular install
         $base_dir = $config_dir;
       } else {
         // Multisite install.  Line 2 of embed.php will be something like:
         //   require('/usr/home/bharat/public_html/gallery2/embed.php');
         $lines = file($embed_path);
         preg_match("#require\('(.*)/embed.php'\);#", $lines[2], $matches);
         $base_dir = $matches[1];
       }

       file_put_contents(
         "$mod_path/embed.php",
         str_replace(
           array(
             "require_once(dirname(__FILE__) . '/modules/core/classes/GalleryDataCache.class');",
             "require(dirname(__FILE__) . '/modules/core/classes/GalleryEmbed.class');"),
           array(
             "require_once('$base_dir/modules/core/classes/GalleryDataCache.class');",
             "require('$base_dir/modules/core/classes/GalleryEmbed.class');"),
           array_merge(
             array("<?php defined(\"SYSPATH\") or die(\"No direct script access.\") ?>\n"),
             file("$base_dir/embed.php"))));

       file_put_contents(
         "$mod_path/main.php",
         str_replace(
           array(
             "include(dirname(__FILE__) . '/bootstrap.inc');",
             "require_once(dirname(__FILE__) . '/init.inc');"),
           array(
             "include(dirname(__FILE__) . '/bootstrap.inc');",
             "require_once('$base_dir/init.inc');"),
           array_merge(
             array("<?php defined(\"SYSPATH\") or die(\"No direct script access.\") ?>\n"),
             file("$base_dir/main.php"))));

       file_put_contents(
         "$mod_path/bootstrap.inc",
         str_replace(
           array(
             "require_once(dirname(__FILE__) . '/modules/core/classes/Gallery.class');",
             "require_once(dirname(__FILE__) . '/modules/core/classes/GalleryDataCache.class');",
             "define('GALLERY_CONFIG_DIR', dirname(__FILE__));",
             "\$gallery =& new Gallery();",
             "\$GLOBALS['gallery'] =& new Gallery();",
             "\$gallery = new Gallery();"),
           array(
             "require_once(dirname(__FILE__) . '/Gallery.class');",
             "require_once('$base_dir/modules/core/classes/GalleryDataCache.class');",
             "define('GALLERY_CONFIG_DIR', '$config_dir');",
             "\$gallery =& new G2_Gallery();",
             "\$GLOBALS['gallery'] =& new G2_Gallery();",
             "\$gallery = new G2_Gallery();"),
           array_merge(
             array("<?php defined(\"SYSPATH\") or die(\"No direct script access.\") ?>\n"),
             file("$base_dir/bootstrap.inc"))));

       file_put_contents(
         "$mod_path/Gallery.class",
         str_replace(
           array("class Gallery",
                 "function Gallery"),
           array("class G2_Gallery",
                 "function G2_Gallery"),
           array_merge(
             array("<?php defined(\"SYSPATH\") or die(\"No direct script access.\") ?>\n"),
             file("$base_dir/modules/core/classes/Gallery.class"))));
     } else {
       // Ok, this is a good one.  If you're running a bytecode accelerator and you move your
       // Gallery install, these files sometimes get cached with the wrong path and then fail to
       // load properly.
       // Documented in https://sourceforge.net/apps/trac/gallery/ticket/1253
       touch("$mod_path/embed.php");
       touch("$mod_path/main.php");
       touch("$mod_path/bootstrap.inc");
       touch("$mod_path/Gallery.class.inc");
     }

      require("$mod_path/embed.php");
      if (!class_exists("GalleryEmbed")) {
        return "invalid";
      }

      $ret = GalleryEmbed::init();
      if ($ret) {
        Kohana_Log::add("error", "Gallery 2 call failed with: " . $ret->getAsText());
        return "broken";
      }

      $admin_group_id = g2(GalleryCoreApi::getPluginParameter("module", "core", "id.adminGroup"));
      $admins = g2(GalleryCoreApi::fetchUsersForGroup($admin_group_id, 1));
      $admin_id = current(array_flip($admins));
      $admin = g2(GalleryCoreApi::loadEntitiesById($admin_id));
      $GLOBALS["gallery"]->setActiveUser($admin);

      // Make sure we have an embed location so that embedded url generation comes out ok.  Without
      // this, the Gallery2 ModRewrite code won't try to do url generation.
      $g2_embed_location =
        g2(GalleryCoreApi::getPluginParameter("module", "rewrite", "modrewrite.embeddedLocation"));

      if (empty($g2_embed_location)) {
        $g2_embed_location =
          g2(GalleryCoreApi::getPluginParameter("module", "rewrite", "modrewrite.galleryLocation"));
        g2(GalleryCoreApi::setPluginParameter("module", "rewrite", "modrewrite.embeddedLocation",
                                              $g2_embed_location));
        g2($gallery->getStorage()->checkPoint());
      }

      if ($g2_embed_location) {
        self::$g2_base_url = $g2_embed_location;
      } else {
        self::$g2_base_url = $GLOBALS["gallery"]->getUrlGenerator()->generateUrl(
          array(),
          array("forceSessionId" => false,
                "htmlEntities" => false,
                "urlEncode" => false,
                "useAuthToken" => false));
      }
    } catch (ErrorException $e) {
      Kohana_Log::add("error", $e->getMessage() . "\n" . $e->getTraceAsString());
      return "broken";
    }

    return "ok";
  }

  /**
   * Return the version of Gallery 2 (eg "2.3")
   */
  static function version() {
    $core = g2(GalleryCoreApi::loadPlugin("module", "core"));
    $versions = $core->getInstalledVersions();
    return $versions["gallery"];
  }

  /**
   * Return true if the given Gallery 2 module is active.
   */
  static function g2_module_active($module) {
    static $plugin_list;
    if (!$plugin_list) {
      $plugin_list = g2(GalleryCoreApi::fetchPluginList("module"));
    }

    return @$plugin_list[$module]["active"];
  }

  /**
   * Return a set of statistics about the number of users, groups, albums, photos, movies and
   * comments available for import from the Gallery 2 instance.
   */
  static function g2_stats() {
    global $gallery;
    $root_album_id = g2(GalleryCoreApi::getDefaultAlbumId());
    $stats["users"] = g2(GalleryCoreApi::fetchUserCount());
    $stats["groups"] = g2(GalleryCoreApi::fetchGroupCount());
    $stats["albums"] = g2(GalleryCoreApi::fetchItemIdCount("GalleryAlbumItem"));
    $stats["photos"] = g2(GalleryCoreApi::fetchItemIdCount("GalleryPhotoItem"));
    $stats["movies"] = g2(GalleryCoreApi::fetchItemIdCount("GalleryMovieItem"));

    if (g2_import::g2_module_active("comment") && module::is_active("comment")) {
      GalleryCoreApi::requireOnce("modules/comment/classes/GalleryCommentHelper.class");
      list (, $stats["comments"]) = g2(GalleryCommentHelper::fetchAllComments($root_album_id, 1));
    } else {
      $stats["comments"] = 0;
    }

    if (g2_import::g2_module_active("tags") && module::is_active("tag")) {
      $result =
        g2($gallery->search("SELECT COUNT(DISTINCT([TagItemMap::itemId])) FROM [TagItemMap]"))
        ->nextResult();
      $stats["tags"] = $result[0];
    } else {
      $stats["tags"] = 0;
    }

    return $stats;
  }

  /**
   * Return a set of statistics about the number of users, groups, albums, photos, movies and
   * comments already imported into the Gallery 3 instance.
   */
  static function g3_stats() {
    $g3_stats = array(
      "album" => 0, "comment" => 0, "item" => 0, "user" => 0, "group" => 0, "tag" => 0);
    foreach (db::build()
             ->select("resource_type")
             ->select(array("C" => 'COUNT("*")'))
             ->from("g2_maps")
             ->where("resource_type", "IN", array("album", "comment", "item", "user", "group"))
             ->group_by("resource_type")
             ->execute() as $row) {
      $g3_stats[$row->resource_type] = $row->C;
    }
    return $g3_stats;
  }

  /**
   * Import a single group.
   */
  static function import_group(&$queue) {
    $messages = array();
    $g2_group_id = array_shift($queue);
    if (self::map($g2_group_id)) {
      return;
    }

    try {
      $g2_group = g2(GalleryCoreApi::loadEntitiesById($g2_group_id));
    } catch (Exception $e) {
      throw new G2_Import_Exception(
          t("Failed to import Gallery 2 group with id: %id,",
            array("id" => $g2_group_id)),
          $e);
    }

    switch ($g2_group->getGroupType()) {
    case GROUP_NORMAL:
      try {
        $group = identity::create_group($g2_group->getGroupName());
        $messages[] = t("Group '%name' was imported",
                        array("name" => $g2_group->getGroupname()));
      } catch (Exception $e) {
        // Did it fail because of a duplicate group name?
        $group = identity::lookup_group_by_name($g2_group->getGroupname());
        if ($group) {
          $messages[] = t("Group '%name' was mapped to the existing group group of the same name.",
                          array("name" => $g2_group->getGroupname()));
        } else {
          throw new G2_Import_Exception(
              t("Failed to import group '%name'",
                array("name" => $g2_group->getGroupname())),
              $e);
        }
      }

      break;

    case GROUP_ALL_USERS:
      $group = identity::registered_users();
      $messages[] = t("Group 'Registered' was converted to '%name'", array("name" => $group->name));
      break;

    case GROUP_SITE_ADMINS:
      $messages[] = t("Group 'Admin' does not exist in Gallery 3, skipping");
      break;  // This is not a group in G3

    case GROUP_EVERYBODY:
      $group = identity::everybody();
      $messages[] = t("Group 'Everybody' was converted to '%name'", array("name" => $group->name));
      break;
    }

    if (isset($group)) {
      self::set_map($g2_group->getId(), $group->id, "group");
    }

    return $messages;
  }

  /**
   * Import a single user.
   */
  static function import_user(&$queue) {
    $messages = array();
    $g2_user_id = array_shift($queue);
    if (self::map($g2_user_id)) {
      return t("User with id: %id already imported, skipping",
               array("id" => $g2_user_id));
    }

    if (g2(GalleryCoreApi::isAnonymousUser($g2_user_id))) {
      self::set_map($g2_user_id, identity::guest()->id, "group");
      return t("Skipping anonymous user");
    }

    $g2_admin_group_id =
      g2(GalleryCoreApi::getPluginParameter("module", "core", "id.adminGroup"));
    try {
      $g2_user = g2(GalleryCoreApi::loadEntitiesById($g2_user_id));
    } catch (Exception $e) {
      throw new G2_Import_Exception(
          t("Failed to import Gallery 2 user with id: %id\n%exception",
            array("id" => $g2_user_id, "exception" => (string)$e)),
          $e);
    }
    $g2_groups = g2(GalleryCoreApi::fetchGroupsForUser($g2_user->getId()));

    $user = identity::lookup_user_by_name($g2_user->getUsername());
    if ($user) {
      $messages[] = t("Loaded existing user: '%name'.", array("name" => $user->name));
    } else {
      $email = $g2_user->getEmail();
      if (empty($email) || !valid::email($email)) {
        $email = "unknown@unknown.com";
      }
      try {
        $user = identity::create_user($g2_user->getUserName(), $g2_user->getFullName(),
                                      // Note: The API expects a password in cleartext.
                                      // Just use the hashed password as an unpredictable
                                      // value here. The user will have to reset the password.
                                      $g2_user->getHashedPassword(), $email);
      } catch (Exception $e) {
        throw new G2_Import_Exception(
          t("Failed to create user: '%name' (id: %id)",
            array("name" => $g2_user->getUserName(), "id" => $g2_user_id)),
          $e, $messages);
      }
      if (class_exists("User_Model") && $user instanceof User_Model) {
        // This will work if G2's password is a PasswordHash password as well.
        $user->hashed_password = $g2_user->getHashedPassword();
      }
      $messages[] = t("Created user: '%name'.", array("name" => $user->name));
      if ($email == "unknown@unknown.com") {
        $messages[] = t("Fixed invalid email (was '%invalid_email')",
                        array("invalid_email" => $g2_user->getEmail()));
      }
    }

    $user->locale = $g2_user->getLanguage();
    foreach ($g2_groups as $g2_group_id => $g2_group_name) {
      if ($g2_group_id == $g2_admin_group_id) {
        $user->admin = true;
        $messages[] = t("Added 'admin' flag to user");
      } else {
        $group = identity::lookup_group(self::map($g2_group_id));
        $user->add($group);
        $messages[] = t("Added user to group '%group'.", array("group" => $group->name));
      }
    }

    try {
      $user->save();
      self::set_map($g2_user->getId(), $user->id, "user");
    } catch (Exception $e) {
      throw new G2_Import_Exception(
          t("Failed to create user: '%name'", array("name" => $user->name)),
          $e, $messages);
    }

    return $messages;
  }

  /**
   * Import a single album.
   */
  static function import_album(&$queue) {
    // The queue is a set of nested associative arrays where the key is the album id and the
    // value is an array of similar arrays.  We'll do a breadth first tree traversal using the
    // queue to keep our state.  Doing it breadth first means that the parent will be created by
    // the time we get to the child.

    // Dequeue the current album and enqueue its children
    list($g2_album_id, $children) = each($queue);
    unset($queue[$g2_album_id]);
    foreach ($children as $key => $value) {
      $queue[$key] = $value;
    }

    if (self::map($g2_album_id)) {
      return;
    }

    try {
      // Load the G2 album item, and figure out its parent in G3.
      $g2_album = g2(GalleryCoreApi::loadEntitiesById($g2_album_id));
    } catch (Exception $e) {
      return t("Failed to load Gallery 2 album with id: %id\n%exception",
               array("id" => $g2_album_id, "exception" => (string)$e));
    }

    if ($g2_album->getParentId() == null) {
      $album = item::root();
    } else {
      $parent_album = ORM::factory("item", self::map($g2_album->getParentId()));

      $album = ORM::factory("item");
      $album->type = "album";
      $album->parent_id = self::map($g2_album->getParentId());

      g2_import::set_album_values($album, $g2_album);

      try {
        $album->save();
      } catch (Exception $e) {
        throw new G2_Import_Exception(
            t("Failed to import Gallery 2 album with id %id and name %name.",
              array("id" => $g2_album_id, "name" => $album->name)),
            $e);
      }

      self::import_keywords_as_tags($g2_album->getKeywords(), $album);
    }

    self::set_map(
      $g2_album_id, $album->id,
      "album",
      self::g2_url(array("view" => "core.ShowItem", "itemId" => $g2_album->getId())));

    self::_import_permissions($g2_album, $album);
  }

  /**
   * Transfer over all the values from a G2 album to a G3 album.
   */
  static function set_album_values($album, $g2_album) {
    $album->name = $g2_album->getPathComponent();
    $album->title = self::_decode_html_special_chars($g2_album->getTitle());
    $album->title or $album->title = $album->name;
    $album->description = self::_decode_html_special_chars(self::extract_description($g2_album));
    $album->owner_id = self::map($g2_album->getOwnerId());
    try {
      $album->view_count = (int) g2(GalleryCoreApi::fetchItemViewCount($g2_album_id));
    } catch (Exception $e) {
      // @todo log
      $album->view_count = 0;
    }
    $album->created = $g2_album->getCreationTimestamp();

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
      1 => "ASC",
      ORDER_ASCENDING => "ASC",
      ORDER_DESCENDING => "DESC");
    // G2 sorts can either be <sort> or <presort>|<sort>.  Right now we can't
    // map presorts so ignore them.
    $g2_order = explode("|", $g2_album->getOrderBy() . "");
    $g2_order = end($g2_order);
    if (empty($g2_order)) {
      $g2_order = g2(GalleryCoreApi::getPluginParameter('module', 'core', 'default.orderBy'));
    }
    $g2_order_direction = explode("|", $g2_album->getOrderDirection() . "");
    $g2_order_direction = $g2_order_direction[0];
    if (empty($g2_order_direction)) {
      $g2_order_direction =
        g2(GalleryCoreApi::getPluginParameter('module', 'core', 'default.orderDirection'));
    }
    if (array_key_exists($g2_order, $order_map)) {
      $album->sort_column = $order_map[$g2_order];
      $album->sort_order = $direction_map[$g2_order_direction];
    }
  }

  /**
   * Set the highlight properly for a single album
   */
  static function set_album_highlight(&$queue) {
    // Dequeue the current album and enqueue its children
    list($g2_album_id, $children) = each($queue);
    unset($queue[$g2_album_id]);
    if (!empty($children)) {
      foreach ($children as $key => $value) {
        $queue[$key] = $value;
      }
    }

    $messages = array();
    $g3_album_id = self::map($g2_album_id);
    if (!$g3_album_id) {
      return t("Album with id: %id not imported", array("id" => $g3_album_id));
    }

    $table = g2(GalleryCoreApi::fetchThumbnailsByItemIds(array($g2_album_id)));
    if (isset($table[$g2_album_id])) {
      // Backtrack the source id to an item
      $orig_g2_source = $g2_source = $table[$g2_album_id];
      while (GalleryUtilities::isA($g2_source, "GalleryDerivative")) {
        $g2_source = g2(GalleryCoreApi::loadEntitiesById($g2_source->getDerivativeSourceId()));
      }
      $item_id = self::map($g2_source->getId());
      if ($item_id) {
        $item = ORM::factory("item", $item_id);
        $g3_album = ORM::factory("item", $g3_album_id);
        $g3_album->album_cover_item_id = $item->id;
        $g3_album->thumb_dirty = 1;
        try {
          $g3_album->view_count = (int) g2(GalleryCoreApi::fetchItemViewCount($g2_album_id));
        } catch (Exception $e) {
          $g3_album->view_count = 0;
        }
        try {
          $g3_album->save();
          graphics::generate($g3_album);
        } catch (Exception $e) {
          return (string) new G2_Import_Exception(
              t("Failed to generate an album highlight for album '%name'.",
                array("name" => $g3_album->name)),
              $e);
        }

        self::set_map(
          $orig_g2_source->getId(), $g3_album->id,
          "thumbnail",
          self::g2_url(array("view" => "core.DownloadItem", "itemId" => $orig_g2_source->getId())));
      }
    }
  }

  /**
   * Import a single photo or movie.
   */
  static function import_item(&$queue) {
    $g2_item_id = array_shift($queue);

    if (self::map($g2_item_id)) {
      return;
    }

    try {
      self::$current_g2_item = $g2_item = g2(GalleryCoreApi::loadEntitiesById($g2_item_id));
      $g2_path = g2($g2_item->fetchPath());
    } catch (Exception $e) {
      return t("Failed to import Gallery 2 item with id: %id\n%exception",
               array("id" => $g2_item_id, "exception" => (string)$e));
    }

    $parent = ORM::factory("item", self::map($g2_item->getParentId()));

    $g2_type = $g2_item->getEntityType();
    $corrupt = 0;
    if (!file_exists($g2_path)) {
      // If the Gallery 2 source image isn't available, this operation is going to fail.  That can
      // happen in cases where there's corruption in the source Gallery 2.  In that case, fall
      // back on using a broken image.  It's important that we import *something* otherwise
      // anything that refers to this item in Gallery 2 will have a dangling pointer in Gallery 3
      //
      // Note that this will change movies to be photos, if there's a broken movie.  Hopefully
      // this case is rare enough that we don't need to take any heroic action here.
      g2_import::log(
        t("%path missing in import; replacing it with a placeholder", array("path" => $g2_path)));
      $g2_path = MODPATH . "g2_import/data/broken-image.gif";
      $g2_type = "GalleryPhotoItem";
      $corrupt = 1;
    }

    $messages = array();
    switch ($g2_type) {
    case "GalleryPhotoItem":
      if (!in_array($g2_item->getMimeType(), array("image/jpeg", "image/gif", "image/png"))) {
        Kohana_Log::add("alert", "$g2_path is an unsupported image type; using a placeholder gif");
        $messages[] = t("'%path' is an unsupported image type, using a placeholder",
                        array("path" => $g2_path));
        $g2_path = MODPATH . "g2_import/data/broken-image.gif";
        $corrupt = 1;
      }
      try {
        $item = ORM::factory("item");
        $item->type = "photo";
        $item->parent_id = $parent->id;
        $item->set_data_file($g2_path);
        $item->name = $g2_item->getPathComponent();
        $item->title = self::_decode_html_special_chars($g2_item->getTitle());
        $item->title or $item->title = $item->name;
        $item->description = self::_decode_html_special_chars(self::extract_description($g2_item));
        $item->owner_id = self::map($g2_item->getOwnerId());
        $item->save();

        // If the item has a preferred derivative with a rotation, then rotate this image
        // accordingly.  Should we obey scale rules as well?  I vote no because rotation is less
        // destructive -- you lose too much data from scaling.
        $g2_preferred = g2(GalleryCoreApi::fetchPreferredSource($g2_item));
        if ($g2_preferred && $g2_preferred instanceof GalleryDerivative) {
          if (preg_match("/rotate\|(-?\d+)/", $g2_preferred->getDerivativeOperations(), $matches)) {
            $tmpfile = tempnam(TMPPATH, "rotate");
            gallery_graphics::rotate($item->file_path(), $tmpfile, array("degrees" => $matches[1]), $item);
            $item->set_data_file($tmpfile);
            $item->save();
            unlink($tmpfile);
          }
        }
      } catch (Exception $e) {
        $exception_info = (string) new G2_Import_Exception(
            t("Corrupt image '%path'", array("path" => $g2_path)),
            $e, $messages);
        Kohana_Log::add("alert", "Corrupt image $g2_path\n" . $exception_info);
        $messages[] = $exception_info;
        $corrupt = 1;
        $item = null;
      }
      break;

    case "GalleryMovieItem":
      // @todo we should transcode other types into FLV
      if (in_array($g2_item->getMimeType(), array("video/mp4", "video/x-flv"))) {
        try {
          $item = ORM::factory("item");
          $item->type = "movie";
          $item->parent_id = $parent->id;
          $item->set_data_file($g2_path);
          $item->name = $g2_item->getPathComponent();
          $item->title = self::_decode_html_special_chars($g2_item->getTitle());
          $item->title or $item->title = $item->name;
          $item->description = self::_decode_html_special_chars(self::extract_description($g2_item));
          $item->owner_id = self::map($g2_item->getOwnerId());
          $item->save();
        } catch (Exception $e) {
          $exception_info = (string) new G2_Import_Exception(
              t("Corrupt movie '%path'", array("path" => $g2_path)),
              $e, $messages);
          Kohana_Log::add("alert", "Corrupt movie $g2_path\n" . $exception_info);
          $messages[] = $exception_info;
          $corrupt = 1;
          $item = null;
        }
      } else {
        Kohana_Log::add("alert", "$g2_path is an unsupported movie type");
        $messages[] = t("'%path' is an unsupported movie type", array("path" => $g2_path));
        $corrupt = 1;
      }

      break;

    default:
      // Ignore
      break;
    }

    if (!empty($item)) {
      self::import_keywords_as_tags($g2_item->getKeywords(), $item);
    }

    $g2_item_url = self::g2_url(array("view" => "core.ShowItem", "itemId" => $g2_item->getId()));
    if (isset($item)) {
      try {
        $item->view_count = (int) g2(GalleryCoreApi::fetchItemViewCount($g2_item_id));
      } catch (Exception $e) {
        $view_count = 1;
      }
      $item->save();

      self::set_map($g2_item_id, $item->id, "item", $g2_item_url);

      self::set_map($g2_item_id, $item->id, "file",
                    self::g2_url(array("view" => "core.DownloadItem", "itemId" => $g2_item_id)));

      $derivatives = g2(GalleryCoreApi::fetchDerivativesByItemIds(array($g2_item_id)));
      if (!empty($derivatives[$g2_item_id])) {
        foreach ($derivatives[$g2_item_id] as $derivative) {
          switch ($derivative->getDerivativeType()) {
          case DERIVATIVE_TYPE_IMAGE_THUMBNAIL: $resource_type = "thumbnail"; break;
          case DERIVATIVE_TYPE_IMAGE_RESIZE:    $resource_type = "resize"; break;
          case DERIVATIVE_TYPE_IMAGE_PREFERRED: $resource_type = "full"; break;
          }

          self::set_map(
            $derivative->getId(), $item->id,
            $resource_type,
            self::g2_url(array("view" => "core.DownloadItem", "itemId" => $derivative->getId())));
        }
      }
    }

    if ($corrupt) {
      if (!empty($item)) {
        $title = $g2_item->getTitle();
        $title or $title = $g2_item->getPathComponent();
        $messages[] =
          t("<a href=\"%g2_url\">%title</a> from Gallery 2 could not be processed; (imported as <a href=\"%g3_url\">%title</a>)",
            array("g2_url" => $g2_item_url,
                  "g3_url" => $item->url(),
                  "title" => $title));
      } else {
        $messages[] =
          t("<a href=\"%g2_url\">%title</a> from Gallery 2 could not be processed",
            array("g2_url" => $g2_item_url, "title" => $g2_item->getTitle()));
      }
    }

    self::$current_g2_item = null;
    return $messages;
  }

  /**
   * g2 encoded'&', '"', '<' and '>' as '&amp;', '&quot;', '&lt;' and '&gt;' respectively.
   * This function undoes that encoding.
   */
  private static function _decode_html_special_chars($value) {
    return str_replace(array("&amp;", "&quot;", "&lt;", "&gt;"),
                       array("&", "\"", "<", ">"), $value);
  }

  private static $_permission_map = array(
    "core.view" => "view",
    "core.viewSource" => "view_full",
    "core.edit" => "edit",
    "core.addDataItem" => "add",
    "core.addAlbumItem" => "add");

  /**
   * Imports G2 permissions, mapping G2's permission model to G3's
   * much simplified permissions.
   *
   *  - Ignores user permissions, G3 only supports group permissions.
   *  - Ignores item permissions, G3 only supports album permissions.
   *
   *  G2 permission   ->  G3 permission
   *  ---------------------------------
   *  core.view           view
   *  core.viewSource     view_full
   *  core.edit           edit
   *  core.addDataItem    add
   *  core.addAlbumItem   add
   *  core.viewResizes    <ignored>
   *  core.delete         <ignored>
   *  comment.*           <ignored>
   */
  private static function _import_permissions($g2_album, $g3_album) {
    // No need to do anything if this album has the same G2 ACL as its parent.
    if ($g2_album->getParentId() != null &&
        g2(GalleryCoreApi::fetchAccessListId($g2_album->getId())) ==
        g2(GalleryCoreApi::fetchAccessListId($g2_album->getParentId()))) {
      return;
    }

    $granted_permissions = self::_map_permissions($g2_album->getId());

    if ($g2_album->getParentId() == null) {
      // Compare to current permissions, and change them if necessary.
      $g3_parent_album = item::root();
    } else {
      $g3_parent_album = $g3_album->parent();
    }
    $granted_parent_permissions = array();
    $perm_ids = array_unique(array_values(self::$_permission_map));
    foreach (identity::groups() as $group) {
      $granted_parent_permissions[$group->id] = array();
      foreach ($perm_ids as $perm_id) {
        if (access::group_can($group, $perm_id, $g3_parent_album)) {
          $granted_parent_permissions[$group->id][$perm_id] = 1;
        }
      }
    }

    // Note: Only registering permissions if they're not the same as
    //       the inherited ones.
    foreach ($granted_permissions as $group_id => $permissions) {
      if (!isset($granted_parent_permissions[$group_id])) {
        foreach (array_keys($permissions) as $perm_id) {
          access::allow(identity::lookup_group($group_id), $perm_id, $g3_album);
        }
      } else if ($permissions != $granted_parent_permissions[$group_id]) {
        $parent_permissions = $granted_parent_permissions[$group_id];
        // @todo Probably worth caching the group instances.
        $group = identity::lookup_group($group_id);
        // Note: Cannot use array_diff_key.
        foreach (array_keys($permissions) as $perm_id) {
          if (!isset($parent_permissions[$perm_id])) {
            access::allow($group, $perm_id, $g3_album);
          }
        }
        foreach (array_keys($parent_permissions) as $perm_id) {
          if (!isset($permissions[$perm_id])) {
            access::deny($group, $perm_id, $g3_album);
          }
        }
      }
    }

    foreach ($granted_parent_permissions as $group_id => $parent_permissions) {
      if (isset($granted_permissions[$group_id])) {
        continue;  // handled above
      }
      $group = identity::lookup_group($group_id);
      foreach (array_keys($parent_permissions) as $perm_id) {
        access::deny($group, $perm_id, $g3_album);
      }
    }
  }

  /**
   * Loads all the granted group G2 permissions for a specific
   * album and returns an array with G3 groups ids and G3 permission ids.
   */
  private static function _map_permissions($g2_album_id) {
    $g2_permissions = g2(GalleryCoreApi::fetchAllPermissionsForItem($g2_album_id));
    $permissions = array();
    foreach ($g2_permissions as $entry) {
      // @todo Do something about user permissions? E.g. map G2's user albums
      //       to a user-specific group in G3?
      if (!isset($entry["groupId"])) {
        continue;
      }
      $g2_permission_id = $entry["permission"];
      if (!isset(self::$_permission_map[$g2_permission_id])) {
        continue;
      }
      $group_id = self::map($entry["groupId"]);
      if ($group_id == null) {
        // E.g. the G2 admin group isn't mapped.
        continue;
      }
      $permission_id = self::$_permission_map[$g2_permission_id];
      if (!isset($permissions[$group_id])) {
        $permissions[$group_id] = array();
      }
      $permissions[$group_id][$permission_id] = 1;
    }
    return $permissions;
  }

  /**
   * Import a single comment.
   */
  static function import_comment(&$queue) {
    $g2_comment_id = array_shift($queue);

    try {
      $g2_comment = g2(GalleryCoreApi::loadEntitiesById($g2_comment_id));
    } catch (Exception $e) {
      return t("Failed to load Gallery 2 comment with id: %id\%exception",
               array("id" => $g2_comment_id, "exception" => (string)$e));
    }

    if ($id = self::map($g2_comment->getId())) {
      if (ORM::factory("comment", $id)->loaded()) {
        // Already imported and still exists
        return;
      }
      // This comment was already imported, but now it no longer exists.  Import it again, per
      // ticket #1736.
    }

    $item_id = self::map($g2_comment->getParentId());
    if (empty($item_id)) {
      // Item was not mapped.
      return;
    }

    $text = join("\n", array($g2_comment->getSubject(), $g2_comment->getComment()));
    $text = html_entity_decode($text);

    // Just import the fields we know about.  Do this outside of the comment API for now so that
    // we don't trigger spam filtering events
    $comment = ORM::factory("comment");
    $comment->author_id = self::map($g2_comment->getCommenterId());
    $comment->guest_name = "";
    if ($comment->author_id == identity::guest()->id) {
      $comment->guest_name = $g2_comment->getAuthor();
      $comment->guest_name or $comment->guest_name = (string) t("Anonymous coward");
      $comment->guest_email = "unknown@nobody.com";
    }
    $comment->item_id = $item_id;
    $comment->text = self::_transform_bbcode($text);
    $comment->state = "published";
    $comment->server_http_host = $g2_comment->getHost();
    try {
      $comment->save();
    } catch (Exception $e) {
      return (string) new G2_Import_Exception(
          t("Failed to import comment with id: %id.",
            array("id" => $g2_comment_id)),
          $e);
    }

    self::set_map($g2_comment->getId(), $comment->id, "comment");

    // Backdate the creation date.  We can't do this at creation time because
    // Comment_Model::save() will override it.  Leave the updated date alone
    // so that if the comments get marked as spam, they don't immediately get
    // flushed (see ticket #1736)
    db::update("comments")
      ->set("created", $g2_comment->getDate())
      ->where("id", "=", $comment->id)
      ->execute();
  }

  /**
   * Import all the tags for a single item
   */
  static function import_tags_for_item(&$queue) {
    if (!module::is_active("tag")) {
      return t("Gallery 3 tag module is inactive, no tags will be imported");
    }

    GalleryCoreApi::requireOnce("modules/tags/classes/TagsHelper.class");
    $g2_item_id = array_shift($queue);
    $g3_item = ORM::factory("item", self::map($g2_item_id));
    if (!$g3_item->loaded()) {
      return;
    }

    try {
      $tag_names = array_values(g2(TagsHelper::getTagsByItemId($g2_item_id)));
    } catch (Exception $e) {
      return t("Failed to import Gallery 2 tags for item with id: %id\n%exception",
               array("id" => $g2_item_id, "exception" => (string)$e));
    }

    foreach ($tag_names as $tag_name) {
      tag::add($g3_item, $tag_name);
    }

    // Tag operations are idempotent so we don't need to map them.  Which is good because we don't
    // have an id for each individual tag mapping anyway so it'd be hard to set up the mapping.
  }

  static function import_keywords_as_tags($keywords, $item) {
    // Keywords in G2 are free form.  So we don't know what our user used as a separator.  Try to
    // be smart about it.  If we see a comma or a semicolon, expect the keywords to be separated
    // by that delimeter.  Otherwise, use space as the delimiter.
    if (strpos($keywords, ";")) {
      $delim = ";";
    } else if (strpos($keywords, ",")) {
      $delim = ",";
    } else {
      $delim = " ";
    }

    foreach (preg_split("/$delim/", $keywords) as $keyword) {
      $keyword = trim($keyword);
      if ($keyword) {
        tag::add($item, $keyword);
      }
    }
  }

  /**
   * If the thumbnails and resizes created for the Gallery 2 photo match the dimensions of the
   * ones we expect to create for Gallery 3, then copy the files over instead of recreating them.
   */
  static function copy_matching_thumbnails_and_resizes($item) {
    // We only operate on items that are being imported
    if (empty(self::$current_g2_item)) {
      return;
    }

    // Precaution: if the Gallery 2 item was watermarked, or we have the Gallery 3 watermark module
    // active then we'd have to do something a lot more sophisticated here.  For now, just skip
    // this step in those cases.
    // @todo we should probably use an API here, eventually.
    if (module::is_active("watermark") && module::get_var("watermark", "name")) {
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

    $target_thumb_size = module::get_var("gallery", "thumb_size");
    $target_resize_size = module::get_var("gallery", "resize_size");
    if (!empty($derivatives[$g2_item_id])) {
      foreach ($derivatives[$g2_item_id] as $derivative) {
        if ($derivative->getPostFilterOperations()) {
          // Let's assume for now that this is a watermark operation, which we can't handle.
          continue;
        }

        if ($derivative->getDerivativeType() == DERIVATIVE_TYPE_IMAGE_THUMBNAIL &&
            $item->thumb_dirty &&
            ($derivative->getWidth() == $target_thumb_size ||
             $derivative->getHeight() == $target_thumb_size)) {
          if (@copy(g2($derivative->fetchPath()), $item->thumb_path())) {
            $item->thumb_height = $derivative->getHeight();
            $item->thumb_width = $derivative->getWidth();
            $item->thumb_dirty = 0;
          }
        }

        if ($derivative->getDerivativeType() == DERIVATIVE_TYPE_IMAGE_RESIZE &&
            $item->resize_dirty &&
            ($derivative->getWidth() == $target_resize_size ||
             $derivative->getHeight() == $target_resize_size)) {
          if (@copy(g2($derivative->fetchPath()), $item->resize_path())) {
            $item->resize_height = $derivative->getHeight();
            $item->resize_width = $derivative->getWidth();
            $item->resize_dirty = 0;
          }
        }
      }
    }
    try {
      $item->save();
    } catch (Exception $e) {
      return (string) new G2_Import_Exception(
          t("Failed to copy thumbnails and resizes for item '%name' (Gallery 2 id: %id)",
            array("name" => $item->name, "id" => $g2_item_id)),
          $e);
    }
  }

  /**
   * Figure out the most common resize and thumb sizes in Gallery 2 so that we can tell the admin
   * what theme settings to set to make the import go faster.  If we match up the sizes then we
   * can just copy over derivatives instead of running graphics toolkit operations.
   */
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
        "  AND [GalleryDerivativeImage::height] > [GalleryDerivativeImage::width] " .
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

  /**
   * Sensibly concatenate Gallery 2 summary and descriptions into a single field.
   */
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
    return self::_transform_bbcode($description);
  }

  static $bbcode_mappings = array(
    "#\\[b\\](.*?)\\[/b\\]#" => "<b>$1</b>",
    "#\\[i\\](.*?)\\[/i\\]#" => "<i>$1</i>",
    "#\\[u\\](.*?)\\[/u\\]#" => "<u>$1</u>",
    "#\\[s\\](.*?)\\[/s\\]#" => "<s>$1</s>",
    "#\\[url\\](.*?)\[/url\\]#" => "<a href=\"$1\">$1</a>",
    "#\\[url=(.*?)\\](.*?)\[/url\\]#" => "<a href=\"$1\">$2</a>",
    "#\\[img\\](.*?)\\[/img\\]#" => "<img src=\"$1\"/>",
    "#\\[quote\\](.*?)\\[/quote\\]#" => "<blockquote><p>$1</p></blockquote>",
    "#\\[code\\](.*?)\\[/code\\]#" => "<pre>$1</pre>",
    "#\\[size=([^\\[]*)\\]([^\\[]*)\\[/size\\]#" => "<font size=\"$1\">$2</font>",
    "#\\[color=([^\\[]*)\\]([^\\[]*)\\[/color\\]#" => "<font color=\"$1\">$2/font>",
    "#\\[ul\\](.*?)\\/ul\\]#" => "<ul>$1</ul>",
    "#\\[li\\](.*?)\\[/li\\]#" => "<li>$1</li>",
  );
  private static function _transform_bbcode($text) {
    if (strpos($text, "[") !== false) {
      $text = preg_replace(array_keys(self::$bbcode_mappings), array_values(self::$bbcode_mappings),
                           $text);
    }
    return $text;
  }

  /**
   * Get a set of photo and movie ids from Gallery 2 greater than $min_id.  We use this to get the
   * next chunk of photos/movies to import.
   */
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

  /**
   * Get a set of comment ids from Gallery 2 greater than $min_id.  We use this to get the
   * next chunk of comments to import.
   */
  static function get_comment_ids($min_id) {
    global $gallery;

    $ids = array();
    $results = g2($gallery->search(
      "SELECT [GalleryComment::id] " .
      "FROM [GalleryComment] " .
      "WHERE [GalleryComment::publishStatus] = 0 " . // 0 == COMMENT_PUBLISH_STATUS_PUBLISHED
      "AND [GalleryComment::id] > ? " .
      "ORDER BY [GalleryComment::id] ASC",
      array($min_id),
      array("limit" => array("count" => 100))));
    while ($result = $results->nextResult()) {
      $ids[] = $result[0];
    }
    return $ids;
  }

  /**
   * Get a set of comment ids from Gallery 2 greater than $min_id.  We use this to get the
   * next chunk of comments to import.
   */
  static function get_tag_item_ids($min_id) {
    global $gallery;

    $ids = array();
    $results = g2($gallery->search(
      "SELECT DISTINCT([TagItemMap::itemId]) FROM [TagItemMap] " .
      "WHERE [TagItemMap::itemId] > ? " .
      "ORDER BY [TagItemMap::itemId] ASC",
      array($min_id),
      array("limit" => array("count" => 100))));
    while ($result = $results->nextResult()) {
      $ids[] = $result[0];
    }
    return $ids;
  }

  /**
   * Get a set of user ids from Gallery 2 greater than $min_id.  We use this to get the
   * next chunk of users to import.
   */
  static function get_user_ids($min_id) {
    global $gallery;

    $ids = array();
    $results = g2($gallery->search(
      "SELECT [GalleryUser::id] " .
      "FROM [GalleryUser] " .
      "WHERE [GalleryUser::id] > ? " .
      "ORDER BY [GalleryUser::id] ASC",
      array($min_id),
      array("limit" => array("count" => 100))));
    while ($result = $results->nextResult()) {
      $ids[] = $result[0];
    }
    return $ids;
  }

  /**
   * Get a set of group ids from Gallery 2 greater than $min_id.  We use this to get the
   * next chunk of groups to import.
   */
  static function get_group_ids($min_id) {
    global $gallery;

    $ids = array();
    $results = g2($gallery->search(
      "SELECT [GalleryGroup::id] " .
      "FROM [GalleryGroup] " .
      "WHERE [GalleryGroup::id] > ? " .
      "ORDER BY [GalleryGroup::id] ASC",
      array($min_id),
      array("limit" => array("count" => 100))));
    while ($result = $results->nextResult()) {
      $ids[] = $result[0];
    }
    return $ids;
  }

  /**
   * Look in our map to find the corresponding Gallery 3 id for the given Gallery 2 id.
   */
  static function map($g2_id) {
    if (!array_key_exists($g2_id, self::$map)) {
      $g2_map = ORM::factory("g2_map")->where("g2_id", "=", $g2_id)->find();
      self::$map[$g2_id] = $g2_map->loaded() ? $g2_map->g3_id : null;
    }

    return self::$map[$g2_id];
  }

  /**
   * Associate a Gallery 2 id with a Gallery 3 item id.
   */
  static function set_map($g2_id, $g3_id, $resource_type, $g2_url=null) {
    self::clear_map($g2_id, $resource_type);
    $g2_map = ORM::factory("g2_map");
    $g2_map->g3_id = $g3_id;
    $g2_map->g2_id = $g2_id;
    $g2_map->resource_type = $resource_type;

    if (strpos($g2_url, self::$g2_base_url) === 0) {
      $g2_url = substr($g2_url, strlen(self::$g2_base_url));
    }

    $g2_map->g2_url = $g2_url;
    $g2_map->save();
    self::$map[$g2_id] = $g3_id;
  }

  /**
   * Remove all map entries associated with the given Gallery 2 id.
   */
  static function clear_map($g2_id, $resource_type) {
    db::build()
      ->delete("g2_maps")
      ->where("g2_id", "=", $g2_id)
      ->where("resource_type", "=", $resource_type)
      ->execute();
  }

  static function log($msg) {
    message::warning($msg);
    Kohana_Log::add("alert", $msg);
  }

  static function g2_url($params) {
    global $gallery;
    return $gallery->getUrlGenerator()->generateUrl(
      $params,
      array("forceSessionId" => false,
            "htmlEntities" => false,
            "urlEncode" => false,
            "useAuthToken" => false));
  }

  static function lower_error_reporting() {
    // Gallery 2 was not designed to run in E_STRICT mode and will barf out errors.  So dial down
    // the error reporting when we make G2 calls.
    self::$error_reporting = error_reporting(error_reporting() & ~E_STRICT);
  }

  static function restore_error_reporting() {
    error_reporting(self::$error_reporting);
  }
}

/**
 * Wrapper around Gallery 2 calls.  We expect the first response to be a GalleryStatus object.  If
 * it's not null, then throw an exception.  Strip the GalleryStatus object out of the result and
 * if there's only an array of 1 return value, turn it into a scalar.  This allows us to simplify
 * this pattern:
 *   list ($ret, $foo) = GalleryCoreApi::someCall();
 *   if ($ret) { handle_error(); }
 *
 * to:
 *   $foo = g2(GalleryCoreApi::someCall());
 */
function g2() {
  $args = func_get_arg(0);
  $ret = is_array($args) ? array_shift($args) : $args;
  if ($ret) {
    Kohana_Log::add("error", "Gallery 2 call failed with: " . $ret->getAsText());
    throw new Exception("@todo G2_FUNCTION_FAILED");
  }
  if (count($args) == 1) {
    return $args[0];
  } else {
    return $args;
  }
}
