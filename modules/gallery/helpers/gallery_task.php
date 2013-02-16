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
class gallery_task_Core {
  const FIX_STATE_START_MPTT = 0;
  const FIX_STATE_RUN_MPTT = 1;
  const FIX_STATE_START_ALBUMS = 2;
  const FIX_STATE_RUN_ALBUMS = 3;
  const FIX_STATE_START_DUPE_SLUGS = 4;
  const FIX_STATE_RUN_DUPE_SLUGS = 5;
  const FIX_STATE_START_DUPE_NAMES = 6;
  const FIX_STATE_RUN_DUPE_NAMES = 7;
  const FIX_STATE_START_DUPE_BASE_NAMES = 8;
  const FIX_STATE_RUN_DUPE_BASE_NAMES = 9;
  const FIX_STATE_START_REBUILD_ITEM_CACHES = 10;
  const FIX_STATE_RUN_REBUILD_ITEM_CACHES = 11;
  const FIX_STATE_START_MISSING_ACCESS_CACHES = 12;
  const FIX_STATE_RUN_MISSING_ACCESS_CACHES = 13;
  const FIX_STATE_DONE = 14;

  static function available_tasks() {
    $dirty_count = graphics::find_dirty_images_query()->count_records();
    $tasks = array();
    $tasks[] = Task_Definition::factory()
                 ->callback("gallery_task::rebuild_dirty_images")
                 ->name(t("Rebuild Images"))
                 ->description($dirty_count ?
                               t2("You have one out of date photo",
                                  "You have %count out of date photos",
                                  $dirty_count)
                               : t("All your photos are up to date"))
      ->severity($dirty_count ? log::WARNING : log::SUCCESS);

    $tasks[] = Task_Definition::factory()
                 ->callback("gallery_task::update_l10n")
                 ->name(t("Update translations"))
                 ->description(t("Download new and updated translated strings"))
      ->severity(log::SUCCESS);

    $tasks[] = Task_Definition::factory()
                 ->callback("gallery_task::file_cleanup")
                 ->name(t("Remove old files"))
                 ->description(t("Remove expired files from the logs and tmp directory"))
      ->severity(log::SUCCESS);

    $tasks[] = Task_Definition::factory()
      ->callback("gallery_task::fix")
      ->name(t("Fix your Gallery"))
      ->description(t("Fix a variety of problems that might cause your Gallery to act strangely.  Requires maintenance mode."))
      ->severity(log::SUCCESS);

    return $tasks;
  }

  /**
   * Task that rebuilds all dirty images.
   * @param Task_Model the task
   */
  static function rebuild_dirty_images($task) {
    $errors = array();
    try {
      // Choose the dirty images in a random order so that if we run this task multiple times
      // concurrently each task is rebuilding different images simultaneously.
      $result = graphics::find_dirty_images_query()->select("id")
        ->select(db::expr("RAND() as r"))
        ->order_by("r", "ASC")
        ->execute();
      $total_count = $task->get("total_count", $result->count());
      $mode = $task->get("mode", "init");
      if ($mode == "init") {
        $task->set("total_count", $total_count);
        $task->set("mode", "process");
        batch::start();
      }

      $completed = $task->get("completed", 0);
      $ignored = $task->get("ignored", array());

      $i = 0;

      // If there's no work left to do, skip to the end.  This can happen if we resume a task long
      // after the work got done in some other task.
      if (!$result->count()) {
        $completed = $total_count;
      }

      foreach ($result as $row) {
        if (array_key_exists($row->id, $ignored)) {
          continue;
        }

        $item = ORM::factory("item", $row->id);
        if ($item->loaded()) {
          try {
            graphics::generate($item);
            $completed++;

            $errors[] = t("Successfully rebuilt images for '%title'",
                          array("title" => html::purify($item->title)));
          } catch (Exception $e) {
            $errors[] = t("Unable to rebuild images for '%title'",
                          array("title" => html::purify($item->title)));
            $errors[] = (string)$e;
            $ignored[$item->id] = 1;
          }
        }

        if (++$i == 2) {
          break;
        }
      }

      $task->status = t2("Updated: 1 image. Total: %total_count.",
                         "Updated: %count images. Total: %total_count.",
                         $completed,
                         array("total_count" => $total_count));

      if ($completed < $total_count) {
        $task->percent_complete = (int)(100 * ($completed + count($ignored)) / $total_count);
      } else {
        $task->percent_complete = 100;
      }

      $task->set("completed", $completed);
      $task->set("ignored", $ignored);
      if ($task->percent_complete == 100) {
        $task->done = true;
        $task->state = "success";
        batch::stop();
        site_status::clear("graphics_dirty");
      }
    } catch (Exception $e) {
      Kohana_Log::add("error",(string)$e);
      $task->done = true;
      $task->state = "error";
      $task->status = $e->getMessage();
      $errors[] = (string)$e;
    }
    if ($errors) {
      $task->log($errors);
    }
  }

