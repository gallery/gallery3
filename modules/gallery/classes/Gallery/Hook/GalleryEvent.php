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
class Gallery_Hook_GalleryEvent {
  /**
   * Initialization.  This is called after the bootstrap, but before the initial request is
   * generated.  This is used to check the timezone, load the user, and set the standard routes.
   */
  static function gallery_ready() {
    // Check for missing timezone setting.
    if (!get_cfg_var("date.timezone")) {
      if (!(rand() % 4)) {
        Log::instance()->add(Log::ERROR, "date.timezone setting not detected in " .
                        get_cfg_var("cfg_file_path") . " falling back to UTC.  " .
                        "Consult http://php.net/manual/function.get-cfg-var.php for help.");
      }
    }

    // Load the active user.
    Identity::load_user();

    // Set our routes.  This will match all valid Gallery URLs (including the empty root URL).
    // These are ordered from lowest to highest priority (opposite of standard Kohana)
    // @see  Gallery_Route::set(), which overrides Kohana_Route::set()
    Route::set("item", "(<item_url>)", array("item_url" => "[A-Za-z0-9-_/]++"))
      ->defaults(array(
          "controller" => "items",
          "action" => "show"
        ));

    Route::set("site", "<controller>(/<action>(/<args>))")
      ->filter(function($route, $params, $request) {
          if (!class_exists("Controller_" . $params["controller"])) {
            // No controller found - abort match so we can try to find an item URL.
            return false;
          }
          return $params;
        })
      ->defaults(array(
          "action" => "index"
        ));

    Route::set("combined", "<controller>/<key>", array("controller" => "combined", "key" => ".*"))
      ->defaults(array(
          "action" => "index"
        ));

    Route::set("admin", "<directory>(/<controller>(/<action>(/<args>)))", array("directory" => "admin"))
      ->defaults(array(
          "controller" => "dashboard",
          "action" => "index"
        ));

    $rel_varpath = substr(VARPATH, strlen(DOCROOT), -1);  // i.e. "var" or "var/test"
    Route::set("file_proxy", "$rel_varpath(/<type>(/<path>))", array("path" => ".*"))
      ->defaults(array(
          "controller" => "file_proxy",
          "action" => "index"
        ));
  }

  /**
   * Initialization after the initial request is generated.
   */
  static function initial_request_ready() {
    // Don't keep a session for robots; it's a waste of database space.
    if (Request::user_agent("robot")) {
      Session::instance()->destroy();
    }

    Theme::load_themes();
    Locales::set_request_locale();
  }

  /**
   * Shutdown.  This is run after PHP finishes running our script, whether it ends in error or not.
   */
  static function gallery_shutdown() {
    // Every 500th request, do a pass over var/logs and var/tmp and delete old files.
    // Limit ourselves to deleting a single file so that we don't spend too much CPU
    // time on it.  As long as servers call this at least twice a day they'll eventually
    // wind up with a clean var/logs directory because we only create 1 file a day there.
    // var/tmp might be stickier because theoretically we could wind up spamming that
    // dir with a lot of files.  But let's start with this and refine as we go.
    if (!(rand() % 500)) {
      // Note that this code is roughly duplicated in Hook_GalleryTask::file_cleanup
      $threshold = time() - 1209600; // older than 2 weeks
      foreach(array("logs", "tmp") as $dir) {
        $dir = VARPATH . $dir;
        if ($dh = opendir($dir)) {
          while (($file = readdir($dh)) !== false) {
            if ($file[0] == ".") {
              continue;
            }

            // Ignore directories for now, but we should really address them in the long term.
            if (is_dir("$dir/$file")) {
              continue;
            }

            if (filemtime("$dir/$file") <= $threshold) {
              unlink("$dir/$file");
              break;
            }
          }
        }
      }
    }
    // Delete all files marked using System::delete_later.
    System::delete_marked_files();
  }

