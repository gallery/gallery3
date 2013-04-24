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
 *   table of permissions for easy lookup in the Model_AccessCache.  There's a 1:1 mapping
 *   between Model_Item and Model_AccessCache entries.
 *
 * o For efficiency, we create columns in Model_AccessIntent and Model_AccessCache for each of
 *   the possible Model_Group and Model_Permission combinations.  This may lead to performance
 *   issues for very large Gallery installs, but for small to medium sized ones (5-10 groups, 5-10
 *   permissions) it's especially efficient because there's a single field value for each
 *   group/permission/item combination.
 *
 * o For efficiency, we store the cache columns for view permissions directly in the Model_Item.
 *   This means that we can filter items by group/permission combination without doing any table
 *   joins making for an especially efficient permission check at the expense of having to
 *   maintain extra columns for each item.
 *
 * o If at any time the Model_AccessCache becomes invalid, we can rebuild the entire table from
 *   the Model_AccessIntent
 */
class Gallery_Access {
  const DENY      = "0";
  const ALLOW     = "1";
  const INHERIT   = null; // access_intent
  const UNKNOWN   = null; // cache (access_cache, items)

  /**
   * Does the active user have this permission on this item?
   *
   * @param  string     $perm_name
   * @param  Model_Item $item
   * @return boolean
   */
  static function can($perm_name, $item) {
    return Access::user_can(Identity::active_user(), $perm_name, $item);
  }

  /**
   * Does the user have this permission on this item?
   *
   * @param  Model_User $user
   * @param  string     $perm_name
   * @param  Model_Item $item
   * @return boolean
   */
  static function user_can($user, $perm_name, $item) {
    if (!$item->loaded()) {
      return false;
    }

    if ($user->admin) {
      return true;
    }

    // Use the nearest parent album (including the current item) so that we take advantage
    // of the cache when checking many items in a single album.
    $id = ($item->type == "album") ? $item->id : $item->parent_id;
    $resource = $perm_name == "view" ?
      $item : ORM::factory("AccessCache", $id, "item_id");

    foreach ($user->groups() as $group) {
      if ($resource->__get("{$perm_name}_{$group->id}") === Access::ALLOW) {
        return true;
      }
    }
    return false;
  }

  /**
   * If the active user does not have this permission, failed with an Access::forbidden().
   *
   * @param  string     $perm_name
   * @param  Model_Item $item
   * @return boolean
   */
  static function required($perm_name, $item) {
    if (!Access::can($perm_name, $item)) {
      if ($perm_name == "view") {
        // Treat as if the item didn't exist, don't leak any information.
        throw HTTP_Exception::factory(404);
      } else {
        Access::forbidden();
      }
    }
  }

  /**
   * Does this group have this permission on this item?
   *
   * @param  Model_Group $group
   * @param  string      $perm_name
   * @param  Model_Item  $item
   * @return boolean
   */
  static function group_can($group, $perm_name, $item) {
    // Use the nearest parent album (including the current item) so that we take advantage
    // of the cache when checking many items in a single album.
    $id = ($item->type == "album") ? $item->id : $item->parent_id;
    $resource = $perm_name == "view" ?
      $item : ORM::factory("AccessCache", $id, "item_id");

    return $resource->__get("{$perm_name}_{$group->id}") === Access::ALLOW;
  }

  /**
   * Return this group's intent for this permission on this item.
   *
   * @param  Model_Group $group
   * @param  string      $perm_name
   * @param  Model_Item  $item
   * @return boolean     Access::ALLOW, Access::DENY or Access::INHERIT (null) for no intent
   */
  static function group_intent($group, $perm_name, $item) {
    $intent = $item->access_intent;
    return $intent->__get("{$perm_name}_{$group->id}");
  }

  /**
   * Is the permission on this item locked by a parent?  If so return the nearest parent that
   * locks it.
   *
   * @param  Model_Group $group
   * @param  string      $perm_name
   * @param  Model_Item  $item
   * @return ORM         item that locks this one
   */
  static function locked_by($group, $perm_name, $item) {
    if ($perm_name != "view") {
      return null;
    }

    // For view permissions, if any parent is Access::DENY, then those parents lock this one.
    // Return
    $lock = ORM::factory("Item", $item->id)
      ->where("left_ptr", "<=", $item->left_ptr)
      ->where("right_ptr", ">=", $item->right_ptr)
      ->with("access_intent")
      ->where("access_intent.view_$group->id", "=", Access::DENY)
      ->order_by("level", "DESC")
      ->limit(1)
      ->find();

    if ($lock->loaded()) {
      return $lock;
    } else {
      return null;
    }
  }