  static function update_l10n($task) {
    $errors = array();
    try {
      $start = microtime(true);
      $data = Cache::instance()->get("update_l10n_cache:{$task->id}");
      if ($data) {
        list($dirs, $files, $cache, $num_fetched) = unserialize($data);
      }
      $i = 0;

      switch ($task->get("mode", "init")) {
      case "init":  // 0%
        $dirs = array("gallery", "modules", "themes", "installer");
        $files = $cache = array();
        $num_fetched = 0;
        $task->set("mode", "find_files");
        $task->status = t("Finding files");
        break;

      case "find_files":  // 0% - 10%
        while (($dir = array_pop($dirs)) && microtime(true) - $start < 0.5) {
          if (in_array(basename($dir), array("tests", "lib"))) {
            continue;
          }

          foreach (glob(DOCROOT . "$dir/*") as $path) {
            $relative_path = str_replace(DOCROOT, "", $path);
            if (is_dir($path)) {
              $dirs[] = $relative_path;
            } else {
              $files[] = $relative_path;
            }
          }
        }

        $task->status = t2("Finding files: found 1 file",
                           "Finding files: found %count files", count($files));

        if (!$dirs) {
          $task->set("mode", "scan_files");
          $task->set("total_files", count($files));
          $task->status = t("Scanning files");
          $task->percent_complete = 10;
        }
        break;

      case "scan_files": // 10% - 70%
        while (($file = array_pop($files)) && microtime(true) - $start < 0.5) {
          $file = DOCROOT . $file;
          switch (pathinfo($file, PATHINFO_EXTENSION)) {
          case "php":
            l10n_scanner::scan_php_file($file, $cache);
            break;

          case "info":
            l10n_scanner::scan_info_file($file, $cache);
            break;
          }
        }

        $total_files = $task->get("total_files");
        $task->status = t2("Scanning files: scanned 1 file",
                           "Scanning files: scanned %count files", $total_files - count($files));

        $task->percent_complete = 10 + 60 * ($total_files - count($files)) / $total_files;
        if (empty($files)) {
          $task->set("mode", "fetch_updates");
          $task->status = t("Fetching updates");
          $task->percent_complete = 70;
        }
        break;

      case "fetch_updates":  // 70% - 100%
        // Send fetch requests in batches until we're done
        $num_remaining = l10n_client::fetch_updates($num_fetched);
        if ($num_remaining) {
          $total = $num_fetched + $num_remaining;
          $task->percent_complete = 70 + 30 * ((float) $num_fetched / $total);
        } else {
          Gallery_I18n::clear_cache();

          $task->done = true;
          $task->state = "success";
          $task->status = t("Translations installed/updated");
          $task->percent_complete = 100;
        }
      }

      if (!$task->done) {
        Cache::instance()->set("update_l10n_cache:{$task->id}",
                               serialize(array($dirs, $files, $cache, $num_fetched)),
                               array("l10n"));
      } else {
        Cache::instance()->delete("update_l10n_cache:{$task->id}");
      }
    } catch (Exception $e) {
      Kohana_Log::add("error",(string)$e);
      $task->done = true;
      $task->state = "error";
      $task->status = $e->getMessage();
      $errors[] = (string)$e;
    }
    if ($errors) {
      $task->log($errors);
    }
  }