  /**
   * Setup the relationships between Model_Item, Model_AccessIntent, and Model_AccessCache.
   */
  static function model_relationships($rels) {
    $rels["item"]["has_one"]["access_intent"] = array();
    $rels["item"]["has_one"]["access_cache"] = array();
    $rels["item"]["belongs_to"]["parent"] = array("model" => "Item", "column" => "parent_id");
    $rels["access_intent"]["belongs_to"]["item"] = array();
    $rels["access_cache"]["belongs_to"]["item"] = array();
  }

  static function user_deleted($user) {
    $admin = Identity::admin_user();
    if (!empty($admin)) {          // could be empty if there is not identity provider
      DB::update("tasks")
        ->set(array("owner_id" => $admin->id))
        ->where("owner_id", "=", $user->id)
        ->execute();
      DB::update("items")
        ->set(array("owner_id" => $admin->id))
        ->where("owner_id", "=", $user->id)
        ->execute();
      DB::update("logs")
        ->set(array("user_id" => $admin->id))
        ->where("user_id", "=", $user->id)
        ->execute();
    }
  }

  static function identity_provider_changed($old_provider, $new_provider) {
    $admin = Identity::admin_user();
    DB::update("tasks")
      ->set(array("owner_id" => $admin->id))
      ->execute();
    DB::update("items")
      ->set(array("owner_id" => $admin->id))
      ->execute();
    DB::update("logs")
      ->set(array("user_id" => $admin->id))
      ->execute();
    Module::set_var("gallery", "email_from", $admin->email);
    Module::set_var("gallery", "email_reply_to", $admin->email);
  }

  static function group_created($group) {
    Access::add_group($group);
  }

  static function group_deleted($group) {
    Access::delete_group($group);
  }

  static function item_created($item) {
    Access::add_item($item);

    // Build our thumbnail/resizes.
    try {
      Graphics::generate($item);
    } catch (Exception $e) {
      GalleryLog::error("graphics", t("Couldn't create a thumbnail or resize for %item_title",
                               array("item_title" => $item->title)),
                 HTML::anchor($item->relative_url(), t("details")));
      Log::instance()->add(Log::ERROR, $e->getMessage() . "\n" . $e->getTraceAsString());
    }

    if ($item->is_photo() || $item->is_movie()) {
      // If the parent has no cover item, make this it.
      $parent = $item->parent;
      if (Access::can("edit", $parent) && $parent->album_cover_item_id == null)  {
        Item::make_album_cover($item);
      }
    }
  }

  static function item_deleted($item) {
    Access::delete_item($item);

    // Find any other albums that had the deleted item as the album cover and null it out.
    // In some cases this may leave us with a missing album cover up in this item's parent
    // hierarchy, but in most cases it'll work out fine.
    foreach (ORM::factory("Item")
             ->where("album_cover_item_id", "=", $item->id)
             ->find_all() as $parent) {
      Item::remove_album_cover($parent);
    }

    $parent = $item->parent;
    if (!$parent->album_cover_item_id) {
      // Assume that we deleted the album cover
      if (Batch::in_progress()) {
        // Remember that this parent is missing an album cover, for later.
        $batch_missing_album_cover = Session::instance()->get("batch_missing_album_cover", array());
        $batch_missing_album_cover[$parent->id] = 1;
        Session::instance()->set("batch_missing_album_cover", $batch_missing_album_cover);
      } else {
        // Choose the first viewable child as the new cover.
        $child = $parent->children->viewable()->find();
        if ($child->loaded()) {
          Item::make_album_cover($child);
        }
      }
    }
  }

  static function item_updated_data_file($item) {
    Graphics::generate($item);

    // Update any places where this is the album cover
    foreach (ORM::factory("Item")
             ->where("album_cover_item_id", "=", $item->id)
             ->find_all() as $target) {
      $target->thumb_dirty = 1;
      $target->save();
      Graphics::generate($target);
    }
  }

