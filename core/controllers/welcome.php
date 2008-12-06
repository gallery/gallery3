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
    Session::instance();

    $this->template->syscheck = new View("welcome_syscheck.html");
    $this->template->syscheck->errors = $this->_get_config_errors();
    $this->template->syscheck->modules = array();

    $old_handler = set_error_handler(array("Welcome_Controller", "_error_handler"));

    try {
      $this->template->syscheck->modules = $this->_read_modules();
      $this->template->album_count = ORM::factory("item")->where("type", "album")->count_all();
      $this->template->photo_count = ORM::factory("item")->where("type", "photo")->count_all();
      $this->template->deepest_photo = ORM::factory("item")
        ->where("type", "photo")->orderby("level", "desc")->find();
      $this->template->album_tree = $this->_load_album_tree();
      $this->template->rearrange_html = new View("rearrange.html");
    } catch (Exception $e) {
      $this->template->album_count = 0;
      $this->template->photo_count = 0;
      $this->template->deepest_photo = null;
      $this->template->album_tree = array();
      $this->template->rearrange_html = "";
    }
    $this->template->add_photo_html = $this->_get_add_photo_html();

    $this->_load_user_info();
    $this->_load_group_info();
    $this->_load_comment_info();
    $this->_load_tag_info();

    set_error_handler($old_handler);

    $this->_create_directories();

    if (Session::instance()->get("profiler", false)) {
      $profiler = new Profiler();
      $profiler->render();
    }
  }

  function install($module_name) {
    $to_install = array();
    if ($module_name == "*") {
      foreach ($this->_read_modules() as $module_name => $version) {
        if (empty($version)) {
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
      $modules = Kohana::config('core.modules');
      $modules[] = MODPATH . $module_name;
      Kohana::config_set('core.modules', $modules);

      Kohana::log("debug", "${module_name}_install (initial)");
      call_user_func(array("${module_name}_installer", "install"));
    }

    url::redirect("welcome");
  }

  function uninstall($module_name) {
    $clean = true;
    if ($module_name == "core") {
      // We have to uninstall all other modules first, else their tables, etc don't
      // get cleaned up.
      try {
        foreach (ORM::factory("module")->find_all() as $module) {
          if ($module->name != "core" && $module->version) {
            try {
              call_user_func(array("{$module->name}_installer", "uninstall"));
            } catch (Exception $e) {
              $clean = false;
            }
          }
        }
      } catch (Exception $e) { }
      if (!$clean) {
        // Since we're in a state of flux, it's possible that other stuff went wrong with the
        // uninstall, so back off and nuke things from orbit.
        $db = Database::instance();
        foreach ($db->list_tables() as $table) {
          $db->query("DROP TABLE `$table`");
        }
      }
    }
    call_user_func(array("{$module_name}_installer", "uninstall"));
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
    $path = $this->input->post("path");
    cookie::set("add_photos_path", $path);

    foreach (glob("$path/*.[Jj][Pp][Gg]") as $file) {
      set_time_limit(30);
      photo::create(1, $file, basename($file), basename($file));
    }
    url::redirect("welcome");
  }

  function add_albums_and_photos($count, $desired_type=null) {
    srand(time());
    $parents = ORM::factory("item")->where("type", "album")->find_all()->as_array();

    try {
      $user = Session::instance()->get("user");
      $owner_id = $user ? $user->id : ORM::factory("user")->find()->id;
    } catch (Exception $e) {
      $owner_id = null;
    }

    for ($i = 0; $i < $count; $i++) {
      set_time_limit(30);

      $parent = $parents[array_rand($parents)];
      $type = $desired_type;
      if (!$type) {
        $type = rand(0, 10) ? "photo" : "album";
      }
      if ($type == "album") {
        $parents[] = album::create(
          $parent->id, "rnd_" . rand(), "Rnd $i", "random album $i", $owner_id)
          ->set_thumbnail(DOCROOT . "core/tests/test.jpg", 200, 150)
          ->save();
      } else {
        photo::create($parent->id, DOCROOT . "themes/default/images/thumbnail.jpg",
                      "thumbnail.jpg", "rnd_" . rand(), "sample thumbnail", $owner_id);
      }
    }
    url::redirect("welcome");
  }

  function add_comments($count) {
    $photos = ORM::factory("item")->where("type", "photo")->find_all()->as_array();

    $sample_text = "Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium
      doloremque laudantium, totam rem aperiam eaque ipsa, quae ab illo inventore veritatis et quasi
      architecto beatae vitae dicta sunt, explicabo. Nemo enim ipsam voluptatem, quia voluptas
      sit, aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos, qui ratione
      voluptatem sequi nesciunt, neque porro quisquam est, qui dolorem ipsum, quia dolor sit,
      amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt, ut
      labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum
      exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi
      consequatur? Quis autem vel eum iure reprehenderit, qui in ea voluptate velit esse, quam
      nihil molestiae consequatur, vel illum, qui dolorem eum fugiat, quo voluptas nulla pariatur?
      At vero eos et accusamus et iusto odio dignissimos ducimus, qui blanditiis praesentium
      voluptatum deleniti atque corrupti, quos dolores et quas molestias excepturi sint, obcaecati
      cupiditate non provident, similique sunt in culpa, qui officia deserunt mollitia animi, id
      est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam
      libero tempore, cum soluta nobis est eligendi optio, cumque nihil impedit, quo minus id,
      quod maxime placeat, facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.
      Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet,
      ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic
      tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut
      perferendis doloribus asperiores repellat.";

    if (empty($photos)) {
      url::redirect("welcome");
    }

    for ($i = 0; $i < $count; $i++) {
      $photo = $photos[array_rand($photos)];
      comment::create("John Doe", "johndoe@example.com",
        substr($sample_text, 0, rand(30, strlen($sample_text))), $photo->id,
        time() - rand(0, 2 * comment::SECONDS_IN_A_YEAR));
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
    foreach (array("logs") as $dir) {
      @mkdir(VARPATH . "$dir");
    }
  }

  /**
   * Create an array of all the modules that are install or available and the version number
   * @return array(moduleId => version)
   */
  private function _read_modules() {
    $modules = module::available();
    try {
      foreach (module::installed() as $installed_module) {
        $modules[$installed_module->name] = $installed_module->version;
      }
    } catch (Exception $e) {
      // The database may not be installed
    }
    ksort($modules);
    return $modules;
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
    $admin = (bool)$this->input->post("admin");
    user::create($name, $name, $name, $admin);
    url::redirect("welcome");
  }

  public function delete_user($id) {
    user::delete($id);
    url::redirect("welcome");
  }

  public function add_group() {
    $name = $this->input->post("group_name");
    group::create($name);
    url::redirect("welcome");
  }

  public function delete_group($id) {
    group::delete($id);
    url::redirect("welcome");
  }

  public function remove_from_group($group_id, $user_id) {
    group::remove_user($group_id, $user_id);
    url::redirect("welcome");
  }

  public function add_to_group($user_id) {
    $group_name = $this->input->post("group_name");
    $group = ORM::factory("group")->where("name", $group_name)->find();
    if ($group->loaded) {
      group::add_user($group->id, $user_id);
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
    access::allow($group_id, $perm, $item_id);
    url::redirect("welcome");
  }

  public function deny_perm($group_id, $perm, $item_id) {
    access::deny($group_id, $perm, $item_id);
    url::redirect("welcome");
  }

  public function reset_all_perms($group_id, $item_id) {
    foreach (ORM::factory("permission")->find_all() as $perm) {
      access::reset($group_id, $perm->name, $item_id);
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