  /**
   * Terminate immediately with an HTTP 403 Forbidden response.
   */
  static function forbidden() {
    throw HTTP_Exception::factory(403);
  }

  /**
   * Internal method to set a permission
   *
   * @param  Model_Group $group
   * @param  string      $perm_name
   * @param  Model_Item  $item
   * @param  boolean     $value
   */
  private static function _set(IdentityProvider_GroupDefinition $group, $perm_name, $album, $value) {
    if (!$album->loaded()) {
      throw new Exception("@todo INVALID_ALBUM $album->id");
    }
    if (!$album->is_album()) {
      throw new Exception("@todo INVALID_ALBUM_TYPE not an album");
    }
    $access = $album->access_intent;
    $access->__set("{$perm_name}_{$group->id}", $value);
    $access->save();

    if ($perm_name == "view") {
      self::_update_access_view_cache($group, $album);
    } else {
      self::_update_access_non_view_cache($group, $perm_name, $album);
    }

    Access::update_htaccess_files($album, $group, $perm_name, $value);
  }

  /**
   * Allow a group to have a permission on an item.
   *
   * @param  Model_Group $group
   * @param  string  $perm_name
   * @param  Model_Item $item
   */
  static function allow($group, $perm_name, $item) {
    self::_set($group, $perm_name, $item, self::ALLOW);
  }

  /**
   * Deny a group the given permission on an item.
   *
   * @param  Model_Group $group
   * @param  string  $perm_name
   * @param  Model_Item $item
   */
  static function deny($group, $perm_name, $item) {
    self::_set($group, $perm_name, $item, self::DENY);
  }

  /**
   * Unset the given permission for this item and use inherited values
   *
   * @param  Model_Group $group
   * @param  string  $perm_name
   * @param  Model_Item $item
   */
  static function reset($group, $perm_name, $item) {
    if ($item->id == 1) {
      throw new Exception("@todo CANT_RESET_ROOT_PERMISSION");
    }
    self::_set($group, $perm_name, $item, self::INHERIT);
  }

  /**
   * Recalculate the permissions for an album's hierarchy.
   */
  static function recalculate_album_permissions($album) {
    foreach (self::_get_all_groups() as $group) {
      foreach (ORM::factory("Permission")->find_all() as $perm) {
        if ($perm->name == "view") {
          self::_update_access_view_cache($group, $album);
        } else {
          self::_update_access_non_view_cache($group, $perm->name, $album);
        }
      }
    }
  }

  /**
   * Recalculate the permissions for a single photo.
   */
  static function recalculate_photo_permissions($photo) {
    $parent = $photo->parent();
    $parent_access_cache = $parent->access_cache;
    $photo_access_cache = $photo->access_cache;
    foreach (self::_get_all_groups() as $group) {
      foreach (ORM::factory("Permission")->find_all() as $perm) {
        $field = "{$perm->name}_{$group->id}";
        if ($perm->name == "view") {
          $photo->$field = $parent->$field;
        } else {
          $photo_access_cache->$field = $parent_access_cache->$field;
        }
      }
    }
    $photo_access_cache->save();
    $photo->save();
  }