  static function batch_complete() {
    // Set the album covers for any items that where we probably deleted the album cover during
    // this batch.  The item may have been deleted, so don't count on it being around.  Choose the
    // first child as the new album cover.
    // NOTE: if the first child doesn't have an album cover, then this won't work.
    foreach (array_keys(Session::instance()->get("batch_missing_album_cover", array())) as $id) {
      $item = ORM::factory("Item", $id);
      if ($item->loaded() && !$item->album_cover_item_id) {
        $child = $item->children->find();
        if ($child->loaded()) {
          Item::make_album_cover($child);
        }
      }
    }
    Session::instance()->delete("batch_missing_album_cover");
  }

  static function item_moved($item, $old_parent) {
    if ($item->is_album()) {
      Access::recalculate_album_permissions($item->parent);
    } else {
      Access::recalculate_photo_permissions($item);
    }

    // If the item's old ancestors used the item as its cover, change it.
    foreach (array_merge($old_parent->parents, array($old_parent)) as $old_ancestor) {
      if ($old_ancestor->album_cover_item_id == $item->id) {
        $new_cover_item = $old_ancestor->children->limit(1)->find();
        if ($new_cover_item->loaded()) {
          Item::make_album_cover($new_cover_item);
        } else {
          Item::remove_album_cover($old_ancestor);
        }
      }
    }

    // If the new parent doesn't have an album cover, make this it.
    if (!$item->parent->album_cover_item_id) {
      Item::make_album_cover($item);
    }
  }

  static function user_login($user) {
    // If this user is an admin, check to see if there are any post-install tasks that we need
    // to run and take care of those now.
    if ($user->admin && Module::get_var("gallery", "choose_default_tookit", null)) {
      Graphics::choose_default_toolkit();
      Module::clear_var("gallery", "choose_default_tookit");
    }
  }

  static function item_index_data($item, $data) {
    $data[] = $item->description;
    $data[] = $item->name;
    $data[] = $item->title;
  }

  static function user_menu($menu, $theme) {
    if ($theme->page_subtype != "login") {
      $user = Identity::active_user();
      if ($user->guest) {
        $menu->append(Menu::factory("dialog")
                      ->id("user_menu_login")
                      ->css_id("g-login-link")
                      ->url(URL::site("login"))
                      ->label(t("Login")));
      } else {
        $csrf = Access::csrf_token();
        $menu->append(Menu::factory("link")
                      ->id("user_menu_edit_profile")
                      ->css_id("g-user-profile-link")
                      ->view("gallery/login_current_user.html")
                      ->url(UserProfile::url($user->id))
                      ->label($user->display_name()));

        if (Theme::$is_admin) {
          $continue_url = URL::abs_site("");
        } else if ($item = $theme->item()) {
          if (Access::user_can(Identity::guest(), "view", $theme->item)) {
            $continue_url = $item->abs_url();
          } else {
            $continue_url = Item::root()->abs_url();
          }
        } else {
          $continue_url = Request::current()->url(true);
        }

        $menu->append(Menu::factory("link")
                      ->id("user_menu_logout")
                      ->css_id("g-logout-link")
                      ->url(URL::site("logout?csrf=$csrf&amp;continue_url=" . urlencode($continue_url)))
                      ->label(t("Logout")));
      }
    }
  }

