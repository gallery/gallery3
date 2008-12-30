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
/**
 * API for Gallery Access control.
 *
 * Permissions are hierarchical, and apply only to groups and albums.  They cascade down from the
 * top of the Gallery to the bottom, so if you set a permission in the root album, that permission
 * applies for any sub-album unless the sub-album overrides it.  Likewise, any permission applied
 * to an album applies to any photos inside the album.  Overrides can be applied at any level of
 * the hierarchy for any permission other than View permissions.
 *
 * View permissions are an exceptional case.  In the case of viewability, we want to ensure that
 * if an album's parent is inaccessible, then this album must be inaccessible also.  So while view
 * permissions cascade downwards and you're free to set the ALLOW permission on any album, that
 * ALLOW permission will be ignored unless all that album's parents are marked ALLOW also.
 *
 * Implementatation Notes:
 *
 * Notes refer to this example album hierarchy:
 *      A1
 *     /  \
 *   A2   A3
 *       /  \
 *      A4  A5
 *
 * o We have the concept of "intents".  A user can specify that he intends for A3 to be
 *   inaccessible (ie: a DENY on the "view" permission to the EVERYBODY group).  Once A3 is
 *   inaccessible, A5 can never be displayed to that group.  If A1 is made inaccessible, then the
 *   entire tree is hidden.  If subsequently A1 is made accessible, then the whole tree is
 *   available again *except* A3 and below since the user's "intent" for A3 is maintained.
 *
 * o Intents are specified as <group_id, perm, item_id> tuples.  It would be inefficient to check
 *   these tuples every time we want to do a lookup, so we use these intents to create an entire
 *   table of permissions for easy lookup in the Access_Cache_Model.  There's a 1:1 mapping
 *   between Item_Model and Access_Cache_Model entries.
 *
 * o For efficiency, we create columns in Access_Intent_Model and Access_Cache_Model for each of
 *   the possible Group_Model and Permission_Model combinations.  This may lead to performance
 *   issues for very large Gallery installs, but for small to medium sized ones (5-10 groups, 5-10
 *   permissions) it's especially efficient because there's a single field value for each
 *   group/permission/item combination.
 *
 * o For efficiency, we store the cache columns for view permissions directly in the Item_Model.
 *   This means that we can filter items by group/permission combination without doing any table
 *   joins making for an especially efficient permission check at the expense of having to
 *   maintain extra columns for each item.
 *
 * o If at any time the Access_Cache_Model becomes invalid, we can rebuild the entire table from
 *   the Access_Intent_Model
 */
class access_Core {
  const DENY      = 0;
  const ALLOW     = 1;
  const UNKNOWN   = 2;

  /**
   * Does this group have this permission on this item?
   *
   * @param  Group_Model $group
   * @param  string      $perm_name
   * @param  Item_Model  $item
   * @return boolean
   */
  public static function group_can($group, $perm_name, $item) {
    $resource = $perm_name == "view" ?
      $item : model_cache::get("access_cache", $item->id, "item_id");
    return $resource->__get("{$perm_name}_{$group->id}") === self::ALLOW;
  }

  /**
   * Does the active user have this permission on this item?
   *
   * @param  string     $perm_name
   * @param  Item_Model $item
   * @return boolean
   */
  public static function can($perm_name, $item) {
    if (!$item->loaded) {
      return false;
    }

    $resource = $perm_name == "view" ?
      $item : model_cache::get("access_cache", $item->id, "item_id");
    foreach (user::group_ids() as $id) {
      if ($resource->__get("{$perm_name}_$id") === self::ALLOW) {
        return true;
      }
    }
    return false;
  }

  /**
   * If the active user does not have this permission, failed with an access::forbidden().
   *
   * @param  string     $perm_name
   * @param  Item_Model $item
   * @return boolean
   */
  public static function required($perm_name, $item) {
    if (!access::can($perm_name, $item)) {
      access::forbidden();
    }
  }

  /**
   * Terminate immediately with an HTTP 503 Forbidden response.
   */
  public static function forbidden() {
    throw new Exception("@todo FORBIDDEN", 503);
  }

