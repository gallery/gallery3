<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2012 Bharat Mediratta
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
class Folder_Sync_Controller extends Admin_Controller {
  public function browse($id) {
    $paths = unserialize(module::get_var("folder_sync", "authorized_paths"));
    foreach (array_keys($paths) as $path) {
      $files[] = $path;
    }

    $item = ORM::factory("item", $id);
    $view = new View("folder_sync_tree_dialog.html");
    $view->item = $item;
    $view->tree = new View("folder_sync_tree.html");
    $view->tree->files = $files;
    $view->tree->parents = array();
    print $view;
  }

  public function children() {
    $path = Input::instance()->get("path");

    $tree = new View("folder_sync_tree.html");
    $tree->files = array();
    $tree->parents = array();

    // Make a tree with the parents back up to the authorized path, and all the children under the
    // current path.
    if (folder_sync::is_valid_path($path)) {
      $tree->parents[] = $path;
      while (folder_sync::is_valid_path(dirname($tree->parents[0])."/")) {
        array_unshift($tree->parents, dirname($tree->parents[0])."/");
      }
      
      if(folder_sync::is_too_deep($path))
        continue;

      $glob_path = str_replace(array("{", "}", "[", "]"), array("\{", "\}", "\[", "\]"), $path);
      foreach (glob("$glob_path*") as $file) {
        if (!is_readable($file)) {
          continue;
        }
        if (!is_dir($file)) {
          $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
          if (!in_array($ext, array("gif", "jpeg", "jpg", "png", "flv", "mp4", "m4v"))) {
            continue;
          }
        }
        else
          $file .= "/";

        $tree->files[] = $file;
      }
    } else {
      // Missing or invalid path; print out the list of authorized path
      $paths = unserialize(module::get_var("folder_sync", "authorized_paths"));
      foreach (array_keys($paths) as $path) {
        $tree->files[] = $path;
      }
    }
    print $tree;
  }

