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
class Gallery_ORM extends Kohana_ORM {
  protected $_changed_through = array();

  /**
   * Stores relationship information for ORM models.
   * @var array
   */
  protected static $_relationship_cache = null;

  /**
   * Merge in a series of where clause tuples and call where() on each one.
   * @chainable
   */
  public function merge_where($tuples) {
    if ($tuples) {
      foreach ($tuples as $tuple) {
        $this->where($tuple[0], $tuple[1], $tuple[2]);
      }
    }
    return $this;
  }

  /**
   * Merge in a series of where clause tuples and call or_where() on each one.
   * @chainable
   */
  public function merge_or_where($tuples) {
    if ($tuples) {
      foreach ($tuples as $tuple) {
        $this->or_where($tuple[0], $tuple[1], $tuple[2]);
      }
    }
    return $this;
  }

  /**
   * Merge in a series of order by column-direction pairs and call order_by() on each one.
   * @chainable
   */
  public function merge_order_by($pairs) {
    if ($pairs) {
      foreach ($pairs as $column => $direction) {
        $this->order_by($column, $direction);
      }
    }
    return $this;
  }

  /**
   * Overload ORM::__initialize() to set the model's object name and relationships.
   *
   * We set the object name using our method, which is then used to set the object plural name and
   * the table name.  Kohana finds it by getting the lowercase version of the model name.  The
   * problem is that this isn't sensitive to camelcase, which breaks how Gallery's DB is set up.
   * By setting $this->_object_name ahead of time, the parent function will use our value instead.
   * Note: can give odd results if you use a series of capital letters (see examples below).
   *
   * Examples: "Model_Item"                --> "item"
   *           "Model_IncomingTranslation" --> "incoming_translation", not "incomingtranslation"
   *           "Model_ORM_Example"         --> "orm_example"
   *           "Model_ORMExample"          --> "ormexample"
   *           "Model_ORMAnotherExample"   --> "ormanother_example", not "ormanotherexample"
   *           "Model_AnotherORMExample"   --> "another_ormexample", not "anotherormexample"
   *
   * Then, we populate the relationships cache by running the "model_relationships" event then
   * use the cache to define the relationships of new model instances.  Since the cache is static,
   * it only needs to be populated once.  Note that we only populate (and use) the cache once we've
   * run Module::load_modules().  This means that it's not easy to add relationships to Model_Module
   * or Model_Var using this method since they're used in the Module class to load the modules in
   * the first place.  Similar to the object name, we set the foreign model names using our method.
   *
   * Example: a module wants to establish a "has_many through" relationship between its foo model
   * and the item model using the pivot table "items_foo_bars".  In the module's event hook, we have:
   *   function model_relationships($relationships) {
   *     $relationships["item"]["has_many"]["foo_bars"] = array("through" => "items_foo_bars");
   *     $relationships["foo_bar"]["has_many"]["items"] = array("through" => "items_foo_bars");
   *   }
   * This will map "Model_Item" and "Model_FooBar" with object names "item" and "foo_bar" to DB
   * tables "items" and "foo_bars" with a DB pivot table "items_foo_bars" that has columns "item_id"
   * and "foo_bar_id".  For more information, see Kohana's ORM relationships documentation.
   *
   * @see ORM::_initialize()
   */
  protected function _initialize() {
    // Set the object name using Inflector::convert_class_to_module_name() if not already defined.
    // This ensures "Model_FooBar" is called "foo_bar" instead of "foobar".
    if (empty($this->_object_name)) {
      $this->_object_name = Inflector::convert_class_to_module_name(substr(get_class($this), 6));
    }

    foreach (array("belongs_to", "has_many", "has_one") as $type) {
      if (isset(ORM::$_relationship_cache[$this->_object_name]) &&
          !empty(ORM::$_relationship_cache[$this->_object_name][$type])) {
        // Relationship found - add it to the model instance.
        $this->{"_$type"} = (array) ORM::$_relationship_cache[$this->_object_name][$type];
      }
    }

    parent::_initialize();
  }

  /**
   * Discover all inter-module relationships.  We will do this whenever the active module set
   * changes, which happens once at start time or if we activate a module during our request.  If
   * called with a module parameter, run incrementally to fold that module's model relationships
   * into the existing set.  This is useful when we're installing a module but it's not yet
   * active and cannot be reached by Module::event()
   *
   * @param   string $module_name
   */
  static function load_relationships($module_name=null) {
    // Run the "model_relationships" event and populate the relationship cache.
    $relationships = new ArrayObject();
    if ($module_name) {
      $class = "Hook_" . Inflector::convert_module_to_class_name($module_name) . "Event";
      if (method_exists($class, "model_relationships")) {
        call_user_func_array(array($class, "model_relationships"), array($relationships));
      }
    } else {
      Module::event("model_relationships", $relationships);
    }
    $relationships = (array) $relationships;

    // Set the "model" parameter using Inflector::convert_module_to_class_name() if not already
    // defined.  This ensures "foo_bar" is called "FooBar" instead of "Foo_Bar".  The
    // $relationships array is not very big, so this nested loop is less ugly than it seems.
    foreach ($relationships as $model => &$types) {
      foreach ($types as $type => &$aliases) {
        foreach ($aliases as $alias => &$details) {
          if (empty($details["model"])) {
            $details["model"] = ($type == "has_many") ?
              Inflector::convert_module_to_class_name(Inflector::singular($alias)) :
              Inflector::convert_module_to_class_name($alias);
          }
        }
      }
    }

    if ($module_name) {
      ORM::$_relationship_cache = Arr::merge(ORM::$_relationship_cache, $relationships);
    } else {
      ORM::$_relationship_cache = $relationships;
    }
  }