  /**
   * Register a permission so that modules can use it.
   *
   * @param  string $name           The internal name for for this permission
   * @param  string $display_name   The internationalized version of the displayable name
   * @return void
  */
  static function register_permission($name, $display_name) {
    $permission = ORM::factory("Permission", $name);
    if ($permission->loaded()) {
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
    $permission = ORM::factory("Permission")->where("name", "=", $name)->find();
    if ($permission->loaded()) {
      $permission->delete();
    }
  }

  /**
   * Add the appropriate columns for a new group
   *
   * @param Model_Group $group
   * @return void
   */
  static function add_group($group) {
    foreach (ORM::factory("Permission")->find_all() as $perm) {
      self::_add_columns($perm->name, $group);
    }
  }

  /**
   * Remove a group's permission columns (usually when it's deleted)
   *
   * @param Model_Group $group
   * @return void
   */
  static function delete_group($group) {
    foreach (ORM::factory("Permission")->find_all() as $perm) {
      self::_drop_columns($perm->name, $group);
    }
  }

  /**
   * Add new access rows when a new item is added.
   *
   * @param Model_Item $item
   * @return void
   */
  static function add_item($item) {
    $access_intent = ORM::factory("AccessIntent", $item->id);
    if ($access_intent->loaded()) {
      throw new Exception("@todo ITEM_ALREADY_ADDED $item->id");
    }
    $access_intent = ORM::factory("AccessIntent");
    $access_intent->item_id = $item->id;
    $access_intent->save();

    // Create a new access cache entry and copy the parents values.
    $access_cache = ORM::factory("AccessCache");
    $access_cache->item_id = $item->id;
    if ($item->id != 1) {
      $parent_access_cache = $item->parent()->access_cache;
      foreach (self::_get_all_groups() as $group) {
        foreach (ORM::factory("Permission")->find_all() as $perm) {
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
   * @param Model_Item $item
   * @return void
   */
  static function delete_item($item) {
    $item->access_intent->delete();
    $item->access_cache->delete();
  }

  /**
   * Verify our Cross Site Request Forgery token is valid, else throw an exception.
   */
  static function verify_csrf() {
    if (Arr::get(Request::current()->post(), "csrf", Request::current()->query("csrf")) !==
        Session::instance()->get("csrf")) {
      Access::forbidden();
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
      $csrf = Random::hash();
      $session->set("csrf", $csrf);
    }
    return $csrf;
  }

  /**
   * Generate an <input> element containing the Cross Site Request Forgery token for this session.
   * @return string
   */
  static function csrf_form_field() {
    return "<input type=\"hidden\" name=\"csrf\" value=\"" . Access::csrf_token() . "\"/>";
  }

  /**
   * Internal method to get all available groups.
   *
   * @return Database_Result
   */
  private static function _get_all_groups() {
    // When we build the gallery package, it's possible that there is no identity provider
    // installed yet.  This is ok at packaging time, so work around it.
   if (Module::is_active(Module::get_var("gallery", "identity_provider", "user"))) {
      return Identity::groups();
    } else {
      return array();
    }
  }

  /**
   * Internal method to  remove Permission/Group columns
   *
   * @param  Model_Group $group
   * @param  string      $perm_name
   * @return void
   */
  private static function _drop_columns($perm_name, $group) {
    $field = "{$perm_name}_{$group->id}";
    $cache_table = $perm_name == "view" ? "items" : "access_caches";
    Database::instance()->query(Database::ALTER, "ALTER TABLE {{$cache_table}} DROP `$field`");
    Database::instance()->query(Database::ALTER, "ALTER TABLE {access_intents} DROP `$field`");
    ORM::factory("Item")->reload_columns(true);
    ORM::factory("AccessIntent")->reload_columns(true);
    ORM::factory("AccessCache")->reload_columns(true);
  }

  /**
   * Internal method to add Permission/Group columns
   *
   * @param  Model_Group $group
   * @param  string  $perm_name
   * @return void
   */
  private static function _add_columns($perm_name, $group) {
    $field = "{$perm_name}_{$group->id}";
    $cache_table = $perm_name == "view" ? "items" : "access_caches";
    $not_null = $cache_table == "items" ? "" : "NOT NULL";
    Database::instance()->query(Database::ALTER,
      "ALTER TABLE {{$cache_table}} ADD `$field` BINARY $not_null DEFAULT FALSE");
    Database::instance()->query(Database::ALTER,
      "ALTER TABLE {access_intents} ADD `$field` BINARY DEFAULT NULL");
    DB::update("access_intents")
      ->set(array($field => Access::DENY))
      ->where("item_id", "=", 1)
      ->execute();
    ORM::factory("Item")->reload_columns(true);
    ORM::factory("AccessIntent")->reload_columns(true);
    ORM::factory("AccessCache")->reload_columns(true);
  }

  /**
   * Update the Access_Cache model based on information from the Access_Intent model for view
   * permissions only.
   *
   * @todo: use database locking
   *
   * @param  Model_Group $group
   * @param  Model_Item $item
   * @return void
   */
  private static function _update_access_view_cache($group, $item) {
    $access = $item->access_intent;
    $field = "view_{$group->id}";

    // With view permissions, deny values in the parent can override allow values in the child,
    // so start from the bottom of the tree and work upwards overlaying negative on top of
    // positive.
    //
    // If the item's intent is ALLOW or DEFAULT, it's possible that some ancestor has specified
    // DENY and this ALLOW cannot be obeyed.  So in that case, back up the tree and find any
    // non-DEFAULT and non-ALLOW parent and propagate from there.  If we can't find a matching
    // item, then its safe to propagate from here.
    if ($access->$field !== Access::DENY) {
      $tmp_item = ORM::factory("Item")
        ->where("left_ptr", "<", $item->left_ptr)
        ->where("right_ptr", ">", $item->right_ptr)
        ->with("access_intent")
        ->where("access_intent.$field", "=", Access::DENY)
        ->order_by("left_ptr", "DESC")
        ->limit(1)
        ->find();
      if ($tmp_item->loaded()) {
        $item = $tmp_item;
      }
    }

    // We will have a problem if we're trying to change a DENY to an ALLOW because the
    // access_caches table will already contain DENY values and we won't be able to overwrite
    // them according the rule above.  So mark every permission below this level as UNKNOWN so
    // that we can tell which permissions have been changed, and which ones need to be updated.
    DB::update("items")
      ->set(array($field => Access::UNKNOWN))
      ->where("left_ptr", ">=", $item->left_ptr)
      ->where("right_ptr", "<=", $item->right_ptr)
      ->execute();

    $query = ORM::factory("AccessIntent")
      ->select("access_intent.$field", "item.left_ptr", "item.right_ptr")
      ->with("item")
      ->where("left_ptr", ">=", $item->left_ptr)
      ->where("right_ptr", "<=", $item->right_ptr)
      ->where("type", "=", "album")
      ->where("access_intent.$field", "IS NOT", Access::INHERIT)
      ->order_by("level", "DESC")
      ->find_all();
    foreach ($query as $row) {
      if ($row->$field == Access::ALLOW) {
        // Propagate ALLOW for any row that is still UNKNOWN.
        DB::update("items")
          ->set(array($field => $row->$field))
          ->where($field, "IS", Access::UNKNOWN) // UNKNOWN is NULL so we have to use IS
          ->where("left_ptr", ">=", $row->left_ptr)
          ->where("right_ptr", "<=", $row->right_ptr)
          ->execute();
      } else if ($row->$field == Access::DENY) {
        // DENY overwrites everything below it
        DB::update("items")
          ->set(array($field => $row->$field))
          ->where("left_ptr", ">=", $row->left_ptr)
          ->where("right_ptr", "<=", $row->right_ptr)
          ->execute();
      }
    }

    // Finally, if our intent is DEFAULT at this point it means that we were unable to find a
    // DENY parent in the hierarchy to propagate from.  So we'll still have a UNKNOWN values in
    // the hierarchy, and all of those are safe to change to ALLOW.
    DB::update("items")
      ->set(array($field => Access::ALLOW))
      ->where($field, "IS", Access::UNKNOWN) // UNKNOWN is NULL so we have to use IS
      ->where("left_ptr", ">=", $item->left_ptr)
      ->where("right_ptr", "<=", $item->right_ptr)
      ->execute();
  }

  /**
   * Update the Access_Cache model based on information from the Access_Intent model for non-view
   * permissions.
   *
   * @todo: use database locking
   *
   * @param  Model_Group $group
   * @param  string  $perm_name
   * @param  Model_Item $item
   * @return void
   */
  private static function _update_access_non_view_cache($group, $perm_name, $item) {
    $access = $item->access_intent;

    $field = "{$perm_name}_{$group->id}";

    // If the item's intent is DEFAULT, then we need to back up the chain to find the nearest
    // parent with an intent and propagate from there.
    //
    // @todo To optimize this, we wouldn't need to propagate from the parent, we could just
    //       propagate from here with the parent's intent.
    if ($access->$field === Access::INHERIT) {
      $tmp_item = ORM::factory("Item")
        ->with("access_intent")
        ->where("left_ptr", "<", $item->left_ptr)
        ->where("right_ptr", ">", $item->right_ptr)
        ->where($field, "IS NOT", Access::UNKNOWN) // UNKNOWN is NULL so we have to use IS NOT
        ->order_by("left_ptr", "DESC")
        ->limit(1)
        ->find();
      if ($tmp_item->loaded()) {
        $item = $tmp_item;
      }
    }

    // With non-view permissions, each level can override any permissions that came above it
    // so start at the top and work downwards, overlaying permissions as we go.
    $query = ORM::factory("AccessIntent")
      ->select("access_intent.$field", "item.left_ptr", "item.right_ptr")
      ->with("item")
      ->where("left_ptr", ">=", $item->left_ptr)
      ->where("right_ptr", "<=", $item->right_ptr)
      ->where($field, "IS NOT", Access::INHERIT)
      ->order_by("level", "ASC")
      ->find_all();
    foreach ($query as $row) {
      $value = ($row->$field === Access::ALLOW) ? true : false;
      DB::update("access_caches")
        ->set(array($field => $value))
        ->where("item_id", "IN",
                DB::select("id")
                ->from("items")
                ->where("left_ptr", ">=", $row->left_ptr)
                ->where("right_ptr", "<=", $row->right_ptr))
        ->execute();
    }
  }

  /**
   * Rebuild the .htaccess files that prevent direct access to albums, resizes and thumbnails.  We
   * call this internally any time we change the view or view_full permissions for guest users.
   * This function is only public because we use it in maintenance tasks.
   *
   * @param  Model_Item   the album
   * @param  Model_Group  the group whose permission is changing
   * @param  string       the permission name
   * @param  string       the new permission value (eg Access::DENY)
   */
  static function update_htaccess_files($album, $group, $perm_name, $value) {
    if ($group->id != Identity::everybody()->id ||
        !($perm_name == "view" || $perm_name == "view_full")) {
      return;
    }

    $dirs = array($album->file_path());
    if ($perm_name == "view") {
      $dirs[] = dirname($album->resize_path());
      $dirs[] = dirname($album->thumb_path());
    }

    $base_url = URL::base(null, true);

    foreach ($dirs as $dir) {
      if ($value === Access::DENY) {
        $fp = fopen("$dir/.htaccess", "w+");
        fwrite($fp, "<IfModule mod_rewrite.c>\n");
        fwrite($fp, "  RewriteEngine On\n");
        fwrite($fp, "  RewriteRule (.*) $base_url [L]\n");
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
    return Module::get_var("gallery", "private_key");
  }

  /**
   * Verify that our htaccess based permission system actually works.  Create a temporary
   * directory containing an .htaccess file that uses mod_rewrite to redirect /verify to
   * /success.  Then request that url.  If we retrieve it successfully, then our redirects are
   * working and our permission system works.
   */
  static function htaccess_works() {
    $success_url = URL::file("var/security_test/success");

    @mkdir(VARPATH . "security_test");
    try {
      if ($fp = @fopen(VARPATH . "security_test/.htaccess", "w+")) {
        fwrite($fp, "Options +FollowSymLinks\n");
        fwrite($fp, "RewriteEngine On\n");
        fwrite($fp, "RewriteRule verify $success_url [L]\n");
        fclose($fp);
      }

      if ($fp = @fopen(VARPATH . "security_test/success", "w+")) {
        fwrite($fp, "success");
        fclose($fp);
      }

      // Proxy our authorization headers so that if the entire Gallery is covered by Basic Auth
      // this callback will still work.
      $headers = array();
      if (function_exists("apache_request_headers")) {
        $arh = apache_request_headers();
        if (!empty($arh["Authorization"])) {
          $headers["Authorization"] = $arh["Authorization"];
        }
      }
      list ($status, $headers, $body) =
        Remote::do_request(URL::abs_file("var/security_test/verify"), HTTP_Request::GET, $headers);
      $works = ($status == "HTTP/1.1 200 OK") && ($body == "success");
    } catch (Exception $e) {
      @System::unlink_dir(VARPATH . "security_test");
      throw $e;
    }
    @System::unlink_dir(VARPATH . "security_test");

    return $works;
  }
}