  static function site_menu($menu, $theme, $item_css_selector) {
    if ($theme->page_subtype != "login") {
      $menu->append(Menu::factory("link")
                    ->id("home")
                    ->label(t("Home"))
                    ->url(Item::root()->url()));


      $item = $theme->item();

      if (!empty($item)) {
        $can_edit = $item && Access::can("edit", $item);
        $can_add = $item && Access::can("add", $item);

        if ($can_add) {
          $menu->append($add_menu = Menu::factory("submenu")
                        ->id("add_menu")
                        ->label(t("Add")));
          $is_album_writable =
            is_writable($item->is_album() ? $item->file_path() : $item->parent->file_path());
          if ($is_album_writable) {
            $add_menu->append(Menu::factory("dialog")
                              ->id("add_photos_item")
                              ->label(t("Add photos"))
                              ->url(URL::site("items/add/$item->id")));
            if ($item->is_album()) {
              $add_menu->append(Menu::factory("dialog")
                                ->id("add_album_item")
                                ->label(t("Add an album"))
                                ->url(URL::site("items/add_album/$item->id")));
            }
          } else {
            Message::warning(t("The album '%album_name' is not writable.",
                               array("album_name" => $item->title)));
          }
        }

        switch ($item->type) {
        case "album":
          $option_text = t("Album options");
          $edit_text = t("Edit album");
          $delete_text = t("Delete album");
          break;
        case "movie":
          $option_text = t("Movie options");
          $edit_text = t("Edit movie");
          $delete_text = t("Delete movie");
          break;
        default:
          $option_text = t("Photo options");
          $edit_text = t("Edit photo");
          $delete_text = t("Delete photo");
        }

        $menu->append($options_menu = Menu::factory("submenu")
                      ->id("options_menu")
                      ->label($option_text));
        if ($item && ($can_edit || $can_add)) {
          if ($can_edit) {
            $options_menu->append(Menu::factory("dialog")
                                  ->id("edit_item")
                                  ->label($edit_text)
                                  ->url(URL::site("items/edit/$item->id")));
          }

          if ($item->is_album()) {
            if ($can_edit) {
              $options_menu->append(Menu::factory("dialog")
                                    ->id("edit_permissions")
                                    ->label(t("Edit permissions"))
                                    ->url(URL::site("permissions/browse/$item->id")));
            }
          }
        }

        $csrf = Access::csrf_token();
        $page_type = $theme->page_type();
        if ($can_edit && $item->is_photo() && Graphics::can("rotate")) {
          $options_menu
            ->append(
              Menu::factory("ajax_link")
              ->id("rotate_ccw")
              ->label(t("Rotate 90° counter clockwise"))
              ->css_class("ui-icon-rotate-ccw")
              ->ajax_handler("function(data) { " .
                             "\$.gallery_replace_image(data, \$('$item_css_selector')) }")
              ->url(URL::site("items/rotate/$item->id/ccw?csrf=$csrf")))
            ->append(
              Menu::factory("ajax_link")
              ->id("rotate_cw")
              ->label(t("Rotate 90° clockwise"))
              ->css_class("ui-icon-rotate-cw")
              ->ajax_handler("function(data) { " .
                             "\$.gallery_replace_image(data, \$('$item_css_selector')) }")
              ->url(URL::site("items/rotate/$item->id/cw?csrf=$csrf")));
        }

        if (!$item->is_root()) {
          $parent = $item->parent;
          if (Access::can("edit", $parent)) {
            // We can't make this item the highlight if it's an album with no album cover, or if it's
            // already the album cover.
            if (($item->type == "album" && empty($item->album_cover_item_id)) ||
                ($item->type == "album" && $parent->album_cover_item_id == $item->album_cover_item_id) ||
                $parent->album_cover_item_id == $item->id) {
              $disabledState = "ui-state-disabled";
            } else {
              $disabledState = "";
            }

            if ($item->parent->id != 1) {
              $options_menu
                ->append(
                  Menu::factory("ajax_link")
                  ->id("make_album_cover")
                  ->label(t("Choose as the album cover"))
                  ->css_class("ui-icon-star $disabledState")
                  ->ajax_handler("function(data) { window.location.reload() }")
                  ->url(URL::site("items/make_album_cover/$item->id?csrf=$csrf")));
            }
            $options_menu
              ->append(
                Menu::factory("dialog")
                ->id("delete")
                ->label($delete_text)
                ->css_class("ui-icon-trash")
                ->url(URL::site("items/delete/$item->id")));
          }
        }
      }

      if (Identity::active_user()->admin) {
        $menu->append($admin_menu = Menu::factory("submenu")
                ->id("admin_menu")
                ->label(t("Admin")));
        Module::event("admin_menu", $admin_menu, $theme);

        $settings_menu = $admin_menu->get("settings_menu");
        uasort($settings_menu->elements, array("Menu", "title_comparator"));
      }
    }
  }