  /**
   * Task that removes old files from var/logs and var/tmp.
   * @param Task_Model the task
   */
  static function file_cleanup($task) {
    $errors = array();
    try {
      $start = microtime(true);
      $data = Cache::instance()->get("file_cleanup_cache:{$task->id}");
      $files = $data ? unserialize($data) : array();
      $i = 0;
      $current = 0;
      $total = 0;

      switch ($task->get("mode", "init")) {
      case "init":
        $threshold = time() - 1209600; // older than 2 weeks
        // Note that this code is roughly duplicated in gallery_event::gallery_shutdown
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
                $files[] = "$dir/$file";
              }
            }
          }
        }
        $task->set("mode", "delete_files");
        $task->set("current", 0);
        $task->set("total", count($files));
        Cache::instance()->set("file_cleanup_cache:{$task->id}", serialize($files),
                               array("file_cleanup"));
        if (count($files) == 0) {
          break;
        }

      case "delete_files":
        $current = $task->get("current");
        $total = $task->get("total");
        while ($current < $total && microtime(true) - $start < 1) {
          @unlink($files[$current]);
          $task->log(t("%file removed", array("file" => $files[$current++])));
        }
        $task->percent_complete = $current / $total * 100;
        $task->set("current", $current);
      }

      $task->status = t2("Removed: 1 file. Total: %total_count.",
                         "Removed: %count files. Total: %total_count.",
                         $current, array("total_count" => $total));

      if ($total == $current) {
        $task->done = true;
        $task->state = "success";
        $task->percent_complete = 100;
      }
    } catch (Exception $e) {
      Kohana_Log::add("error",(string)$e);
      $task->done = true;
      $task->state = "error";
      $task->status = $e->getMessage();
      $errors[] = (string)$e;
    }
    if ($errors) {
      $task->log($errors);
    }
  }

  static function fix($task) {
    $start = microtime(true);

    $total = $task->get("total");
    if (empty($total)) {
      $item_count = db::build()->count_records("items");
      $total = 0;

      // mptt: 2 operations for every item
      $total += 2 * $item_count;

      // album audit (permissions and bogus album covers): 1 operation for every album
      $total += db::build()->where("type", "=", "album")->count_records("items");

      // one operation for each dupe slug, dupe name, dupe base name, and missing access cache
      foreach (array("find_dupe_slugs", "find_dupe_names", "find_dupe_base_names",
                     "find_missing_access_caches") as $func) {
        foreach (self::$func() as $row) {
          $total++;
        }
      }

      // one operation to rebuild path and url caches;
      $total += 1 * $item_count;

      $task->set("total", $total);
      $task->set("state", $state = self::FIX_STATE_START_MPTT);
      $task->set("ptr", 1);
      $task->set("completed", 0);
    }

    $completed = $task->get("completed");
    $state = $task->get("state");

    if (!module::get_var("gallery", "maintenance_mode")) {
      module::set_var("gallery", "maintenance_mode", 1);
    }

    // This is a state machine that checks each item in the database.  It verifies the following
    // attributes for an item.
    // 1. Left and right MPTT pointers are correct
    // 2. The .htaccess permission files for restricted items exist and are well formed.
    // 3. The relative_path_cache and relative_url_cache values are set to null.
    // 4. there are no album_cover_item_ids pointing to missing items
    //
    // We'll do a depth-first tree walk over our hierarchy using only the adjacency data because
    // we don't trust MPTT here (that might be what we're here to fix!).  Avoid avoid using ORM
    // calls as much as possible since they're expensive.
    //
    // NOTE: the MPTT check will only traverse items that have valid parents.  It's possible that
    // we have some tree corruption where there are items with parent ids to non-existent items.
    // We should probably do something about that.
    while ($state != self::FIX_STATE_DONE && microtime(true) - $start < 1.5) {
      switch ($state) {
      case self::FIX_STATE_START_MPTT:
        $task->set("ptr", $ptr = 1);
        $task->set("stack", item::root()->id . ":L");
        $state = self::FIX_STATE_RUN_MPTT;
        break;

      case self::FIX_STATE_RUN_MPTT:
        $ptr = $task->get("ptr");
        $stack = explode(" ", $task->get("stack"));
        list ($id, $ptr_mode) = explode(":", array_pop($stack));
        if ($ptr_mode == "L") {
          $stack[] = "$id:R";
          db::build()
            ->update("items")
            ->set("left_ptr", $ptr++)
            ->where("id", "=", $id)
            ->execute();

          foreach (db::build()
                   ->select(array("id"))
                   ->from("items")
                   ->where("parent_id", "=", $id)
                   ->order_by("left_ptr", "ASC")
                   ->execute() as $child) {
            array_push($stack, "{$child->id}:L");
          }
        } else if ($ptr_mode == "R") {
          db::build()
            ->update("items")
            ->set("right_ptr", $ptr++)
            ->set("relative_path_cache", null)
            ->set("relative_url_cache", null)
            ->where("id", "=", $id)
            ->execute();
        }
        $task->set("ptr", $ptr);
        $task->set("stack", implode(" ", $stack));
        $completed++;

        if (empty($stack)) {
          $state = self::FIX_STATE_START_DUPE_SLUGS;
        }
        break;


      case self::FIX_STATE_START_DUPE_SLUGS:
        $stack = array();
        foreach (self::find_dupe_slugs() as $row) {
          list ($parent_id, $slug) = explode(":", $row->parent_slug, 2);
          $stack[] = join(":", array($parent_id, $slug));
        }
        if ($stack) {
          $task->set("stack", implode(" ", $stack));
          $state = self::FIX_STATE_RUN_DUPE_SLUGS;
        } else {
          $state = self::FIX_STATE_START_DUPE_NAMES;
        }
        break;

      case self::FIX_STATE_RUN_DUPE_SLUGS:
        $stack = explode(" ", $task->get("stack"));
        list ($parent_id, $slug) = explode(":", array_pop($stack));

        // We want to leave the first one alone and update all conflicts to be random values.
        $fixed = 0;
        $conflicts = ORM::factory("item")
          ->where("parent_id", "=", $parent_id)
          ->where("slug", "=", $slug)
          ->find_all(1, 1);
        if ($conflicts->count() && $conflict = $conflicts->current()) {
          $task->log("Fixing conflicting slug for item id {$conflict->id}");
          db::build()
            ->update("items")
            ->set("slug", $slug . "-" . (string)rand(1000, 9999))
            ->where("id", "=", $conflict->id)
            ->execute();

          // We fixed one conflict, but there might be more so put this parent back on the stack
          // and try again.  We won't consider it completed when we don't fix a conflict.  This
          // guarantees that we won't spend too long fixing one set of conflicts, and that we
          // won't stop before all are fixed.
          $stack[] = "$parent_id:$slug";
          break;
        }
        $task->set("stack", implode(" ", $stack));
        $completed++;

        if (empty($stack)) {
          $state = self::FIX_STATE_START_DUPE_NAMES;
        }
        break;

      case self::FIX_STATE_START_DUPE_NAMES:
        $stack = array();
        foreach (self::find_dupe_names() as $row) {
          list ($parent_id, $name) = explode(":", $row->parent_name, 2);
          $stack[] = join(":", array($parent_id, $name));
        }
        if ($stack) {
          $task->set("stack", implode(" ", $stack));
          $state = self::FIX_STATE_RUN_DUPE_NAMES;
        } else {
          $state = self::FIX_STATE_START_DUPE_BASE_NAMES;
        }
        break;

      case self::FIX_STATE_RUN_DUPE_NAMES:
        // NOTE: This does *not* attempt to fix the file system!
        $stack = explode(" ", $task->get("stack"));
        list ($parent_id, $name) = explode(":", array_pop($stack));

        $fixed = 0;
        // We want to leave the first one alone and update all conflicts to be random values.
        $conflicts = ORM::factory("item")
          ->where("parent_id", "=", $parent_id)
          ->where("name", "=", $name)
          ->find_all(1, 1);
        if ($conflicts->count() && $conflict = $conflicts->current()) {
          $task->log("Fixing conflicting name for item id {$conflict->id}");
          if (!$conflict->is_album() && preg_match("/^(.*)(\.[^\.\/]*?)$/", $conflict->name, $matches)) {
            $item_base_name = $matches[1];
            $item_extension = $matches[2]; // includes a leading dot
          } else {
            $item_base_name = $conflict->name;
            $item_extension = "";
          }
          db::build()
            ->update("items")
            ->set("name", $item_base_name . "-" . (string)rand(1000, 9999) . $item_extension)
            ->where("id", "=", $conflict->id)
            ->execute();

          // We fixed one conflict, but there might be more so put this parent back on the stack
          // and try again.  We won't consider it completed when we don't fix a conflict.  This
          // guarantees that we won't spend too long fixing one set of conflicts, and that we
          // won't stop before all are fixed.
          $stack[] = "$parent_id:$name";
          break;
        }
        $task->set("stack", implode(" ", $stack));
        $completed++;

        if (empty($stack)) {
          $state = self::FIX_STATE_START_DUPE_BASE_NAMES;
        }
        break;

      case self::FIX_STATE_START_DUPE_BASE_NAMES:
        $stack = array();
        foreach (self::find_dupe_base_names() as $row) {
          list ($parent_id, $base_name) = explode(":", $row->parent_base_name, 2);
          $stack[] = join(":", array($parent_id, $base_name));
        }
        if ($stack) {
          $task->set("stack", implode(" ", $stack));
          $state = self::FIX_STATE_RUN_DUPE_BASE_NAMES;
        } else {
          $state = self::FIX_STATE_START_ALBUMS;
        }
        break;

      case self::FIX_STATE_RUN_DUPE_BASE_NAMES:
        // NOTE: This *does* attempt to fix the file system!  So, it must go *after* run_dupe_names.
        $stack = explode(" ", $task->get("stack"));
        list ($parent_id, $base_name) = explode(":", array_pop($stack));
        $base_name_escaped = Database::escape_for_like($base_name);

        $fixed = 0;
        // We want to leave the first one alone and update all conflicts to be random values.
        $conflicts = ORM::factory("item")
          ->where("parent_id", "=", $parent_id)
          ->where("name", "LIKE", "{$base_name_escaped}.%")
          ->where("type", "<>", "album")
          ->find_all(1, 1);
        if ($conflicts->count() && $conflict = $conflicts->current()) {
          $task->log("Fixing conflicting name for item id {$conflict->id}");
          if (preg_match("/^(.*)(\.[^\.\/]*?)$/", $conflict->name, $matches)) {
            $item_base_name = $matches[1]; // unlike $base_name, this always maintains capitalization
            $item_extension = $matches[2]; // includes a leading dot
          } else {
            $item_base_name = $conflict->name;
            $item_extension = "";
          }
          // Unlike conflicts found in run_dupe_names, these items are likely to have an intact
          // file system.  Let's use the item save logic to rebuild the paths and rename the files
          // if possible.
          try {
            $conflict->name = $item_base_name . "-" . (string)rand(1000, 9999) . $item_extension;
            $conflict->validate();
            // If we get here, we're safe to proceed with save
            $conflict->save();
          } catch (Exception $e) {
            // Didn't work.  Edit database directly without fixing file system.
            db::build()
              ->update("items")
              ->set("name", $item_base_name . "-" . (string)rand(1000, 9999) . $item_extension)
              ->where("id", "=", $conflict->id)
              ->execute();
          }

          // We fixed one conflict, but there might be more so put this parent back on the stack
          // and try again.  We won't consider it completed when we don't fix a conflict.  This
          // guarantees that we won't spend too long fixing one set of conflicts, and that we
          // won't stop before all are fixed.
          $stack[] = "$parent_id:$base_name";
          break;
        }
        $task->set("stack", implode(" ", $stack));
        $completed++;

        if (empty($stack)) {
          $state = self::FIX_STATE_START_ALBUMS;
        }
        break;

      case self::FIX_STATE_START_ALBUMS:
        $stack = array();
        foreach (db::build()
                 ->select("id")
                 ->from("items")
                 ->where("type", "=", "album")
                 ->execute() as $row) {
          $stack[] = $row->id;
        }
        $task->set("stack", implode(" ", $stack));
        $state = self::FIX_STATE_RUN_ALBUMS;
        break;

      case self::FIX_STATE_RUN_ALBUMS:
        $stack = explode(" ", $task->get("stack"));
        $id = array_pop($stack);

        $item = ORM::factory("item", $id);
        if ($item->album_cover_item_id) {
          $album_cover_item = ORM::factory("item", $item->album_cover_item_id);
          if (!$album_cover_item->loaded()) {
            $item->album_cover_item_id = null;
            $item->save();
          }
        }

        $everybody = identity::everybody();
        $view_col = "view_{$everybody->id}";
        $view_full_col = "view_full_{$everybody->id}";
        $intent = ORM::factory("access_intent")->where("item_id", "=", $id)->find();
        if ($intent->$view_col === access::DENY) {
          access::update_htaccess_files($item, $everybody, "view", access::DENY);
        }
        if ($intent->$view_full_col === access::DENY) {
          access::update_htaccess_files($item, $everybody, "view_full", access::DENY);
        }
        $task->set("stack", implode(" ", $stack));
        $completed++;

        if (empty($stack)) {
          $state = self::FIX_STATE_START_REBUILD_ITEM_CACHES;
        }
        break;

      case self::FIX_STATE_START_REBUILD_ITEM_CACHES:
        $stack = array();
        foreach (self::find_empty_item_caches(500) as $row) {
          $stack[] = $row->id;
        }
        $task->set("stack", implode(" ", $stack));
        $state = self::FIX_STATE_RUN_REBUILD_ITEM_CACHES;
        break;

      case self::FIX_STATE_RUN_REBUILD_ITEM_CACHES:
        $stack = explode(" ", $task->get("stack"));
        if (!empty($stack)) {
          $id = array_pop($stack);
          $item = ORM::factory("item", $id);
          $item->relative_path();  // this rebuilds the cache and saves the item as a side-effect
          $task->set("stack", implode(" ", $stack));
          $completed++;
        }

        if (empty($stack)) {
          // Try refilling the stack
          foreach (self::find_empty_item_caches(500) as $row) {
            $stack[] = $row->id;
          }
          $task->set("stack", implode(" ", $stack));

          if (empty($stack)) {
            $state = self::FIX_STATE_START_MISSING_ACCESS_CACHES;
          }
        }
        break;

      case self::FIX_STATE_START_MISSING_ACCESS_CACHES:
        $stack = array();
        foreach (self::find_missing_access_caches_limited(500) as $row) {
          $stack[] = $row->id;
        }
        $task->set("stack", implode(" ", $stack));
        $state = self::FIX_STATE_RUN_MISSING_ACCESS_CACHES;
        break;

      case self::FIX_STATE_RUN_MISSING_ACCESS_CACHES:
        $stack = array_filter(explode(" ", $task->get("stack"))); // filter removes empty/zero ids
        if (!empty($stack)) {
          $id = array_pop($stack);
          $access_cache = ORM::factory("access_cache");
          $access_cache->item_id = $id;
          $access_cache->save();
          $task->set("stack", implode(" ", $stack));
          $completed++;
        }

        if (empty($stack)) {
          // Try refilling the stack
          foreach (self::find_missing_access_caches_limited(500) as $row) {
            $stack[] = $row->id;
          }
          $task->set("stack", implode(" ", $stack));

          if (empty($stack)) {
            // The new cache rows are there, but they're incorrectly populated so we have to fix
            // them.  If this turns out to be too slow, we'll have to refactor
            // access::recalculate_permissions to allow us to do it in slices.
            access::recalculate_album_permissions(item::root());
            $state = self::FIX_STATE_DONE;
          }
        }
        break;
      }
    }

    $task->set("state", $state);
    $task->set("completed", $completed);

    if ($state == self::FIX_STATE_DONE) {
      $task->done = true;
      $task->state = "success";
      $task->percent_complete = 100;
      module::set_var("gallery", "maintenance_mode", 0);
    } else {
      $task->percent_complete = round(100 * $completed / $total);
    }
    $task->status = t2("One operation complete", "%count / %total operations complete", $completed,
                       array("total" => $total));
  }

  static function find_dupe_slugs() {
    return db::build()
      ->select_distinct(
        array("parent_slug" => db::expr("CONCAT(`parent_id`, ':', LOWER(`slug`))")))
      ->select("id")
      ->select(array("C" => "COUNT(\"*\")"))
      ->from("items")
      ->having("C", ">", 1)
      ->group_by("parent_slug")
      ->execute();
  }

  static function find_dupe_names() {
    // looking for photos, movies, and albums
    return db::build()
      ->select_distinct(
        array("parent_name" => db::expr("CONCAT(`parent_id`, ':', LOWER(`name`))")))
      ->select("id")
      ->select(array("C" => "COUNT(\"*\")"))
      ->from("items")
      ->having("C", ">", 1)
      ->group_by("parent_name")
      ->execute();
  }

  static function find_dupe_base_names() {
    // looking for photos or movies, not albums
    return db::build()
      ->select_distinct(
        array("parent_base_name" => db::expr("CONCAT(`parent_id`, ':', LOWER(SUBSTR(`name`, 1, LOCATE('.', `name`) - 1)))")))
      ->select("id")
      ->select(array("C" => "COUNT(\"*\")"))
      ->from("items")
      ->where("type", "<>", "album")
      ->having("C", ">", 1)
      ->group_by("parent_base_name")
      ->execute();
  }

  static function find_empty_item_caches($limit) {
    return db::build()
      ->select("items.id")
      ->from("items")
      ->where("relative_path_cache", "is", null)
      ->or_where("relative_url_cache", "is", null)
      ->limit($limit)
      ->execute();
  }

  static function find_missing_access_caches() {
    return self::find_missing_access_caches_limited(1 << 16);
  }

  static function find_missing_access_caches_limited($limit) {
    return db::build()
      ->select("items.id")
      ->from("items")
      ->join("access_caches", "items.id", "access_caches.item_id", "left")
      ->where("access_caches.id", "is", null)
      ->limit($limit)
      ->execute();
  }
}