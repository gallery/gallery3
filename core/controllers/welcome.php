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
class Welcome_Controller extends Template_Controller {
  public $template = "welcome.html";

  function index() {
    try {
      $session = Session::instance();
    } catch (Exception $e) {
    }

    $this->template->syscheck = new View("welcome_syscheck.html");
    $this->template->syscheck->errors = $this->_get_config_errors();
    $this->template->syscheck->modules = array();

    set_error_handler(array("Welcome_Controller", "_error_handler"));
    try {
      $this->template->syscheck->modules = module::available();
      $this->template->album_count = ORM::factory("item")->where("type", "album")->count_all();
      $this->template->photo_count = ORM::factory("item")->where("type", "photo")->count_all();
      $this->template->deepest_photo = ORM::factory("item")
        ->where("type", "photo")->orderby("level", "desc")->find();
      $this->template->deepest_album = ORM::factory("item")
        ->where("type", "album")->orderby("level", "desc")->find();
      $this->template->album_tree = $this->_load_album_tree();
      $this->template->add_photo_html = $this->_get_add_photo_html();
    } catch (Exception $e) {
      $this->template->album_count = 0;
      $this->template->photo_count = 0;
      $this->template->deepest_photo = null;
      $this->template->album_tree = array();
      $this->template->add_photo_html = "";
    }

    $this->_load_user_info();
    $this->_load_group_info();
    $this->_load_comment_info();
    $this->_load_tag_info();

    restore_error_handler();

    $this->_create_directories();

    if (!empty($session) && $session->get("profiler", false)) {
      $profiler = new Profiler();
      $profiler->render();
    }
  }

  function install($module_name) {
    $to_install = array();
    if ($module_name == "*") {
      foreach (module::available() as $module_name => $info) {
        if (empty($info->installed)) {
          $to_install[] = $module_name;
        }
      }
    } else {
      $to_install[] = $module_name;
    }

    foreach ($to_install as $module_name) {
      if ($module_name != "core") {
        require_once(DOCROOT . "modules/${module_name}/helpers/${module_name}_installer.php");
      }
      module::install($module_name);
    }

    url::redirect("welcome");
  }

  function uninstall($module_name) {
    $clean = true;
    if ($module_name == "core") {
      // We have to uninstall all other modules first, else their tables, etc don't
      // get cleaned up.
      $old_handler = set_error_handler(array("Welcome_Controller", "_error_handler"));
      try {
        foreach (ORM::factory("module")->find_all() as $module) {
          if ($module->name != "core" && $module->version) {
            try {
              call_user_func(array("{$module->name}_installer", "uninstall"));
            } catch (Exception $e) {
              print $e;
            }
          }
        }
        core_installer::uninstall();
      } catch (Exception $e) {
        print $e;
      }


      // Since we're in a state of flux, it's possible that other stuff went wrong with the
      // uninstall, so back off and nuke it from orbit.  It's the only way to be sure.
      $db = Database::instance();
      foreach ($db->list_tables() as $table) {
        $db->query("DROP TABLE `$table`");
      }
      set_error_handler($old_handler);
    } else {
      module::uninstall($module_name);
    }
    url::redirect("welcome");
  }

  function mptt() {
    $this->auto_render = false;
    $items = ORM::factory("item")->orderby("id")->find_all();
    $data = "digraph G {\n";
    foreach ($items as $item) {
      $data .= "  $item->parent_id -> $item->id\n";
      $data .= "  $item->id [label=\"$item->id $item->title <$item->left, $item->right>\"]\n";
    }
    $data .= "}\n";

    if ($this->input->get("type") == "text") {
      print "<pre>$data";
    } else {
      $proc = proc_open("/usr/bin/dot -Tsvg",
                        array(array("pipe", "r"),
                              array("pipe", "w")),
                        $pipes,
                        VARPATH . "tmp");
      fwrite($pipes[0], $data);
      fclose($pipes[0]);

      header("Content-Type: image/svg+xml");
      print(stream_get_contents($pipes[1]));
      fclose($pipes[1]);
      proc_close($proc);
    }
  }