  static function admin_menu($menu, $theme) {
    $menu
      ->append(Menu::factory("link")
               ->id("dashboard")
               ->label(t("Dashboard"))
               ->url(URL::site("admin")))
      ->append(Menu::factory("submenu")
               ->id("settings_menu")
               ->label(t("Settings"))
               ->append(Menu::factory("link")
                        ->id("graphics_toolkits")
                        ->label(t("Graphics"))
                        ->url(URL::site("admin/graphics")))
               ->append(Menu::factory("link")
                        ->id("movies_settings")
                        ->label(t("Movies"))
                        ->url(URL::site("admin/movies")))
               ->append(Menu::factory("link")
                        ->id("languages")
                        ->label(t("Languages"))
                        ->url(URL::site("admin/languages")))
               ->append(Menu::factory("link")
                        ->id("advanced")
                        ->label(t("Advanced"))
                        ->url(URL::site("admin/advanced_settings"))))
      ->append(Menu::factory("link")
               ->id("modules")
               ->label(t("Modules"))
               ->url(URL::site("admin/modules")))
      ->append(Menu::factory("submenu")
               ->id("content_menu")
               ->label(t("Content")))
      ->append(Menu::factory("submenu")
               ->id("appearance_menu")
               ->label(t("Appearance"))
               ->append(Menu::factory("link")
                        ->id("themes")
                        ->label(t("Theme choice"))
                        ->url(URL::site("admin/themes")))
               ->append(Menu::factory("link")
                        ->id("theme_options")
                        ->label(t("Theme options"))
                        ->url(URL::site("admin/theme_options")))
               ->append(Menu::factory("link")
                        ->id("sidebar")
                        ->label(t("Manage sidebar"))
                        ->url(URL::site("admin/sidebar"))))
      ->append(Menu::factory("submenu")
               ->id("statistics_menu")
               ->label(t("Statistics")))
      ->append(Menu::factory("link")
               ->id("maintenance")
               ->label(t("Maintenance"))
               ->url(URL::site("admin/maintenance")));
    return $menu;
  }

