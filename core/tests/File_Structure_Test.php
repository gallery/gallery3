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
class File_Structure_Test extends Unit_Test_Case {
  public function no_trailing_closing_php_tag_test() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));
    foreach ($dir as $file) {
      if (!preg_match("|\.html\.php$|", $file->getPathname())) {
        $this->assert_false(
          preg_match('/\?\>\s*$/', file_get_contents($file)),
          "{$file->getPathname()} ends in ?>");
      }
    }
  }

  public function view_files_correct_suffix_test() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));
    foreach ($dir as $file) {
      if (strpos($file, "views")) {
        $this->assert_true(
          preg_match("#/views/.*?(\.html|mrss|txt)\.php$#", $file->getPathname()),
          "{$file->getPathname()} should end in .{html,mrss,txt}.php");
      }
    }
  }

  public function no_windows_line_endings_test() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));
    foreach ($dir as $file) {
      if (preg_match("/\.(php|css|html|js)$/", $file)) {
        foreach (file($file) as $line) {
          $this->assert_true(substr($line, -2) != "\r\n", "$file has windows style line endings");
        }
      }
    }
  }

  private function _check_view_preamble($path, &$errors) {
    // The preamble for views is a single line that prevents direct script access
    if (strpos($path, SYSPATH) === 0) {
      // Kohana preamble
      $expected = "<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>\n";
    } else {
      // Gallery preamble
      // @todo use the same preamble for both!
      $expected = "<?php defined(\"SYSPATH\") or die(\"No direct script access.\") ?>\n";
    }

    $fp = fopen($path, "r");
    $actual = fgets($fp);
    fclose($fp);

    if ($expected != $actual) {
      $errors[] = "$path:1\n  expected:\n\t$expected\n  actual:\n\t$actual";
    }
  }

  private function _check_php_preamble($path, &$errors) {
    if (strpos($path, SYSPATH) === 0 ||
        strpos($path, MODPATH . "unit_test") === 0) {
      // Kohana: we only care about the first line
      $fp = fopen($path, "r");
      $actual = array(fgets($fp));
      fclose($fp);
      $expected = array("<?php defined('SYSPATH') OR die('No direct access allowed.');\n");
    } else if (strpos($path, MODPATH . "forge") === 0 ||
               strpos($path, MODPATH . "exif/lib") === 0 ||
               $path == MODPATH . "user/lib/PasswordHash.php" ||
               $path == DOCROOT . "var/database.php") {
      // 3rd party module security-only preambles, similar to Gallery's
      $expected = array("<?php defined(\"SYSPATH\") or die(\"No direct script access.\");\n");
      $fp = fopen($path, "r");
      $actual = array(fgets($fp));
      fclose($fp);
    } else {
      // Gallery: we care about the entire copyright
      $actual = $this->_get_preamble($path);
      $expected = array(
        "<?php defined(\"SYSPATH\") or die(\"No direct script access.\");",
        "/**",
        " * Gallery - a web based photo album viewer and editor",
        " * Copyright (C) 2000-2009 Bharat Mediratta",
        " *",
        " * This program is free software; you can redistribute it and/or modify",
        " * it under the terms of the GNU General Public License as published by",
        " * the Free Software Foundation; either version 2 of the License, or (at",
        " * your option) any later version.",
        " *",
        " * This program is distributed in the hope that it will be useful, but",
        " * WITHOUT ANY WARRANTY; without even the implied warranty of",
        " * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU",
        " * General Public License for more details.",
        " *",
        " * You should have received a copy of the GNU General Public License",
        " * along with this program; if not, write to the Free Software",
        " * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.",
        " */",
      );
    }
    if ($expected != $actual) {
      $errors[] = "$path:1\n  expected\n\t" . join("\n\t", $expected) .
        "\n  actual:\n\t" . join("\n\t", $actual);
    }
  }

  public function code_files_start_with_preamble_test() {
    $dir = new PhpCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));

    $errors = array();
    foreach ($dir as $file) {
      $path = $file->getPathname();
      switch ($path) {
      case DOCROOT . "installer/database_config.php":
      case DOCROOT . "installer/init_var.php":
        // Special case views
        $this->_check_view_preamble($path, $errors);
        break;

      case DOCROOT . "index.php":
      case DOCROOT . "installer/index.php":
        // Front controllers
        break;

      default:
        if (strpos($path, DOCROOT . "var/logs") === 0) {
          continue;
        } else if (preg_match("/views/", $path)) {
          $this->_check_view_preamble($path, $errors);
        } else {
          $this->_check_php_preamble($path, $errors);
        }
      }
    }

    if ($errors) {
      $this->assert_false(true, "Preamble errors:\n" . join("\n", $errors));
    }
  }

  public function no_tabs_in_our_code_test() {
    $dir = new PhpCodeFilterIterator(
      new GalleryCodeFilterIterator(
        new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator(DOCROOT))));
    foreach ($dir as $file) {
      $this->assert_false(
        preg_match('/\t/', file_get_contents($file)),
        "{$file->getPathname()} has tabs in it");
    }
  }

  private function _get_preamble($file) {
    $lines = file($file);
    $copy = array();
    for ($i = 0; $i < count($lines); $i++) {
      $copy[] = rtrim($lines[$i]);
      if (!strncmp($lines[$i], ' */', 3)) {
        return $copy;
      }
    }
    return $copy;
  }

  public function helpers_are_static_test() {
    $dir = new PhpCodeFilterIterator(
      new GalleryCodeFilterIterator(
        new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator(DOCROOT))));
    foreach ($dir as $file) {
      if (basename(dirname($file)) == "helpers") {
        foreach (file($file) as $line) {
          $this->assert_true(
            !preg_match("/\sfunction\s.*\(/", $line) ||
            preg_match("/^\s*(private static function _|static function)/", $line),
            "should be \"static function foo\" or \"private static function _foo\":\n" .
            "$file\n$line\n");
        }
      }
    }
  }
}

class PhpCodeFilterIterator extends FilterIterator {
  public function accept() {
    $path_name = $this->getInnerIterator()->getPathName();
    return (substr($path_name, -4) == ".php" &&
            !(strpos($path_name, VARPATH) === 0));
  }
}

class GalleryCodeFilterIterator extends FilterIterator {
  public function accept() {
    // Skip anything that we didn"t write
    $path_name = $this->getInnerIterator()->getPathName();
    return !(
      strpos($path_name, ".svn") ||
      strpos($path_name, "core/views/kohana_profiler.php") !== false ||
      strpos($path_name, DOCROOT . "test") !== false ||
      strpos($path_name, DOCROOT . "var") !== false ||
      strpos($path_name, MODPATH . "forge") !== false ||
      strpos($path_name, MODPATH . "gallery_unit_test/views/kohana_error_page.php") !== false ||
      strpos($path_name, MODPATH . "gallery_unit_test/views/kohana_unit_test.php") !== false ||
      strpos($path_name, MODPATH . "unit_test") !== false ||
      strpos($path_name, MODPATH . "exif/lib") !== false ||
      strpos($path_name, MODPATH . "user/libraries/PasswordHash") !== false ||
      strpos($path_name, DOCROOT . "lib/swfupload") !== false ||
      strpos($path_name, SYSPATH) !== false ||
      substr($path_name, -1, 1) == "~");
  }
}