  /**
   * Internal method to set a permission
   *
   * @param  Group_Model $group
   * @param  string      $perm_name
   * @param  Item_Model  $item
   * @param  boolean     $value
   */
  private static function _set($group, $perm_name, $album, $value) {
    if (!$album->loaded) {
      throw new Exception("@todo INVALID_ALBUM $album->id");
    }
    if ($album->type != "album") {
      throw new Exception("@todo INVALID_ALBUM_TYPE not an album");
    }
    $access = model_cache::get("access_intent", $album->id, "item_id");
    $access->__set("{$perm_name}_{$group->id}", $value);
    $access->save();

    if ($perm_name == "view") {
      self::_update_access_view_cache($group, $album);
      if ($group->id == 1) {
        if ($value === self::DENY) {
          self::_create_htaccess_files($album);
        } else {
          self::_delete_htaccess_files($album);
        }
      }
    } else {
      self::_update_access_non_view_cache($group, $perm_name, $album);
    }
  }

  /**
   * Allow a group to have a permission on an item.
   *
   * @param  Group_Model $group
   * @param  string  $perm_name
   * @param  Item_Model $item
   */
  public static function allow($group, $perm_name, $item) {
    self::_set($group, $perm_name, $item, self::ALLOW);
  }

  /**
   * Deny a group the given permission on an item.
   *
   * @param  Group_Model $group
   * @param  string  $perm_name
   * @param  Item_Model $item
   */
  public static function deny($group, $perm_name, $item) {
    self::_set($group, $perm_name, $item, self::DENY);
  }

  /**
   * Unset the given permission for this item and use inherited values
   *
   * @param  Group_Model $group
   * @param  string  $perm_name
   * @param  Item_Model $item
   */
  public static function reset($group, $perm_name, $item) {
    if ($item->id == 1) {
      throw new Exception("@todo CANT_RESET_ROOT_PERMISSION");
    }
    self::_set($group, $perm_name, $item, null);
  }

  /**
   * Register a permission so that modules can use it.
   *
   * @param  string $perm_name
   * @return void
  */
  public static function register_permission($perm_name) {
    $permission = ORM::factory("permission", $perm_name);
    if ($permission->loaded) {
      throw new Exception("@todo PERMISSION_ALREADY_EXISTS $name");
    }
    $permission->name = $perm_name;
    $permission->save();

    foreach (self::_get_all_groups() as $group) {
      self::_add_columns($perm_name, $group);
    }
  }

  /**
   * Delete a permission.
   *
   * @param  string $perm_name
   * @return void
   */
  public static function delete_permission($name) {
    foreach (self::_get_all_groups() as $group) {
      self::_drop_columns($name, $group);
    }
    $permission = ORM::factory("permission")->where("name", $name)->find();
    if ($permission->loaded) {
      $permission->delete();
    }
  }

  /**
   * Add the appropriate columns for a new group
   *
   * @param Group_Model $group
   * @return void
   */
  public static function add_group($group) {
    foreach (ORM::factory("permission")->find_all() as $perm) {
      self::_add_columns($perm->name, $group);
    }
  }

  /**
   * Remove a group's permission columns (usually when it's deleted)
   *
   * @param Group_Model $group
   * @return void
   */
  public static function delete_group($group) {
    foreach (ORM::factory("permission")->find_all() as $perm) {
      self::_drop_columns($perm->name, $group);
    }
  }

  /**
   * Add new access rows when a new item is added.
   *
   * @param Item_Model $item
   * @return void
   */
  public static function add_item($item) {
    $access_intent = ORM::factory("access_intent", $item->id);
    if ($access_intent->loaded) {
      throw new Exception("@todo ITEM_ALREADY_ADDED $item->id");
    }
    $access_intent = ORM::factory("access_intent");
    $access_intent->item_id = $item->id;
    $access_intent->save();

    // Create a new access cache entry and copy the parents values.
    $access_cache = ORM::factory("access_cache");
    $access_cache->item_id = $item->id;
    if ($item->id != 1) {
      $parent_access_cache =
        ORM::factory("access_cache")->where("item_id", $item->parent()->id)->find();
      foreach (self::_get_all_groups() as $group) {
        foreach (ORM::factory("permission")->find_all() as $perm) {
          $field = "{$perm->name}_{$group->id}";
          if ($perm->name == "view") {
            $item->$field = $item->parent()->$field;
          } else {
            $access_cache->$field = $parent_access_cache->$field;
          }
        }
      }
    }
    $item->save();
    $access_cache->save();
  }

  /**
   * Delete appropriate access rows when an item is deleted.
   *
   * @param Item_Model $item
   * @return void
   */
  public static function delete_item($item) {
    ORM::factory("access_intent")->where("item_id", $item->id)->find()->delete();
    ORM::factory("access_cache")->where("item_id", $item->id)->find()->delete();
  }