  function i18n($action) {
    $translation_file = VARPATH . "translation.php";

    switch($action) {
    case "build":
      $t = array();
      for ($i = 0; $i < 500; $i++) {
        $t["this is message $i of many"] = "localized version of $i";
      }

      $fp = fopen($translation_file, "wb");
      fwrite($fp, "<? \$t = ");
      fwrite($fp, var_export($t, 1));
      fwrite($fp, ";");
      fclose($fp);
      url::redirect("welcome");
      break;

    case "run":
      Benchmark::start("load_translation");
      include $translation_file;
      Benchmark::stop("load_translation");

      $count = 500;
      Benchmark::start("loop_overhead_$count");
      for ($i = 0; $i < $count; $i++) {
      }
      Benchmark::stop("loop_overhead_$count");

      $count = 500;
      Benchmark::start("translations_$count");
      for ($i = 0; $i < $count; $i++) {
        $value = $t["this is message $i of many"];
      }
      Benchmark::stop("loop_overhead_$count");

      $profiler = new Profiler();
      $this->auto_render = false;
    }
  }

  function add_photos() {
    $path = trim($this->input->post("path"));
    $parent_id = (int)$this->input->post("parent_id");
    $parent = ORM::factory("item", $parent_id);
    if (!$parent->loaded) {
      throw new Exception("@todo BAD_ALBUM");
    }

    cookie::set("add_photos_path", $path);
    $photo_count = 0;
    foreach (glob("$path/*.[Jj][Pp][Gg]") as $file) {
      set_time_limit(30);
      photo::create($parent, $file, basename($file), basename($file));
      $photo_count++;
    }

    if ($photo_count > 0) {
      log::success("content", "(scaffold) Added $photo_count photos",
                   html::anchor("albums/$parent_id", "View album"));
    }

    url::redirect("welcome");
  }

  function add_albums_and_photos($count, $desired_type=null) {
    srand(time());
    $parents = ORM::factory("item")->where("type", "album")->find_all()->as_array();
    $owner_id = module::is_installed("user") ? user::active()->id : null;

    $test_images = glob(APPPATH . "tests/images/*.[Jj][Pp][Gg]");

    $album_count = $photo_count = 0;
    for ($i = 0; $i < $count; $i++) {
      set_time_limit(30);

      $parent = $parents[array_rand($parents)];
      $parent->reload();
      $type = $desired_type;
      if (!$type) {
        $type = rand(0, 10) ? "photo" : "album";
      }
      if ($type == "album") {
        $thumb_size = module::get_var("core", "thumb_size");
        $parents[] = album::create(
          $parent, "rnd_" . rand(), "Rnd $i", "random album $i", $owner_id)
          ->save();
        $album_count++;
      } else {
        $photo_index = rand(0, count($test_images) - 1);
        photo::create($parent, $test_images[$photo_index], basename($test_images[$photo_index]),
                      "rnd_" . rand(), "sample thumb", $owner_id);
        $photo_count++;
      }
    }

    if ($photo_count > 0) {
      log::success("content", "(scaffold) Added $photo_count photos");
    }

    if ($album_count > 0) {
      log::success("content", "(scaffold) Added $album_count albums");
    }
    url::redirect("welcome");
  }

  function random_phrase($count) {
    static $words;
    if (empty($words)) {
      $sample_text = "Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium
        laudantium, totam rem aperiam eaque ipsa, quae ab illo inventore veritatis et quasi
        architecto beatae vitae dicta sunt, explicabo. Nemo enim ipsam voluptatem, quia voluptas
        sit, aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos, qui ratione
        voluptatem sequi nesciunt, neque porro quisquam est, qui dolorem ipsum, quia dolor sit,
        amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt, ut
        labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis
        nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi
        consequatur? Quis autem vel eum iure reprehenderit, qui in ea voluptate velit esse, quam
        nihil molestiae consequatur, vel illum, qui dolorem eum fugiat, quo voluptas nulla
        pariatur?  At vero eos et accusamus et iusto odio dignissimos ducimus, qui blanditiis
        praesentium voluptatum deleniti atque corrupti, quos dolores et quas molestias excepturi
        sint, obcaecati cupiditate non provident, similique sunt in culpa, qui officia deserunt
        mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et
        expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio, cumque
        nihil impedit, quo minus id, quod maxime placeat, facere possimus, omnis voluptas
        assumenda est, omnis dolor repellendus.  Temporibus autem quibusdam et aut officiis
        debitis aut rerum necessitatibus saepe eveniet, ut et voluptates repudiandae sint et
        molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut
        reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores
        repellat.";
      $words = preg_split('/\s+/', $sample_text);
    }

    $chosen = array();
    for ($i = 0; $i < $count; $i++) {
      $chosen[] = $words[array_rand($words)];
    }

    return implode(' ', $chosen);
  }

