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
class Scaffold_Controller extends Template_Controller {
  public $template = "scaffold.html";

  public function __construct($theme=null) {
    if (!(user::active()->admin)) {
      throw new Exception("@todo UNAUTHORIZED", 401);
    }
    parent::__construct();
  }

  function index() {
    $session = Session::instance();

    set_error_handler(array("Scaffold_Controller", "_error_handler"));
    try {
      $this->template->album_count = ORM::factory("item")->where("type", "album")->count_all();
      $this->template->photo_count = ORM::factory("item")->where("type", "photo")->count_all();
      $this->template->album_tree = $this->_load_album_tree();
      $this->template->add_photo_html = $this->_get_add_photo_html();
    } catch (Exception $e) {
      $this->template->album_count = 0;
      $this->template->photo_count = 0;
      $this->template->deepest_photo = null;
      $this->template->album_tree = array();
      $this->template->add_photo_html = "";
    }

    $this->_load_comment_info();
    $this->_load_tag_info();

    restore_error_handler();

    if (!empty($session) && $session->get("profiler", false)) {
      $profiler = new Profiler();
      $profiler->render();
    }
  }


  function add_photos() {
    $path = trim($this->input->post("path"));
    $parent_id = (int)$this->input->post("parent_id");
    $parent = ORM::factory("item", $parent_id);
    if (!$parent->loaded) {
      throw new Exception("@todo BAD_ALBUM");
    }

    batch::start();
    cookie::set("add_photos_path", $path);
    $photo_count = 0;
    foreach (glob("$path/*.[Jj][Pp][Gg]") as $file) {
      set_time_limit(30);
      photo::create($parent, $file, basename($file), basename($file));
      $photo_count++;
    }
    batch::stop();

    if ($photo_count > 0) {
      log::success("content", "(scaffold) Added $photo_count photos",
                   html::anchor("albums/$parent_id", "View album"));
    }

    url::redirect("scaffold");
  }

  function add_albums_and_photos($count, $desired_type=null) {
    srand(time());
    $parents = ORM::factory("item")->where("type", "album")->find_all()->as_array();
    $owner_id = module::is_installed("user") ? user::active()->id : null;

    $test_images = glob(APPPATH . "tests/images/*.[Jj][Pp][Gg]");

    batch::start();
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
    batch::stop();

    if ($photo_count > 0) {
      log::success("content", "(scaffold) Added $photo_count photos");
    }

    if ($album_count > 0) {
      log::success("content", "(scaffold) Added $album_count albums");
    }
    url::redirect("scaffold");
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
    $users = ORM::factory("user")->find_all()->as_array();

    if (empty($photos)) {
      url::redirect("scaffold");
    }

    if (module::is_installed("akismet")) {
      akismet::$test_mode = 1;
    }
    for ($i = 0; $i < $count; $i++) {
      $photo = $photos[array_rand($photos)];
      $author = $users[array_rand($users)];
      $guest_name = ucfirst($this->random_phrase(rand(1, 3)));
      $guest_email = sprintf("%s@%s.com", $this->random_phrase(1), $this->random_phrase(1));
      $guest_url = sprintf("http://www.%s.com", $this->random_phrase(1));
      comment::create($photo, $author, $this->random_phrase(rand(8, 500)),
                      $guest_name, $guest_email, $guest_url);
    }

    url::redirect("scaffold");
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

    url::redirect("scaffold");
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

  function _error_handler($x) {
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


  public function package() {
    $this->auto_render = false;

    // Cleanly uninstalling and reinstalling within the same request requires us to do the "cache
    // invalidation" cha-cha.  It's a dance of many steps.
    $this->uninstall("core", false);
    module::$module_names = array();
    module::$modules = array();
    Database::instance()->clear_cache();
    $this->install("core", false);
    module::load_modules();
    foreach (array("core", "user", "comment", "info",
                   "rss", "search", "slideshow", "tag") as $module_name) {
      $this->install($module_name, false);
    }
    url::redirect("scaffold/dump_database");
  }

  public function dump_database() {
    $this->auto_render = false;

    // We now have a clean install with just the packages that we want.  Make sure that the
    // database is clean too.
    $db = Database::instance();
    $db->query("TRUNCATE {sessions}");
    $db->query("TRUNCATE {logs}");
    $db->update("users", array("password" => ""), array("id" => 2));

    $dbconfig = Kohana::config('database.default');
    $dbconfig = $dbconfig["connection"];
    $pass = $dbconfig["pass"] ? "-p{$dbconfig['pass']}" : "";
    $sql_file = DOCROOT . "installer/install.sql";
    if (!is_writable($sql_file)) {
      print "$sql_file is not writeable";
      return;
    }
    $command = "mysqldump --compact --add-drop-table -h{$dbconfig['host']} " .
      "-u{$dbconfig['user']} $pass {$dbconfig['database']} > $sql_file";
    exec($command, $output, $status);
    if ($status) {
      print "<pre>";
      print "$command\n";
      print "Failed to dump database\n";
      print implode("\n", $output);
      return;
    }

    // Post-process the sql file to support prefixes
    foreach (file($sql_file) as $line) {
      $buf .= preg_replace("/(CREATE TABLE|IF EXISTS|INSERT INTO) `(\w+)`/", "\\1 {\\2}", $line);
    }
    $fd = fopen($sql_file, "wb");
    fwrite($fd, $buf);
    fclose($fd);

    url::redirect("scaffold/dump_var");
  }

  public function dump_var() {
    $this->auto_render = false;

    $objects = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator(VARPATH),
      RecursiveIteratorIterator::SELF_FIRST);

    $var_file = DOCROOT . "installer/init_var.php";
    if (!is_writable($var_file)) {
      print "$var_file is not writeable";
      return;
    }

    $fd = fopen($var_file, "w");
    fwrite($fd, "<?php defined(\"SYSPATH\") or die(\"No direct script access.\") ?>\n");
    fwrite($fd, "<?php\n");
    foreach($objects as $name => $file){
      if ($file->getBasename() == "database.php") {
        continue;
      } else if (basename($file->getPath()) == "logs") {
        continue;
      }

      if ($file->isDir()) {
        $path = "VARPATH . \"" . substr($name, strlen(VARPATH)) . "\"";
        fwrite($fd, "!file_exists($path) && mkdir($path);\n");
      } else {
        // @todo: serialize non-directories
        print "Unknown file: $name";
        return;
      }
    }
    fclose($fd);
    url::redirect("scaffold");
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