  /**
   * Verify our Cross Site Request Forgery token is valid, else throw an exception.
   */
  public static function verify_csrf() {
    $input = Input::instance();
    if ($input->post("csrf", $input->get("csrf", null)) !== Session::instance()->get("csrf")) {
      access::forbidden();
    }
  }

  /**
   * Get the Cross Site Request Forgery token for this session.
   * @return string
   */
  public static function csrf_token() {
    $session = Session::instance();
    $csrf = $session->get("csrf");
    if (empty($csrf)) {
      $csrf = md5(rand());
      $session->set("csrf", $csrf);
    }
    return $csrf;
  }

  /**
   * Generate an <input> element containing the Cross Site Request Forgery token for this session.
   * @return string
   */
  public static function csrf_form_field() {
    return "<input type=\"hidden\" name=\"csrf\" value=\"" . self::csrf_token() . "\"/>";
  }

  /**
   * Internal method to get all available groups.
   *
   * @return ORM_Iterator
   */
  private static function _get_all_groups() {
    if (module::is_installed("user")) {
      return ORM::factory("group")->find_all();
    } else {
      return array();
    }
  }

  /**
   * Internal method to  remove Permission/Group columns
   *
   * @param  Group_Model $group
   * @param  string      $perm_name
   * @return void
   */
  private static function _drop_columns($perm_name, $group) {
    $db = Database::instance();
    $field = "{$perm_name}_{$group->id}";
    $cache_table = $perm_name == "view" ? "items" : "access_caches";
    $db->query("ALTER TABLE `$cache_table` DROP `$field`");
    $db->query("ALTER TABLE `access_intents` DROP `$field`");
  }

  /**
   * Internal method to add Permission/Group columns
   *
   * @param  Group_Model $group
   * @param  string  $perm_name
   * @return void
   */
  private static function _add_columns($perm_name, $group) {
    $db = Database::instance();
    $field = "{$perm_name}_{$group->id}";
    $cache_table = $perm_name == "view" ? "items" : "access_caches";
    $db->query("ALTER TABLE `$cache_table` ADD `$field` TINYINT(2) NOT NULL DEFAULT 0");
    $db->query("ALTER TABLE `access_intents` ADD `$field` BOOLEAN DEFAULT NULL");
    $db->query("UPDATE `access_intents` SET `$field` = 0 WHERE `item_id` = 1");
  }

  /**
   * Update the Access_Cache model based on information from the Access_Intent model for view
   * permissions only.
   *
   * @todo: use database locking
   *
   * @param  Group_Model $group
   * @param  Item_Model $item
   * @return void
   */
  private static function _update_access_view_cache($group, $item) {
    $access = ORM::factory("access_intent")->where("item_id", $item->id)->find();

    $db = Database::instance();
    $field = "view_{$group->id}";

    // With view permissions, deny values in the parent can override allow values in the child,
    // so start from the bottom of the tree and work upwards overlaying negative on top of
    // positive.
    //
    // If the item's intent is ALLOW or DEFAULT, it's possible that some ancestor has specified
    // DENY and this ALLOW cannot be obeyed.  So in that case, back up the tree and find any
    // non-DEFAULT and non-ALLOW parent and propagate from there.  If we can't find a matching
    // item, then its safe to propagate from here.
    if ($access->$field !== self::DENY) {
      $tmp_item = ORM::factory("item")
        ->where("left <", $item->left)
        ->where("right >", $item->right)
        ->where($field, self::DENY)
        ->orderby("left", "DESC")
        ->limit(1)
        ->find();
      if ($tmp_item->loaded) {
        $item = $tmp_item;
      }
    }

    // We will have a problem if we're trying to change a DENY to an ALLOW because the
    // access_caches table will already contain DENY values and we won't be able to overwrite
    // them according the rule above.  So mark every permission below this level as UNKNOWN so
    // that we can tell which permissions have been changed, and which ones need to be updated.
    $db->query("UPDATE `items` SET `$field` = ? " .
               "WHERE `left` >= $item->left " .
               "AND `right` <= $item->right",
               array(self::UNKNOWN));

    $query = $db->query(
      "SELECT `access_intents`.`$field`, `items`.`left`, `items`.`right`, `items`.`id` " .
      "FROM `access_intents` JOIN (`items`) ON (`access_intents`.`item_id` = `items`.`id`) " .
      "WHERE `left` >= $item->left " .
      "AND `right` <= $item->right " .
      "AND `type` = 'album' " .
      "AND `access_intents`.`$field` IS NOT NULL " .
      "ORDER BY `level` DESC ");
    foreach ($query as $row) {
      if ($row->$field == self::ALLOW) {
        // Propagate ALLOW for any row that is still UNKNOWN.
        $db->query(
          "UPDATE `items` SET `$field` = {$row->$field} " .
          "WHERE `$field` = ? " .
          "AND `left` >= $row->left " .
          "AND `right` <= $row->right",
          array(self::UNKNOWN));
      } else if ($row->$field == self::DENY) {
        // DENY overwrites everything below it
        $db->query(
          "UPDATE `items` SET `$field` = {$row->$field} " .
          "WHERE `left` >= $row->left " .
          "AND `right` <= $row->right");
      }
    }

    // Finally, if our intent is DEFAULT at this point it means that we were unable to find a
    // DENY parent in the hierarchy to propagate from.  So we'll still have a UNKNOWN values in
    // the hierarchy, and all of those are safe to change to ALLOW.
    $db->query("UPDATE `items` SET `$field` = ? " .
               "WHERE `$field` = ? " .
               "AND `left` >= $item->left " .
               "AND `right` <= $item->right",
               array(self::ALLOW, self::UNKNOWN));
  }

