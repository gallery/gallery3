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
   * Does the active user have this permission on this item?
   *
   * @param  string     $perm_name
   * @param  Item_Model $item
   * @return boolean
   */
  static function can($perm_name, $item) {
    if (!$item->loaded) {
      return false;
    }

    if (user::active()->admin) {
      return true;
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
  static function required($perm_name, $item) {
    if (!self::can($perm_name, $item)) {
      self::forbidden();
    }
  }

  /**
   * Does this group have this permission on this item?
   *
   * @param  Group_Model $group
   * @param  string      $perm_name
   * @param  Item_Model  $item
   * @return boolean
   */
  static function group_can($group, $perm_name, $item) {
    $resource = $perm_name == "view" ?
      $item : model_cache::get("access_cache", $item->id, "item_id");
    return $resource->__get("{$perm_name}_{$group->id}") === self::ALLOW;
  }

  /**
   * Return this group's intent for this permission on this item.
   *
   * @param  Group_Model $group
   * @param  string      $perm_name
   * @param  Item_Model  $item
   * @return integer     access::ALLOW, access::DENY or null for no intent
   */
  static function group_intent($group, $perm_name, $item) {
    $intent = model_cache::get("access_intent", $item->id, "item_id");
    return $intent->__get("{$perm_name}_{$group->id}");
  }

  /**
   * Is the permission on this item locked by a parent?  If so return the nearest parent that
   * locks it.
   *
   * @param  Group_Model $group
   * @param  string      $perm_name
   * @param  Item_Model  $item
   * @return ORM_Model   item that locks this one
   */
  static function locked_by($group, $perm_name, $item) {
    if ($perm_name != "view") {
      return null;
    }

    // For view permissions, if any parent is self::DENY, then those parents lock this one.
    // Return
    $lock = ORM::factory("item")
      ->where("`left` <= $item->left")
      ->where("`right` >= $item->right")
      ->where("items.id <> $item->id")
      ->join("access_intents", "items.id", "access_intents.item_id")
      ->where("access_intents.view_$group->id", 0)
      ->orderby("level", "DESC")
      ->limit(1)
      ->find();

    if ($lock->loaded) {
      return $lock;
    } else {
      return null;
    }
  }

  /**
   * Terminate immediately with an HTTP 503 Forbidden response.
   */
  static function forbidden() {
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
  private static function _set(Group_Model $group, $perm_name, $album, $value) {
    if (get_class($group) != "Group_Model") {
      throw new Exception("@todo PERMISSIONS_ONLY_WORK_ON_GROUPS");
    }
    if (!$album->loaded) {
      throw new Exception("@todo INVALID_ALBUM $album->id");
    }
    if (!$album->is_album()) {
      throw new Exception("@todo INVALID_ALBUM_TYPE not an album");
    }
    $access = model_cache::get("access_intent", $album->id, "item_id");
    $access->__set("{$perm_name}_{$group->id}", $value);
    $access->save();

    if ($perm_name == "view") {
      self::_update_access_view_cache($group, $album);
    } else {
      self::_update_access_non_view_cache($group, $perm_name, $album);
    }

    self::_update_htaccess_files($album, $group, $perm_name, $value);
  }

  /**
   * Allow a group to have a permission on an item.
   *
   * @param  Group_Model $group
   * @param  string  $perm_name
   * @param  Item_Model $item
   */
  static function allow($group, $perm_name, $item) {
    self::_set($group, $perm_name, $item, self::ALLOW);
  }

  /**
   * Deny a group the given permission on an item.
   *
   * @param  Group_Model $group
   * @param  string  $perm_name
   * @param  Item_Model $item
   */
  static function deny($group, $perm_name, $item) {
    self::_set($group, $perm_name, $item, self::DENY);
  }

  /**
   * Unset the given permission for this item and use inherited values
   *
   * @param  Group_Model $group
   * @param  string  $perm_name
   * @param  Item_Model $item
   */
  static function reset($group, $perm_name, $item) {
    if ($item->id == 1) {
      throw new Exception("@todo CANT_RESET_ROOT_PERMISSION");
    }
    self::_set($group, $perm_name, $item, null);
  }

  /**
   * Register a permission so that modules can use it.
   *
   * @param  string $name           The internal name for for this permission
   * @param  string $display_name   The internationalized version of the displayable name
   * @return void
  */
  static function register_permission($name, $display_name) {
    $permission = ORM::factory("permission", $name);
    if ($permission->loaded) {
      throw new Exception("@todo PERMISSION_ALREADY_EXISTS $name");
    }
    $permission->name = $name;
    $permission->display_name = $display_name;
    $permission->save();

    foreach (self::_get_all_groups() as $group) {
      self::_add_columns($name, $group);
    }
  }

  /**
   * Delete a permission.
   *
   * @param  string $perm_name
   * @return void
   */
  static function delete_permission($name) {
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
  static function add_group($group) {
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
  static function delete_group($group) {
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
  static function add_item($item) {
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
  static function delete_item($item) {
    ORM::factory("access_intent")->where("item_id", $item->id)->find()->delete();
    ORM::factory("access_cache")->where("item_id", $item->id)->find()->delete();
  }

  /**
   * Verify our Cross Site Request Forgery token is valid, else throw an exception.
   */
  static function verify_csrf() {
    $input = Input::instance();
    if ($input->post("csrf", $input->get("csrf", null)) !== Session::instance()->get("csrf")) {
      self::forbidden();
    }
  }

  /**
   * Get the Cross Site Request Forgery token for this session.
   * @return string
   */
  static function csrf_token() {
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
  static function csrf_form_field() {
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
    $db->query("ALTER TABLE {{$cache_table}} DROP `$field`");
    $db->query("ALTER TABLE {access_intents} DROP `$field`");
    ORM::factory("access_intent")->clear_cache();
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
    $db->query("ALTER TABLE {{$cache_table}} ADD `$field` SMALLINT NOT NULL DEFAULT 0");
    $db->query("ALTER TABLE {access_intents} ADD `$field` BOOLEAN DEFAULT NULL");
    $db->update("access_intents", array($field => 0), array("item_id" => 1));
    ORM::factory("access_intent")->clear_cache();
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
        ->join("access_intents", "access_intents.item_id", "items.id")
        ->where("access_intents.$field", self::DENY)
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
    $db->update("items", array($field => self::UNKNOWN),
                array("left >=" => $item->left, "right <=" => $item->right));

    $query = ORM::factory("access_intent")
      ->select(array("access_intents.$field", "items.left", "items.right", "items.id"))
      ->join("items", "items.id", "access_intents.item_id")
      ->where("left >=", $item->left)
      ->where("right <=", $item->right)
      ->where("type", "album")
      ->where("access_intents.$field IS NOT", null)
      ->orderby("level", "DESC")
      ->find_all();
    foreach ($query as $row) {
      if ($row->$field == self::ALLOW) {
        // Propagate ALLOW for any row that is still UNKNOWN.
        $db->update("items", array($field => $row->$field),
          array($field => self::UNKNOWN, "left >=" => $row->left, "right <=" => $row->right));
      } else if ($row->$field == self::DENY) {
        // DENY overwrites everything below it
        $db->update("items", array($field => $row->$field),
                    array("left >=" => $row->left, "right <=" => $row->right));
      }
    }

    // Finally, if our intent is DEFAULT at this point it means that we were unable to find a
    // DENY parent in the hierarchy to propagate from.  So we'll still have a UNKNOWN values in
    // the hierarchy, and all of those are safe to change to ALLOW.
    $db->update("items", array($field => self::ALLOW),
                array($field => self::UNKNOWN, "left >=" => $item->left, "right <=" => $item->right));
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
    $query = ORM::factory("access_intent")
      ->select(array("access_intents.$field", "items.left", "items.right"))
      ->join("items", "items.id", "access_intents.item_id")
      ->where("left >=", $item->left)
      ->where("right <=", $item->right)
      ->where("$field IS NOT", null)
      ->orderby("level", "ASC")
      ->find_all();
    foreach  ($query as $row) {
      $db->query(
        "UPDATE {access_caches} SET `$field` = {$row->$field} " .
        "WHERE `item_id` IN " .
        "  (SELECT `id` FROM {items} " .
        "  WHERE `left` >= $row->left " .
        "  AND `right` <= $row->right)");
    }
  }

  /**
   * Maintain .htacccess files to prevent direct access to albums, resizes and thumbnails when we
   * apply the view and view_full permissions to guest users.
   */
  private static function _update_htaccess_files($album, $group, $perm_name, $value) {
    if ($group->id != 1 || !($perm_name == "view" || $perm_name == "view_full")) {
      return;
    }

    $dirs = array($album->file_path());
    if ($perm_name == "view") {
      $dirs[] = dirname($album->resize_path());
      $dirs[] = dirname($album->thumb_path());
    }

    $base_url = url::site("file_proxy");
    foreach ($dirs as $dir) {
      if ($value === self::DENY) {
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
      } else {
        @unlink($dir . "/.htaccess");
      }
    }
  }

  static function private_key() {
    return module::get_var("core", "private_key");
  }
}