  /**
   * Implements the "delete_through" argument of a has_many relationship, which removes the pivot
   * table rows corresponding to the model at the same time as the model itself.  By default,
   * Kohana's ORM does not do this, so the pivot table (defined by "through") remains populated
   * with rows corresponding to deleted models.
   *
   * Example: users can belong to one or more groups, related by the pivot table "groups_users".
   * In the user module event hook, where the relationship is defined, we have:
   *   function model_relationships($relationships) {
   *     $relationships["user"]["has_many"]["groups"] =
   *       array("through" => "groups_users", "delete_through" => true);
   *     $relationships["group"]["has_many"]["users"] =
   *       array("through" => "groups_users", "delete_through" => true);
   *   }
   * Now, when either a group or user is deleted, all rows in "groups_users" are also deleted.
   *
   * @see ORM::delete()
   */
  public function delete() {
    if (!empty($this->_has_many)) {
      foreach ($this->_has_many as $alias => $details) {
        if (!empty($details["delete_through"])) {
          $this->remove($alias);
        }
      }
    }
    return parent::delete();
  }

  /**
   * Overload ORM::add() to add the related model(s) to changed_through if track_changed
   * is true (default).  We do this after the parent function so any thrown exceptions
   * stop it from getting added.
   * @see ORM::add()
   */
  public function add($alias, $far_keys, $track_changed=true) {
    parent::add($alias, $far_keys);
    $this->_add_changed_through($alias, $far_keys, $track_changed);
    return $this;
  }

  /**
   * Overload ORM::remove() to add the related model(s) to changed_through if track_changed
   * is true (default).  We do this after the parent function so any thrown exceptions
   * stop it from getting added.
   * @see ORM::remove()
   */
  public function remove($alias, $far_keys=null, $track_changed=true) {
    parent::remove($alias, $far_keys);
    $this->_add_changed_through($alias, $far_keys, $track_changed);
    return $this;
  }

  /**
   * Return an array of the objects of a has_many through relationship that were just added/removed.
   * If clear is true (default), this will also clear the list.  We clear it here (as opposed to in
   * update()/create()/clear()/reload() similar to changed()) since the list reflects changes in the
   * pivot (or "through") table and not necessarily in the model itself.
   */
  public function changed_through($alias, $clear=true) {
    if (!empty($this->_changed_through[$alias])) {
      $changed = $this->_changed_through[$alias];
      if ($clear) {
        $this->_changed_through[$alias] = array();
      }
      return $changed;
    }
    return array();
  }

  /**
   * Implements the "track_changed_through" argument of a has_many relationship, which adds
   * added/removed objects to the changed_through array.  This is called by the add() and
   * remove() overrides above.
   */
  protected function _add_changed_through($alias, $far_keys=null, $track_changed=true) {
    if ($track_changed && !empty($this->_has_many[$alias]["track_changed_through"])) {
      if ($far_keys instanceof ORM) {
        // It's a model - add it.
        $this->_changed_through[$alias][$far_keys->pk()] = $far_keys;
      } else if (isset($far_keys)) {
        // It's one or more keys - initialize the models and add them.
        foreach ((array) $far_keys as $key) {
          $this->_changed_through[$alias][$key] =
            ORM::factory($this->_has_many[$alias]["model"], $key);
        }
      } else {
        // It's null - add *all* models of the related type.
        $this->_changed_through[$alias] = $this->{$alias}->find_all();
      }
    }
  }

  /**
   * Reset any ORM initialization that's happened so that we can start over.  We use this in the
   * testing framework when we switch from the main database over to the test database.
   */
  static function reinitialize() {
    ORM::$_init_cache = array();
    ORM::$_column_cache = array();
    ORM::$_relationship_cache = new ArrayObject();
  }

  /**
   * Similar to ORM::reload_columns, except wipe out the ORM::_column_cache as well so that we
   * get the fresh set of columns from the database.  This is used by the permission code which
   * alters the database to add and remove columns at run time.
   *
   * @chainable
   * @param   boolean $force Force reloading
   * @return  ORM
   */
  public function reload_columns($force=false) {
    if ($force) {
      unset(ORM::$_column_cache[$this->_object_name]);
    }
    return parent::reload_columns($force);
  }
}