  /**
   * Update the Access_Cache model based on information from the Access_Intent model for non-view
   * permissions.
   *
   * @todo: use database locking
   *
   * @param  Group_Model $group
   * @param  string  $perm_name
   * @param  Item_Model $item
   * @return void
   */
  private static function _update_access_non_view_cache($group, $perm_name, $item) {
    $access = ORM::factory("access_intent")->where("item_id", $item->id)->find();

    $db = Database::instance();
    $field = "{$perm_name}_{$group->id}";

    // If the item's intent is DEFAULT, then we need to back up the chain to find the nearest
    // parent with an intent and propagate from there.
    //
    // @todo To optimize this, we wouldn't need to propagate from the parent, we could just
    //       propagate from here with the parent's intent.
    if ($access->$field === null) {
      $tmp_item = ORM::factory("item")
        ->join("access_intents", "items.id", "access_intents.item_id")
        ->where("left <", $item->left)
        ->where("right >", $item->right)
        ->where("$field IS NOT", null)
        ->orderby("left", "DESC")
        ->limit(1)
        ->find();
      if ($tmp_item->loaded) {
        $item = $tmp_item;
      }
    }

    // With non-view permissions, each level can override any permissions that came above it
    // so start at the top and work downwards, overlaying permissions as we go.
    $query = $db->query(
      "SELECT `access_intents`.`$field`, `items`.`left`, `items`.`right` " .
      "FROM `access_intents` JOIN (`items`) ON (`access_intents`.`item_id` = `items`.`id`) " .
      "WHERE `left` >= $item->left " .
      "AND `right` <= $item->right " .
      "AND `type` = 'album' " .
      "AND `$field` IS NOT NULL " .
      "ORDER BY `level` ASC");
    foreach  ($query as $row) {
      $db->query(
        "UPDATE `access_caches` SET `$field` = {$row->$field} " .
        "WHERE `item_id` IN " .
        "  (SELECT `id` FROM `items` " .
        "  WHERE `left` >= $row->left " .
        "  AND `right` <= $row->right)");
    }
  }

  /**
   * Create .htaccess files to prevent direct access to the given album and its hierarchy.
   */
  private static function _create_htaccess_files($album) {
    foreach (array($album->file_path(),
                   dirname($album->resize_path()),
                   dirname($album->thumb_path())) as $dir) {
      $base_url = url::site("file_proxy");
      $fp = fopen("$dir/.htaccess", "w+");
      fwrite($fp, "<IfModule mod_rewrite.c>\n");
      fwrite($fp, "  RewriteEngine On\n");
      fwrite($fp, "  RewriteRule (.*) $base_url/\$1 [L]\n");
      fwrite($fp, "</IfModule>\n");
      fwrite($fp, "<IfModule !mod_rewrite.c>\n");
      fwrite($fp, "  Order Deny,Allow\n");
      fwrite($fp, "  Deny from All\n");
      fwrite($fp, "</IfModule>\n");
      fclose($fp);
    }
  }

  /**
   * Delete the .htaccess files that are preventing access to the given album and its hierarchy.
   */
  private static function _delete_htaccess_files($album) {
    @unlink($album->file_path() . "/.htaccess");
    @unlink(dirname($album->resize_path()) . "/.htaccess");
    @unlink(dirname($album->thumb_path()) . "/.htaccess");
  }
}
