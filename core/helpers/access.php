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
 *   available again *except* A3 and below since the user's preference for A3 is maintained.
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
 * o If at any time the Access_Cache_Model becomes invalid, we can rebuild the entire table from
 *   the Access_Intent_Model
 *
 * TODO
 * o In the near future, we'll be moving the "view" columns out of Access_Intent_Model and
 *   directly into Item_Model.  By doing this, we'll be able to find viewable items (the most
 *   common permission access) without doing table joins.
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
    $access = ORM::factory("access_cache")->where("item_id", $item->id)->find();
    if (!$access) {
      throw new Exception("@todo MISSING_ACCESS for $item->id");
    }

    $group_id = $group ? $group->id : 0;
    return $access->__get("{$perm_name}_$group_id") === self::ALLOW;
  }

  /**
   * Does the active user have this permission on this item?
   *
   * @param  string     $perm_name
   * @param  Item_Model $item
   * @return boolean
   */
  public static function can($perm_name, $item) {
    $user = Session::instance()->get("user", null);
    if ($user) {
      $access = ORM::factory("access_cache")->where("item_id", $item->id)->find();
      if (!$access) {
        throw new Exception("@todo MISSING_ACCESS for $item->id");
      }

      if ($access->view_0 == self::ALLOW) {
        return true;
      }
      foreach ($user->groups as $group) {
        if ($access->__get("{$perm_name}_{$group->id}") === self::ALLOW) {
          return true;
        }
      }
      return false;
    } else {
      return self::group_can(group::EVERYBODY, $perm_name, $item);
    }
  }

  /**
   * Internal method to set a permission
   *
   * @param  Group_Model $group
   * @param  string  $perm_name
   * @param  Item_Model $item
   * @param  boolean $value
   * @return boolean
   */
  private static function _set($group, $perm_name, $item, $value) {
    $access = ORM::factory("access_intent")->where("item_id", $item->id)->find();
    if (!$access->loaded) {
      throw new Exception("@todo MISSING_ACCESS for $item->id");
    }

    $group_id = $group ? $group->id : 0;
    $access->__set("{$perm_name}_$group_id", $value);
    $access->save();

    if ($perm_name =="view") {
      self::_update_access_view_cache($group, $item);
    } else {
      self::_update_access_non_view_cache($group, $perm_name, $item);
    }
  }

  /**
   * Allow a group to have a permission on an item.
   *
   * @param  Group_Model $group
   * @param  string  $perm_name
   * @param  Item_Model $item
   * @return boolean
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
   * @return boolean
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
   * @return boolean
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
    self::_add_columns($perm_name, null);
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
    self::_drop_columns($name, null);
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
    $access_intent = ORM::factory("access_intent");
    $access_intent->item_id = $item->id;
    $access_intent->save();

    // Create a new access cache entry and copy the parents values.
    $parent_access_cache =
      ORM::factory("access_cache")->where("item_id", $item->parent()->id)->find();
    $access_cache = ORM::factory("access_cache");
    $access_cache->item_id = $item->id;
    foreach (ORM::factory("permission")->find_all() as $perm) {
      foreach (self::_get_all_groups() as $group) {
        $field = "{$perm->name}_{$group->id}";
        $access_cache->$field = $parent_access_cache->$field;
      }
      $field = "{$perm->name}_0";
      $access_cache->$field = $parent_access_cache->$field;
    }
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
    $group_id = $group ? $group->id : 0;
    $db = Database::instance();
    $field = "{$perm_name}_$group_id";
    $db->query("ALTER TABLE `access_caches` DROP `$field`");
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
    $group_id = $group ? $group->id : 0;
    $db = Database::instance();
    $field = "{$perm_name}_$group_id";
    $db->query("ALTER TABLE `access_caches` ADD `$field` TINYINT(2) NOT NULL DEFAULT 0");
    $db->query("ALTER TABLE `access_intents` ADD `$field` BOOLEAN DEFAULT NULL");
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
  public static function _update_access_view_cache($group, $item) {
    $access = ORM::factory("access_intent")->where("item_id", $item->id)->find();

    $group_id = $group ? $group->id : 0;
    $db = Database::instance();
    $field = "view_$group_id";

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
        ->join("access_intents", "items.id", "access_intents.item_id")
        ->where("left <", $item->left)
        ->where("right >", $item->right)
        ->where("$field", self::DENY)
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
    $db->query("UPDATE `access_caches` SET `$field` = ? " .
               "WHERE `item_id` IN " .
               "  (SELECT `id` FROM `items` " .
               "  WHERE `left` >= $item->left " .
               "  AND `right` <= $item->right)",
               array(self::UNKNOWN));

    $query = $db->query(
      "SELECT `access_intents`.`$field`, `items`.`left`, `items`.`right`, `items`.`id` " .
      "FROM `access_intents` JOIN (`items`) ON (`access_intents`.`item_id` = `items`.`id`) " .
      "WHERE `left` >= $item->left " .
      "AND `right` <= $item->right " .
      "AND `type` = 'album' " .
      "AND `$field` IS NOT NULL " .
      "ORDER BY `level` DESC ");
    foreach ($query as $row) {
      if ($row->$field == self::ALLOW) {
        // Propagate ALLOW for any row that is still UNKNOWN.
        $db->query(
          "UPDATE `access_caches` SET `$field` = {$row->$field} " .
          "WHERE `$field` = ? " .
          "AND `item_id` IN " .
          "  (SELECT `id` FROM `items` " .
          "  WHERE `left` >= $row->left " .
          "  AND `right` <= $row->right)",
          array(self::UNKNOWN));
      } else if ($row->$field == self::DENY) {
        // DENY overwrites everything below it
        $db->query(
          "UPDATE `access_caches` SET `$field` = {$row->$field} " .
          "WHERE `item_id` IN " .
          "  (SELECT `id` FROM `items` " .
          "  WHERE `left` >= $row->left " .
          "  AND `right` <= $row->right)");
      }
    }

    // Finally, if our intent is DEFAULT at this point it means that we were unable to find a
    // DENY parent in the hierarchy to propagate from.  So we'll still have a UNKNOWN values in
    // the hierarchy, and all of those are safe to change to ALLOW.
    $db->query("UPDATE `access_caches` SET `$field` = ? " .
               "WHERE `$field` = ? " .
               "AND `item_id` IN " .
               "  (SELECT `id` FROM `items` " .
               "  WHERE `left` >= $item->left " .
               "  AND `right` <= $item->right)",
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
  public static function _update_access_non_view_cache($group, $perm_name, $item) {
    $access = ORM::factory("access_intent")->where("item_id", $item->id)->find();

    $group_id = $group ? $group->id : 0;
    $db = Database::instance();
    $field = "{$perm_name}_$group_id";


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
}