  function cron()
  {
    $owner_id = 2;
    
    // Login as Admin
    $session = Session::instance();
    $session->delete("user");
    auth::login(IdentityProvider::instance()->admin_user());
 
    // check if some folders are still unprocessed from previous run
    $entry = ORM::factory("folder_sync_entry")
      ->where("is_directory", "=", 1)
      ->where("checked", "=", 0)
      ->order_by("id", "ASC")
      ->find();
    if (!$entry->loaded())
    {
      //print "Starting from scratch\n";
      // Add all folders
      $paths = unserialize(module::get_var("folder_sync", "authorized_paths"));
      foreach (array_keys($paths) as $path) {
        if (folder_sync::is_valid_path($path)) {
          $path = rtrim($path, "/");
          //print "Adding start folder: $path\n";

          $entry = ORM::factory("folder_sync_entry")
            ->where("is_directory", "=", 1)
            ->where("path", "=", $path)
            ->find();
           
          if($entry && $entry->loaded())
          {
            $entry->checked = 0;
            $entry->save();
            //print "Reusing entry\n";
          }
          else
          {
            //print "Adding new entry\n";
            $entry = ORM::factory("folder_sync_entry");
            $entry->path = $path;
            $entry->is_directory = 1;
            $entry->parent_id = null;
            $entry->item_id = 1;
            $entry->md5 = '';
            $entry->save();
          }
        }
      }
    }
    else
    {
      //print "Continue where left off\n";
    }

    // Scan and add files
    $done = false;
    $limit = 2;
    while(!$done && $limit > 0) {
      $entry = ORM::factory("folder_sync_entry")
        ->where("is_directory", "=", 1)
        ->where("checked", "=", 0)
        ->order_by("id", "ASC")
        ->find();

      // get the parrent
      $parent = ORM::factory("item", $entry->item_id);
      
      if ($entry->loaded()) {
        $child_paths = glob(preg_quote($entry->path) . "/*");
        if (!$child_paths) {
          $child_paths = glob("{$entry->path}/*");
        }
        foreach ($child_paths as $child_path) {
          //print "Checking $child_path\n";
          $name = basename($child_path);
          $title = item::convert_filename_to_title($name);

          if (is_dir($child_path)) {
            //print "It's directory\n";

            // check if album imported
            $entry_exists = ORM::factory("folder_sync_entry")
              ->where("is_directory", "=", 1)
              ->where("path", "=", $child_path)
              ->find();

            //$album_exists = ORM::factory("item")->where("type", "=", "album")
              //  ->where("name", "=", $name)
              //  ->where("parent_id", "=", $parent->id)
              //  ->find();
            //$album_exists = null;

            //print "check if we already imported ...";
            if($entry_exists && $entry_exists->loaded()) {
              //print "yes\n";
              //print "Rechecking {$entry_exists->path}\n";
              $entry_exists->checked = 0;
              $entry_exists->save();
            } else {
              //print "no\n";

              //print "Added ITEM entry\n";
              $album = ORM::factory("item");
              $album->type = "album";
              $album->parent_id = $parent->id;
              $album->name = $name;
              $album->title = $title;
              $album->owner_id = $owner_id;
              $album->sort_order = $parent->sort_order;
              $album->sort_column = $parent->sort_column;
              $album->save();

              //print "Added FOLDER_SYNC_ENTRY entry\n";
              $child_entry = ORM::factory("folder_sync_entry");
              $child_entry->path = $child_path;
              $child_entry->parent_id = $entry->id;
              $child_entry->item_id = $album->id;
              $child_entry->is_directory = 1;
              $child_entry->md5 = "";
              $child_entry->save();
            }
          } else {
            $ext = strtolower(pathinfo($child_path, PATHINFO_EXTENSION));
            if (!in_array($ext, legal_file::get_extensions()) || !filesize($child_path))
            {
              // Not importable, skip it.
              continue;
            }
            
            // check if file was already imported
            $entry_exists = ORM::factory("folder_sync_entry")
              ->where("is_directory", "=", 0)
              ->where("path", "=", $child_path)
              ->find();

            if($entry_exists && $entry_exists->loaded())
            {
              //print "Found image record ... ";
              if(empty($entry_exists->added) || empty($entry_exists->md5) || $entry_exists->added != filemtime($child_path) || $entry_exists->md5 != md5_file($child_path))
              {
                //print "updating\n";
                $item = ORM::factory("item", $entry_exists->item_id);
                $item->set_data_file($child_path);
                $item->save();
              }
              else
              {
                //print "NOT updating\n";
              }
            }
            else
            {
              if (in_array($ext, legal_file::get_photo_extensions())) {
                $item = ORM::factory("item");
                $item->type = "photo";
                $item->parent_id = $parent->id;
                $item->set_data_file($child_path);
                $item->name = $name;
                $item->title = $title;
                $item->owner_id = $owner_id;
                $item->save();
              } else if (in_array($ext, legal_file::get_movie_extensions())) {
                $item = ORM::factory("item");
                $item->type = "movie";
                $item->parent_id = $parent->id;
                $item->set_data_file($child_path);
                $item->name = $name;
                $item->title = $title;
                $item->owner_id = $owner_id;
                $item->save();
              }

              $entry_exists = ORM::factory("folder_sync_entry");
              $entry_exists->path = $child_path;
              $entry_exists->parent_id = $entry->id;  // null if the parent was a staging dir
              $entry_exists->is_directory = 0;
              $entry_exists->md5 = md5_file($child_path);
              $entry_exists->added = filemtime($child_path);
              $entry_exists->item_id = $item->id;
              $entry_exists->save();

              $limit--;
            }
          }
          if($limit <= 0)
            break;
        }

        // We've processed this entry unless we reached a limit.
        if($limit > 0)
        {
          //print "Limit is NOT hit\n";
          $entry->checked = 1;
          $entry->save();
        }
        else
        {
          //print "Limit is HIT\n";
        }
      } else {
        //print "Nothing found ...\n";
        $done = true;
      }
      //print "\nNext cycle ...\n\n";
    }
    //print "Done!!!";
    exit;

    /*$done = false;
    while(!$done) {
      $entries = ORM::factory("folder_sync_entry")
        ->where("item_id", "IS", null)
        ->order_by("id", "ASC")
        ->limit(10)
        ->find_all();
      if ($entries->count() == 0) {
        $done = true;
      }

      foreach ($entries as $entry) {
        $parent_entry = ORM::factory("folder_sync_entry", $entry->parent_id);
        if (!$parent_entry->loaded()) {
          $parent = ORM::factory("item", 1);
        } else {
          $parent = ORM::factory("item", $parent_entry->item_id);
        }

        $name = basename($entry->path);
        $title = item::convert_filename_to_title($name);
        if ($entry->is_directory) {

        } else {
          try {
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            $entry_exists = 0;
            if(module::get_var("folder_sync", "skip_duplicates")) {
              $entry_exists = ORM::factory("folder_sync_entry")
                ->where("is_directory", "=", 0)
                ->where("item_id", "IS NOT", null)
                ->where("path", "=", $entry->path)
                ->find();
            }
            if ($entry_exists && $entry_exists->loaded()) {
              // skip adding an image
              if(module::get_var("folder_sync", "process_updates")) {
                if(empty($entry_exists->md5) || empty($entry_exists->added) || $entry_exists->added != filemtime($entry->path))
                {
                  $md5 = md5_file($entry->path);
                  if(empty($entry_exists->md5) || empty($entry_exists->added) || $entry_exists->md5 != $md5) {
                    $item = ORM::factory("item", $entry_exists->item_id);
                    if($item->loaded()) {
                      $item->set_data_file($entry->path);
                      $item->save();
                      $entry_exists->md5 = $md5;
                      $entry_exists->added = filemtime($entry->path);
                      $entry_exists->save();
                    }
                  }
                }
              }
              $entry->item_id = 0;
              // This should never happen, because we don't add stuff to the list that we can't
              // process.  But just in, case.. set this to a non-null value so that we skip this
              // entry.
              $entry->item_id = 0;
            }
          } catch (Exception $e) {
            // This can happen if a photo file is invalid, like a BMP masquerading as a .jpg
            $entry->item_id = 0;
          }
        }
        $entry->save();
      }
    }*/
  }
}
