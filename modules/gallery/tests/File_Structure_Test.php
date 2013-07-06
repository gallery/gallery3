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
class File_Structure_Test extends Unittest_TestCase {
  public function test_no_trailing_closing_php_tag() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));
    $count = 0;
    foreach ($dir as $file) {
      $count++;
      if (!preg_match("/\bviews\b/", $file->getPathname())) {
        $this->assertNotRegExp(
          '/\?\>\s*$/', file_get_contents($file), "{$file->getPathname()} ends in ?>");
      }
    }

    $this->assertTrue($count > 500, "We should have analyzed at least this 500 files");
    $this->assertTrue($count < 1000, "We shouldn't be shipping 1000 files!");
  }

  public function test_view_files_correct_suffix() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));
    foreach ($dir as $file) {
      if (strpos($file, "views/kohana/error.php")) {
        continue;
      }

      if (strpos($file, "views")) {
        $this->assertRegExp(
          "#/views/.*?\.(html|mrss|txt|json)\.php$#", $file->getPathname(),
          "{$file->getPathname()} should end in .{html,mrss,txt,json}.php");
      }
    }
  }

  public function test_no_windows_line_endings() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));
    foreach ($dir as $file) {
      if (preg_match("/\.(php|css|html|js)$/", $file)) {
        foreach (file($file) as $line) {
          $this->assertTrue(substr($line, -2) != "\r\n", "$file has windows style line endings");
        }
      }
    }
  }

  protected function _check_view_preamble($path, &$errors) {
    $expected = "<?php defined(\"SYSPATH\") or die(\"No direct script access.\") ?>\n";
    $fp = fopen($path, "r");
    $actual = fgets($fp);
    fclose($fp);

    if ($expected != $actual) {
      $errors[] = "$path:1\n  expected:\n\t$expected\n  actual:\n\t$actual";
    }
  }

  protected function _is_kohana_path($path) {
    return strpos($path, SYSPATH) === 0 ||
      strpos($path, MODPATH . "cache") === 0 ||
      strpos($path, MODPATH . "database") === 0 ||
      strpos($path, MODPATH . "formo") === 0 ||
      strpos($path, MODPATH . "image") === 0 ||
      strpos($path, MODPATH . "orm") === 0 ||
      strpos($path, MODPATH . "unittest") === 0;
  }

  protected function _is_vendor_path($path) {
    return strpos($path, "/vendor/") !== null;
  }

  protected function _is_var_path($path) {
    return strpos($path, DOCROOT . "var") === 0;
  }

  protected function _is_transparent_extension_class($path) {
    return preg_match("#modules/[^/]+/classes/#", $path) && filesize($path) < 300;
  }

  protected function _is_var_logs($path) {
    return strpos($path, DOCROOT . "var/logs/") === 0;
  }

  protected function _check_php_preamble($path, &$errors) {
    $file = file($path);
    if ($this->_is_var_logs($path)) {
      // Similar to view files
      $actual = array($file[0]);
      $expected = array("<?php defined(\"SYSPATH\") or die(\"No direct script access.\"); ?>\n");
    } else if ($this->_is_transparent_extension_class($path)) {
      // Similar to the one-line preamble, except that we don't close the PHP tag
      // because more code follows.  Put this first because transparent extensions
      // exist inside Kohana modules as well.
      $actual = array($file[0]);
      $expected = array("<?php defined(\"SYSPATH\") or die(\"No direct script access.\");\n");
    } else if ($this->_is_kohana_path($path) ||
               $this->_is_vendor_path($path) ||
               $this->_is_var_path($path)) {
      // In all of these cases we only care about the first line.
      $actual = array($file[0]);
      $expected = array("<?php defined(\"SYSPATH\") or die(\"No direct script access.\");\n");
    } else {
      // For everything else we care about the entire copyright
      $actual = $this->_get_preamble($path);
      $expected = array(
        "<?php defined(\"SYSPATH\") or die(\"No direct script access.\");",
        "/**",
        " * Gallery - a web based photo album viewer and editor",
        " * Copyright (C) 2000-2013 Bharat Mediratta",
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
      $errors[] = "$path:1\n  expected:\n\t" . join("\n\t", $expected) .
        "\n  actual:\n\t" . implode("\n\t", $actual);
    }
  }

  public function test_code_files_start_with_preamble() {
    $dir = new PhpCodeFilterIterator(
      new GalleryCodeFilterIterator(
        new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator(DOCROOT))));
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

      case DOCROOT . "lib/mediaelementjs/flashmediaelement.swf.php":
        // SWF wrappers - directly accessible
        break;

      case DOCROOT . "local.php":
        // Special case optional file, not part of the codebase
        break;

      default:
        if (preg_match("/views/", $path)) {
          $this->_check_view_preamble($path, $errors);
        } else {
          $this->_check_php_preamble($path, $errors);
        }
      }
    }

    if ($errors) {
      $this->assertFalse(true, "Preamble errors:\n" . join("\n", $errors));
    }
  }

  public function test_no_tabs_in_our_code() {
    $dir = new PhpCodeFilterIterator(
      new GalleryCodeFilterIterator(
        new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator(DOCROOT))));
    $errors = array();
    foreach ($dir as $file) {
      $file_as_string = file_get_contents($file);
      if (preg_match('/\t/', $file_as_string)) {
        foreach (explode("\n", $file_as_string) as $l => $line) {
          if (preg_match('/\t/', $line)) {
            $errors[] = "$file:$l has tab(s) ($line)";
          }
        }
      }
      $file_as_string = null;
    }
    if ($errors) {
      $this->assertFalse(true, "tab(s) found:\n" . join("\n", $errors));
    }
  }

  protected function _get_preamble($file) {
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

  public function test_helpers_are_static() {
    $dir = new PhpCodeFilterIterator(
      new GalleryCodeFilterIterator(
        new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator(DOCROOT))));
    foreach ($dir as $file) {
      if (basename(dirname($file)) == "helpers") {
        foreach (file($file) as $line) {
          $this->assertTrue(
            !preg_match("/\sfunction\s.*\(/", $line) ||
            preg_match("/^\s*(protected static function _|static function)/", $line),
            "should be \"static function foo\" or \"protected static function _foo\":\n" .
            "$file\n$line\n");
        }
      }
    }
  }

  public function test_module_info_is_well_formed() {
    $info_files = array_merge(
      glob("modules/*/module.info"),
      glob("themes/*/module.info"));

    $errors = array();
    foreach ($info_files as $file) {
      foreach (file($file) as $line) {
        $parts = explode("=", $line, 2);
        if (isset($parts[1])) {
          $values[trim($parts[0])] = trim($parts[1]);
        }
      }

      $module = dirname($file);
      // Certain keys must exist
      foreach (array("name", "description", "version") as $key) {
        if (!array_key_exists($key, $values)) {
          $errors[] = "$module: missing key $key";
        }
      }

      // Any values containing spaces must be quoted
      foreach ($values as $key => $value) {
        if (strpos($value, " ") !== false && !preg_match('/^".*"$/', $value)) {
          $errors[] = "$module: value for $key must be quoted";
        }
      }

      // The file must parse
      if (!is_array(parse_ini_file($file))) {
        $errors[] = "$module: info file is not parseable";
      }
    }
    if ($errors) {
      $this->assertTrue(false, $errors);
    }
  }

  public function test_all_public_functions_in_test_files_start_with_test() {
    // Who tests the tests?  :-)   (ref: http://www.xkcd.com/1163)
    $dir = new PhpCodeFilterIterator(
      new GalleryCodeFilterIterator(
        new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator(DOCROOT))));
    foreach ($dir as $file) {
      $scan = 0;
      if (basename(dirname($file)) == "tests") {
        foreach (file($file) as $line) {
          if (!substr($file, -9, 9) == "_Test.php") {
            continue;
          }

          if (preg_match("/class.*extends.*Unittest_TestCase/", $line)) {
            $scan = 1;
          } else if (preg_match("/class.*extends/", $line)) {
            $scan = 0;
          }

          if ($scan) {
            if (preg_match("/^\s*public\s+function/", $line)) {
              $this->assertRegExp("/^\s*public\s+function (setup|teardown|test_.*)\(\) {/", $line,
                                  "public functions must start with in 'test_':\n$file\n$line\n");
            }
          }
        }
      }
    }
  }

  public function test_no_extra_spaces_at_end_of_line() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));
    $errors = "";
    foreach ($dir as $file) {
      if (preg_match("/\.(php|css|html|js)$/", $file)) {
        foreach (file($file) as $line_num => $line) {
          if ((substr($line, -2) == " \n") || (substr($line, -1) == " ")) {
            $errors .= "$file at line " . ($line_num + 1) . "\n";
          }
        }
      }
    }
    $this->assertTrue(empty($errors), "Extra spaces at end of line found at:\n$errors");
  }
}