  function add_comments($count) {
    srand(time());
    $photos = ORM::factory("item")->where("type", "photo")->find_all()->as_array();

    if (empty($photos)) {
      url::redirect("welcome");
    }

    if (module::is_installed("akismet")) {
      akismet::$test_mode = 1;
    }
    for ($i = 0; $i < $count; $i++) {
      $photo = $photos[array_rand($photos)];
      comment::create(
        ucfirst($this->random_phrase(rand(1, 3))),
        "johndoe@example.com",
        $this->random_phrase(rand(8, 500)), $photo->id);
    }

    url::redirect("welcome");
  }

  function add_tags($count) {
    $items = ORM::factory("item")->find_all()->as_array();

    if (!empty($items)) {
      $tags = $this->_generateTags($count);

      while ($count-- > 0) {
        $tag_name = $tags[array_rand($tags)];
        $item = $items[array_rand($items)];

        tag::add($item, $tag_name);
      }
    }

    url::redirect("welcome");
  }

  private function _generateTags($number){
    // Words from lorem2.com
    $words = explode(
      " ",
      "Lorem ipsum dolor sit amet consectetuer adipiscing elit Donec odio Quisque volutpat " .
      "mattis eros Nullam malesuada erat ut turpis Suspendisse urna nibh viverra non " .
      "semper suscipit posuere a pede  Donec nec justo eget felis facilisis " .
      "fermentum Aliquam porttitor mauris sit amet orci Aenean dignissim pellentesque " .
      "felis Morbi in sem quis dui placerat ornare Pellentesque odio nisi euismod in " .
      "pharetra a ultricies in diam Sed arcu Cras consequat Praesent dapibus neque " .
      "id cursus faucibus tortor neque egestas augue eu vulputate magna eros eu " .
      "erat Aliquam erat volutpat Nam dui mi tincidunt quis accumsan porttitor " .
      "facilisis luctus metus Phasellus ultrices nulla quis nibh Quisque a " .
      "lectus Donec consectetuer ligula vulputate sem tristique cursus Nam nulla quam " .
      "gravida non commodo a sodales sit amet nisi Pellentesque fermentum " .
      "dolor Aliquam quam lectus facilisis auctor ultrices ut elementum vulputate " .
      "nunc Sed adipiscing ornare risus Morbi est est blandit sit amet sagittis vel " .
      "euismod vel velit Pellentesque egestas sem Suspendisse commodo ullamcorper " .
      "magna");

    while ($number--) {
      $results[] = $words[array_rand($words, 1)];
    }
    return $results;
  }

  public function session($key) {
    Session::instance()->set($key, $this->input->get("value", false));
    $this->auto_render = false;
    url::redirect("welcome");
  }

  private function _get_config_errors() {
    $errors = array();
    if (!file_exists(VARPATH)) {
      $error = new stdClass();
      $error->message = "Missing: " . VARPATH;
      $error->instructions[] = "mkdir " . VARPATH;
      $error->instructions[] = "chmod 777 " . VARPATH;
      $errors[] = $error;
    } else if (!is_writable(VARPATH)) {
      $error = new stdClass();
      $error->message = "Not writable: " . VARPATH;
      $error->instructions[] = "chmod 777 " . VARPATH;
      $errors[] = $error;
    }

    $db_php = VARPATH . "database.php";
    if (!file_exists($db_php)) {
      $error = new stdClass();
      $error->message = "Missing: $db_php <br/> Run the following commands...";
      $error->instructions[] = "cp " . DOCROOT . "kohana/config/database.php $db_php";
      $error->instructions[] = "chmod 644 $db_php";
      $error->message2 = "...then edit this file and enter your database configuration settings.";
      $errors[] = $error;
    } else if (!is_readable($db_php)) {
      $error = new stdClass();
      $error->message = "Not readable: $db_php";
      $error->instructions[] = "chmod 644 $db_php";
      $error->message2 = "Then edit this file and enter your database configuration settings.";
      $errors[] = $error;
    } else {
      $old_handler = set_error_handler(array("Welcome_Controller", "_error_handler"));
      try {
        Database::instance()->connect();
      } catch (Exception $e) {
        $error = new stdClass();
        $error->message = "Database error: {$e->getMessage()}";
        $db_name = Kohana::config("database.default.connection.database");
        if (strchr($error->message, "Unknown database")) {
          $error->instructions[] = "mysqladmin -uroot create $db_name";
        } else {
          $error->instructions = array();
          $error->message2 = "Check " . VARPATH . "database.php";
        }
        $errors[] = $error;
      }
      set_error_handler($old_handler);
    }

    return $errors;
  }