  static function context_menu($menu, $theme, $item, $thumb_css_selector) {
    $menu->append($options_menu = Menu::factory("submenu")
                  ->id("options_menu")
                  ->label(t("Options"))
                  ->css_class("ui-icon-carat-1-n"));

    $page_type = $theme->page_type();
    if (Access::can("edit", $item)) {
      switch ($item->type) {
      case "movie":
        $edit_title = t("Edit this movie");
        $delete_title = t("Delete this movie");
        break;

      case "album":
        $edit_title = t("Edit this album");
        $delete_title = t("Delete this album");
        break;

      default:
        $edit_title = t("Edit this photo");
        $delete_title = t("Delete this photo");
        break;
      }
      $cover_title = t("Choose as the album cover");

      $csrf = Access::csrf_token();

      $theme_item = $theme->item();
      $options_menu->append(Menu::factory("dialog")
                            ->id("edit")
                            ->label($edit_title)
                            ->css_class("ui-icon-pencil")
                            ->url(URL::site("items/edit/$item->id?from_id={$theme_item->id}")));

      if ($item->is_photo() && Graphics::can("rotate")) {
        $options_menu
          ->append(
            Menu::factory("ajax_link")
            ->id("rotate_ccw")
            ->label(t("Rotate 90° counter clockwise"))
            ->css_class("ui-icon-rotate-ccw")
            ->ajax_handler("function(data) { " .
                           "\$.gallery_replace_image(data, \$('$thumb_css_selector')) }")
            ->url(URL::site("items/rotate/$item->id/ccw?csrf=$csrf&amp;from_id={$theme_item->id}")))
          ->append(
            Menu::factory("ajax_link")
            ->id("rotate_cw")
            ->label(t("Rotate 90° clockwise"))
            ->css_class("ui-icon-rotate-cw")
            ->ajax_handler("function(data) { " .
                           "\$.gallery_replace_image(data, \$('$thumb_css_selector')) }")
            ->url(URL::site("items/rotate/$item->id/cw?csrf=$csrf&amp;from_id={$theme_item->id}")));
      }

      $parent = $item->parent;
      if (Access::can("edit", $parent)) {
        // We can't make this item the highlight if it's an album with no album cover, or if it's
        // already the album cover.
        if (($item->type == "album" && empty($item->album_cover_item_id)) ||
            ($item->type == "album" && $parent->album_cover_item_id == $item->album_cover_item_id) ||
            $parent->album_cover_item_id == $item->id) {
          $disabledState = "ui-state-disabled";
        } else {
          $disabledState = "";
        }
        if ($item->parent->id != 1) {
          $options_menu
            ->append(Menu::factory("ajax_link")
                     ->id("make_album_cover")
                     ->label($cover_title)
                     ->css_class("ui-icon-star $disabledState")
                     ->ajax_handler("function(data) { window.location.reload() }")
                     ->url(URL::site("items/make_album_cover/$item->id?csrf=$csrf")));
        }
        $options_menu
          ->append(Menu::factory("dialog")
                   ->id("delete")
                   ->label($delete_title)
                   ->css_class("ui-icon-trash")
                   ->url(URL::site("items/delete/$item->id?from_id={$theme_item->id}")));
      }

      if ($item->is_album()) {
        $options_menu
          ->append(Menu::factory("dialog")
                   ->id("add_item")
                   ->label(t("Add a photo"))
                   ->css_class("ui-icon-plus")
                   ->url(URL::site("items/add/$item->id")))
          ->append(Menu::factory("dialog")
                   ->id("add_album")
                   ->label(t("Add an album"))
                   ->css_class("ui-icon-note")
                   ->url(URL::site("items/add_album/$item->id")))
          ->append(Menu::factory("dialog")
                   ->id("edit_permissions")
                   ->label(t("Edit permissions"))
                   ->css_class("ui-icon-key")
                   ->url(URL::site("permissions/browse/$item->id")));
      }
    }
  }

  static function show_user_profile($data) {
    $v = new View("gallery/user_profile_info.html");

    $fields = array("name" => t("Name"), "locale" => t("Language Preference"),
                    "email" => t("Email"), "full_name" => t("Full name"), "url" => t("Web site"));
    if (!$data->user->guest) {
      $fields = array("name" => t("Name"), "full_name" => t("Full name"), "url" => t("Web site"));
    }
    $v->user_profile_data = array();
    foreach ($fields as $field => $label) {
      if (!empty($data->user->$field)) {
        $value = $data->user->$field;
        if ($field == "locale") {
          $value = Locales::display_name($value);
        } else if ($field == "url") {
          $value = HTML::mark_clean(HTML::anchor(HTML::clean($data->user->$field)));
        }
        $v->user_profile_data[(string) $label] = $value;
      }
    }
    $data->content[] = (object) array("title" => t("User information"), "view" => $v);

  }

  static function user_updated($original_user, $updated_user) {
    // If the default from/reply-to email address is set to the install time placeholder value
    // of unknown@unknown.com then adopt the value from the first admin to set their own email
    // address so that we at least have a valid address for the Gallery.
    if ($updated_user->admin) {
      $email = Module::get_var("gallery", "email_from", "");
      if ($email == "unknown@unknown.com") {
        Module::set_var("gallery", "email_from", $updated_user->email);
        Module::set_var("gallery", "email_reply_to", $updated_user->email);
      }
    }
  }
}
