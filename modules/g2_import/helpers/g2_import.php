<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
    $mod_path = VARPATH . "modules/g2_import/" . md5($embed_path);
    if (file_exists($mod_path)) {
      dir::unlink($mod_path);
    }
    return file_exists($embed_path) && g2_import::init_embed($embed_path);
  }

  /**
   * Initialize the embedded Gallery 2 instance.  Call this before any other Gallery 2 calls.
   */
  static function init_embed($embed_path) {
    if (!is_file($embed_path)) {
      return false;
    }

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
          array_merge(array("<?php defined(\"SYSPATH\") or die(\"No direct script access.\") ?>\n"),
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
          array_merge(array("<?php defined(\"SYSPATH\") or die(\"No direct script access.\") ?>\n"),
                      file("$base_dir/main.php"))));

      file_put_contents(
        "$mod_path/bootstrap.inc",
        str_replace(
          array("require_once(dirname(__FILE__) . '/modules/core/classes/Gallery.class');",
                "require_once(dirname(__FILE__) . '/modules/core/classes/GalleryDataCache.class');",
                "define('GALLERY_CONFIG_DIR', dirname(__FILE__));",
                "\$gallery =& new Gallery();",
                "\$GLOBALS['gallery'] =& new Gallery();",
                "\$gallery = new Gallery();"),
          array("require_once(dirname(__FILE__) . '/Gallery.class');",
                "require_once('$base_dir/modules/core/classes/GalleryDataCache.class');",
                "define('GALLERY_CONFIG_DIR', '$config_dir');",
                "\$gallery =& new G2_Gallery();",
                "\$GLOBALS['gallery'] =& new G2_Gallery();",
                "\$gallery = new G2_Gallery();"),
          array_merge(array("<?php defined(\"SYSPATH\") or die(\"No direct script access.\") ?>\n"),
                      file("$base_dir/bootstrap.inc"))));

      file_put_contents(
        "$mod_path/Gallery.class",
        str_replace(
          array("class Gallery",
                "function Gallery"),
          array("class G2_Gallery",
                "function G2_Gallery"),
          array_merge(array("<?php defined(\"SYSPATH\") or die(\"No direct script access.\") ?>\n"),
                      file("$base_dir/modules/core/classes/Gallery.class"))));
    }

    require("$mod_path/embed.php");
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
  static function stats() {
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
   * Import a single group.
   */
  static function import_group(&$queue) {
    $g2_group_id = array_shift($queue);
    if (self::map($g2_group_id)) {
      return t("Group with id: %id already imported, skipping", array("id" => $g2_group_id));
    }

    try {
      $g2_group = g2(GalleryCoreApi::loadEntitiesById($g2_group_id));
    } catch (Exception $e) {
      return t("Failed to import Gallery 2 group with id: %id\n%exception",
               array("id" => $g2_group_id, "exception" => $e->__toString()));
    }

    switch ($g2_group->getGroupType()) {
    case GROUP_NORMAL:
      try {
        $group = group::create($g2_group->getGroupName());
      } catch (Exception $e) {
        // @todo For now we assume this is a "duplicate group" exception
        $group = group::lookup_by_name($g2_group->getGroupname());
      }
      $message = t("Group '%name' was imported", array("name" => $g2_group->getGroupname()));
      break;

    case GROUP_ALL_USERS:
      $group = group::registered_users();
      $message = t("Group 'Registered' was converted to '%name'", array("name" => $group->name));
      break;

    case GROUP_SITE_ADMINS:
      $message = t("Group 'Admin' does not exist in Gallery 3, skipping");
      break;  // This is not a group in G3

    case GROUP_EVERYBODY:
      $group = group::everybody();
      $message = t("Group 'Everybody' was converted to '%name'", array("name" => $group->name));
      break;
    }

    if (isset($group)) {
      self::set_map($g2_group->getId(), $group->id);
    }

    return $message;
  }

  /**
   * Import a single user.
   */
  static function import_user(&$queue) {
    $g2_user_id = array_shift($queue);
    if (self::map($g2_user_id)) {
      return t("User with id: %id already imported, skipping", array("id" => $g2_user_id));
    }

    if (g2(GalleryCoreApi::isAnonymousUser($g2_user_id))) {
      self::set_map($g2_user_id, user::guest()->id);
      return t("Skipping Anonymous User");
    }

    $g2_admin_group_id =
      g2(GalleryCoreApi::getPluginParameter("module", "core", "id.adminGroup"));
    try {
      $g2_user = g2(GalleryCoreApi::loadEntitiesById($g2_user_id));
    } catch (Exception $e) {
      return t("Failed to import Gallery 2 user with id: %id\n%exception",
               array("id" => $g2_user_id, "exception" => $e->__toString()));
    }
    $g2_groups = g2(GalleryCoreApi::fetchGroupsForUser($g2_user->getId()));

    try {
      $user = user::create($g2_user->getUsername(), $g2_user->getfullname(), "");
      $message = t("Created user: '%name'.", array("name" => $user->name));
    } catch (Exception $e) {
      // @todo For now we assume this is a "duplicate user" exception
      $user = user::lookup_by_name($g2_user->getUsername());
      $message = t("Loaded existing user: '%name'.", array("name" => $user->name));
    }

    $user->hashed_password = $g2_user->getHashedPassword();
    $user->email = $g2_user->getEmail();
    $user->locale = $g2_user->getLanguage();
    foreach ($g2_groups as $g2_group_id => $g2_group_name) {
      if ($g2_group_id == $g2_admin_group_id) {
        $user->admin = true;
        $message .= t("\n\tAdded 'admin' flag to user");
      } else {
        $group = ORM::factory("group", self::map($g2_group_id));
        $user->add($group);
        $message .= t("\n\tAdded user to group '%group'.", array("group" => $group->name));
      }
    }
    $user->save();

    self::set_map($g2_user->getId(), $user->id);

    return $message;
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
      return t("Album with id: %id already imported, skipping", array("id" => $g2_album_id));
    }

    try {
      // Load the G2 album item, and figure out its parent in G3.
      $g2_album = g2(GalleryCoreApi::loadEntitiesById($g2_album_id));
    } catch (Exception $e) {
      return t("Failed to import Gallery 2 album with id: %id\n%exception",
               array("id" => $g2_album_id, "exception" => $e->__toString()));
    }

    if ($g2_album->getParentId() == null) {
      return t("Skipping Gallery 2 root album");
    }
    $parent_album = ORM::factory("item", self::map($g2_album->getParentId()));

    $album = album::create(
      $parent_album,
      $g2_album->getPathComponent(),
      self::_decode_html_special_chars($g2_album->getTitle()),
      self::_decode_html_special_chars(self::extract_description($g2_album)),
      self::map($g2_album->getOwnerId()));

    $album->view_count = g2(GalleryCoreApi::fetchItemViewCount($g2_album_id));
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
      ORDER_ASCENDING => "asc",
      ORDER_DESCENDING => "desc");
    if (array_key_exists($g2_order = $g2_album->getOrderBy(), $order_map)) {
      $album->sort_column = $order_map[$g2_order];
      $album->sort_order = $direction_map[$g2_album->getOrderDirection()];
    }
    $album->save();

    self::import_keywords_as_tags($g2_album->getKeywords(), $album);
    self::set_map($g2_album_id, $album->id);

    // @todo import album highlights
  }

  /**
   * Set the highlight properly for a single album
   */
  static function set_album_highlight(&$queue) {
    // Dequeue the current album and enqueue its children
    list($g2_album_id, $children) = each($queue);
    unset($queue[$g2_album_id]);
    foreach ($children as $key => $value) {
      $queue[$key] = $value;
    }

    $g3_album_id = self::map($g2_album_id);
    if (!$g3_album_id) {
      return t("Album with id: %id not imported", array("id" => $g3_album_id));
    }

    $table = g2(GalleryCoreApi::fetchThumbnailsByItemIds(array($g2_album_id)));
    if (isset($table[$g2_album_id])) {
      // Backtrack the source id to an item
      $g2_source = $table[$g2_album_id];
      while (GalleryUtilities::isA($g2_source, "GalleryDerivative")) {
        $g2_source = g2(GalleryCoreApi::loadEntitiesById($g2_source->getDerivativeSourceId()));
      }
      $item_id = self::map($g2_source->getId());
      if ($item_id) {
        $item = ORM::factory("item", $item_id);
        $g2_album = ORM::factory("item", $g3_album_id);
        $g2_album->album_cover_item_id = $item->id;
        $g2_album->thumb_dirty = 1;
        $g2_album->view_count = g2(GalleryCoreApi::fetchItemViewCount($g2_album_id));
        $g2_album->save();
        graphics::generate($g2_album);
      }
    }
  }

  /**
   * Import a single photo or movie.
   */
  static function import_item(&$queue) {
    $g2_item_id = array_shift($queue);

    if (self::map($g2_item_id)) {
      return t("Item with id: %id already imported, skipping", array("id" => $g2_item_id));
    }

    try {
      self::$current_g2_item = $g2_item = g2(GalleryCoreApi::loadEntitiesById($g2_item_id));
      $g2_path = g2($g2_item->fetchPath());
    } catch (Exception $e) {
      return t("Failed to import Gallery 2 item with id: %id\n%exception",
               array("id" => $g2_item_id, "exception" => $e->__toString()));
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

    $message = array();
    switch ($g2_type) {
    case "GalleryPhotoItem":
      if (!in_array($g2_item->getMimeType(), array("image/jpeg", "image/gif", "image/png"))) {
        Kohana::log("alert", "$g2_path is an unsupported image type; using a placeholder gif");
        $message[] = t("'%path' is an unsupported image type, using a placeholder",
                     array("path" => $g2_path));
        $g2_path = MODPATH . "g2_import/data/broken-image.gif";
        $corrupt = 1;
      }
      try {
        $item = photo::create(
          $parent,
          $g2_path,
          $g2_item->getPathComponent(),
          self::_decode_html_special_chars($g2_item->getTitle()),
          self::_decode_html_special_chars(self::extract_description($g2_item)),
          self::map($g2_item->getOwnerId()));
      } catch (Exception $e) {
        Kohana::log(
          "alert", "Corrupt image $g2_path\n" . $e->__toString());
        $message[] = t("Corrupt image '%path'", array("path" => $g2_path));
        $message[] = $e->__toString();
        $corrupt = 1;
      }
      break;

    case "GalleryMovieItem":
      // @todo we should transcode other types into FLV
      if (in_array($g2_item->getMimeType(), array("video/mp4", "video/x-flv"))) {
        try {
          $item = movie::create(
            $parent,
            $g2_path,
            $g2_item->getPathComponent(),
            self::_decode_html_special_chars($g2_item->getTitle()),
            self::_decode_html_special_chars(self::extract_description($g2_item)),
            self::map($g2_item->getOwnerId()));
        } catch (Exception $e) {
          Kohana::log("alert", "Corrupt movie $g2_path\n" . $e->__toString());
          $message[] = t("Corrupt movie '%path'", array("path" => $g2_path));
          $message[] = $e->__toString();
          $corrupt = 1;
        }
      } else {
        Kohana::log("alert", "$g2_path is an unsupported movie type");
        $message[] = t("'%path' is an unsupported movie type", array("path" => $g2_path));
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

    if (isset($item)) {
      self::set_map($g2_item_id, $item->id);
      $item->view_count = g2(GalleryCoreApi::fetchItemViewCount($g2_item_id));
      $item->save();
    }

    if ($corrupt) {
      $url_generator = $GLOBALS["gallery"]->getUrlGenerator();
      // @todo we need a more persistent warning
      $g2_item_url = $url_generator->generateUrl(array("itemId" => $g2_item->getId()));
      // Why oh why did I ever approve the session id placeholder idea in G2?
      $g2_item_url =
        str_replace('&amp;g2_GALLERYSID=TMP_SESSION_ID_DI_NOISSES_PMT', '', $g2_item_url);
      if (!empty($item)) {
        $message[] =
          t("<a href=\"%g2_url\">%title</a> from Gallery 2 could not be processed; " .
            "(imported as <a href=\"%g3_url\">%title</a>)",
            array("g2_url" => $g2_item_url,
                  "g3_url" => $item->url(),
                  "title" => $g2_item->getTitle()));
      } else {
        $message[] =
          t("<a href=\"%g2_url\">%title</a> from Gallery 2 could not be processed",
            array("g2_url" => $g2_item_url, "title" => $g2_item->getTitle()));
      }
    }

    self::$current_g2_item = null;
    return $message;
  }

  /**
   * g2 encoded'&', '"', '<' and '>' as '&amp;', '&quot;', '&lt;' and '&gt;' respectively.
   * This function undoes that encoding.
   */
  private static function _decode_html_special_chars($value) {
    return str_replace(array("&amp;", "&quot;", "&lt;", "&gt;"),
                       array("&", "\"", "<", ">"), $value);
  }

  /**
   * Import a single comment.
   */
  static function import_comment(&$queue) {
    $g2_comment_id = array_shift($queue);

    try {
      $g2_comment = g2(GalleryCoreApi::loadEntitiesById($g2_comment_id));
    } catch (Exception $e) {
      return t("Failed to import Gallery 2 comment with id: %id\%exception",
               array("id" => $g2_comment_id, "exception" => $e->__toString()));
    }

    $text = $g2_comment->getSubject();
    if ($text) {
      $text .= " ";
    }
    $text .= $g2_comment->getComment();

    // Just import the fields we know about.  Do this outside of the comment API for now so that
    // we don't trigger spam filtering events
    $comment = ORM::factory("comment");
    $comment->author_id = self::map($g2_comment->getCommenterId());
    $comment->guest_name = $g2_comment->getAuthor();
    $comment->item_id = self::map($g2_comment->getParentId());
    $comment->text = self::_transform_bbcode($text);
    $comment->state = "published";
    $comment->server_http_host = $g2_comment->getHost();
    $comment->created = $g2_comment->getDate();
    $comment->save();

    self::map($g2_comment->getId(), $comment->id);
    return t("Imported comment '%comment' for item with id: %id",
             array("id" => $comment->item_id,
                   "comment" => text::limit_words(nl2br(html::purify($comment->text)), 50)));
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

    try {
      $tag_names = array_values(g2(TagsHelper::getTagsByItemId($g2_item_id)));
    } catch (Exception $e) {
      return t("Failed to import Gallery 2 tags for item with id: %id\n%exception",
               array("id" => $g2_item_id, "exception" => $e->__toString()));
    }

    $tags = "";
    // Multiword tags have the space changed to dots.s
    foreach ($tag_names as $tag_name) {
      $tags .= (strlen($tags) ? ", " : "") .
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

    $tags = "";
    foreach (preg_split("/$delim/", $keywords) as $keyword) {
      $keyword = trim($keyword);
      if ($keyword) {
        $tags .= (strlen($tags) ? ", " : "") . tag::add($item, $keyword);
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
            $item->thumb_dirty = false;
          }
        }

        if ($derivative->getDerivativeType() == DERIVATIVE_TYPE_IMAGE_RESIZE &&
            $item->resize_dirty &&
            ($derivative->getWidth() == $target_resize_size ||
             $derivative->getHeight() == $target_resize_size)) {
          if (@copy(g2($derivative->fetchPath()), $item->resize_path())) {
            $item->resize_height = $derivative->getHeight();
            $item->resize_width = $derivative->getWidth();
            $item->resize_dirty = false;
          }
        }
      }
    }
    $item->save();
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
      "AND   [GalleryComment::id] > ?",
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
      "WHERE [TagItemMap::itemId] > ?",
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
      $g2_map = ORM::factory("g2_map")->where("g2_id", $g2_id)->find();
      self::$map[$g2_id] = $g2_map->loaded ? $g2_map->g3_id : null;
    }

    return self::$map[$g2_id];
  }

  /**
   * Associate a Gallery 2 id with a Gallery 3 item id.
   */
  static function set_map($g2_id, $g3_id) {
    $g2_map = ORM::factory("g2_map");
    $g2_map->g3_id = $g3_id;
    $g2_map->g2_id = $g2_id;
    $g2_map->save();
    self::$map[$g2_id] = $g3_id;
  }

  static function log($msg) {
    message::warning($msg);
    Kohana::log("alert", $msg);
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
  $ret = array_shift($args);
  if ($ret) {
    Kohana::log("error", "Gallery 2 call failed with: " . $ret->getAsText());
    throw new Exception("@todo G2_FUNCTION_FAILED");
  }
  if (count($args) == 1) {
    return $args[0];
  } else {
    return $args;
  }
}