  function _error_handler($x) {
  }

  function _create_directories() {
    foreach (array("logs", "uploads") as $dir) {
      @mkdir(VARPATH . "$dir");
    }
  }

  private function _load_group_info() {
    if (class_exists("Group_Model")) {
      $this->template->groups = ORM::factory("group")->find_all();
    } else {
      $this->template->groups = array();
    }
  }

  private function _load_user_info() {
    if (class_exists("User_Model")) {
      $this->template->users = ORM::factory("user")->find_all();
    } else {
      $this->template->users = array();
    }
  }

  private function _load_comment_info() {
    if (class_exists("Comment_Model")) {
      $this->template->comment_count = ORM::factory("comment")->count_all();
    } else {
      $this->template->comment_count = 0;
    }
  }

  private function _load_tag_info() {
    if (class_exists("Tag_Model")) {
      $this->template->tag_count = ORM::factory("tag")->count_all();
      $this->template->most_tagged = Database::instance()
        ->select("item_id AS id", "COUNT(tag_id) AS count")
        ->from("items_tags")
        ->groupby("item_id")
        ->orderby("count", "DESC")
        ->limit(1)
        ->get()
        ->current();
    } else {
      $this->template->tag_count = 0;
      $this->template->most_tagged = 0;
    }
  }

  public function add_user() {
    $name = $this->input->post("user_name");
    $isAdmin = (bool)$this->input->post("admin");
    $user = user::create($name, $name, $name);
    if ($isAdmin) {
      $user->admin = true;
      $user->save();
    }
    url::redirect("welcome");
  }

  public function delete_user($id) {
    ORM::factory("user", $id)->delete();
    url::redirect("welcome");
  }

  public function add_group() {
    $name = $this->input->post("group_name");
    group::create($name);
    url::redirect("welcome");
  }

  public function delete_group($id) {
    ORM::factory("group", $id)->delete();
    url::redirect("welcome");
  }

  public function remove_from_group($group_id, $user_id) {
    $group = ORM::factory("group", $group_id);
    $user = ORM::factory("group", $user_id);
    if ($group->loaded && $user->loaded) {
      $group->remove($user);
      $group->save();
    }
    url::redirect("welcome");
  }

  public function add_to_group($user_id) {
    $group_name = $this->input->post("group_name");
    $group = ORM::factory("group")->where("name", $group_name)->find();
    $user = ORM::factory("group", $user_id);
    if ($group->loaded && $user->loaded) {
      $group->add($user);
      $group->save();
    }
    url::redirect("welcome");
  }

  private function _load_album_tree() {
    $tree = array();
    foreach (ORM::factory("item")->where("type", "album")->find_all() as $album) {
      if ($album->parent_id) {
        $tree[$album->parent_id]->children[] = $album->id;
      }
      $tree[$album->id]->album = $album;
      $tree[$album->id]->children = array();
    }

    return $tree;
  }

  public function add_perm($group_id, $perm, $item_id) {
    access::allow(ORM::factory("group", $group_id), $perm, ORM::factory("item", $item_id));
    url::redirect("welcome");
  }

  public function deny_perm($group_id, $perm, $item_id) {
    access::deny(ORM::factory("group", $group_id), $perm, ORM::factory("item", $item_id));
    url::redirect("welcome");
  }

  public function reset_all_perms($group_id, $item_id) {
    $group = ORM::factory("group", $group_id);
    $item = ORM::factory("item", $item_id);
    foreach (ORM::factory("permission")->find_all() as $perm) {
      access::reset($group, $perm->name, $item);
    }
    url::redirect("welcome");
  }

  public function form($arg1, $arg2) {
    if ($arg1 == "add" && $arg2 == "photos") {
      print $this->_get_add_photo_html();
    }
    $this->auto_render = false;
  }

  public function _get_add_photo_html($parent_id=1) {
    $parent = ORM::factory("item", $parent_id);
    return photo::get_add_form($parent);
  }
}
