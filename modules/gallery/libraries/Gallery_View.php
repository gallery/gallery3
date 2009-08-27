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
class Gallery_View_Core extends View {
  protected $theme_name = null;
  protected $scripts = array();
  protected $css = array();

  /**
   * Add a script to the combined scripts list.
   * @param $file  the relative path to a script from the gallery3 directory
   */
  public function script($file) {
    $base_file = str_replace(".js", "", $file);
    if (($path = Kohana::find_file("js", $base_file, false, "js")) ||
        file_exists($path = DOCROOT . "lib/$file")) {
      $this->scripts[$path] = 1;
    } else {
      Kohana::log("error", "Can't find script file: $file");
    }
  }

  /**
   * Provide a url to a resource within the current theme.  This allows us to refer to theme
   * resources without naming the theme itself which makes themes easier to copy.
   */
  public function url($path, $absolute_url=false) {
    $arg = "themes/{$this->theme_name}/$path";
    return $absolute_url ? url::abs_file($arg) : url::file($arg);
  }

  /**
   * Add a css file to the combined css list.
   * @param $file  the relative path to a script from the gallery3 directory
   */
  public function css($file) {
    $base_file = str_replace(".css", "", $file);
    if (($path = Kohana::find_file("css", $base_file, false, "css")) ||
        file_exists($path = DOCROOT . "lib/$file")) {
      $this->css[$path] = 1;
    } else {
      Kohana::log("error", "Can't find css file: $file");
    }
  }

  /**
   * Combine a series of files into a single one and cache it in the database.
   */
  protected function combine_files($paths, $type) {
    $links = array();

    if (empty($paths)) {
      return;
    }

    // Include the url in the cache key so that if the Gallery moves, we don't use old cached
    // entries.
    $key = array(url::abs_file(""));

    foreach (array_keys($paths) as $path) {
      $stats = stat($path);
      // 7 == size, 9 == mtime, see http://php.net/stat
      $key[] = "$path $stats[7] $stats[9]";
    }

    $key = md5(join(" ", $key));
    $cache = Cache::instance();
    $contents = $cache->get($key);

    if (empty($contents)) {
      $contents = "";
      $docroot_len = strlen(DOCROOT);
      foreach (array_keys($paths) as $path) {
        $relative = substr($path, $docroot_len);
        if ($type == "css") {
          $contents .= "/* $relative */\n" . $this->process_css($path) . "\n";
        } else {
          $contents .= "/* $relative */\n" . file_get_contents($path) . "\n";
        }
      }

      $cache->set($key, $contents, array($type), 30 * 84600);

      $use_gzip = function_exists("gzencode") &&
        (int) ini_get("zlib.output_compression") === 0;
      if ($use_gzip) {
        $cache->set("{$key}_gz", gzencode($contents, 9, FORCE_GZIP),
                    array($type, "gzip"), 30 * 84600);
      }
    }

    if ($type == "css") {
      return "<!-- LOOKING FOR YOUR CSS? It's all been combined into the link below -->\n" .
        html::stylesheet("combined/css/$key", "screen,print,projection", true);
    } else {
      return "<!-- LOOKING FOR YOUR JAVASCRIPT? It's all been combined into the link below -->\n" .
        html::script("combined/javascript/$key", true);
    }
  }

  /**
   * Convert relative references inside a CSS file to absolute ones so that when it's served from
   * a new location as part of a combined bundle the references are still correct.
   * @param string  the path to the css file
   */
  private function process_css($css_file) {
    static $PATTERN = "#url\(\s*['|\"]{0,1}(.*?)['|\"]{0,1}\s*\)#";
    $docroot_length = strlen(DOCROOT);

    $css = file_get_contents($css_file);
    if (preg_match_all($PATTERN, $css, $matches, PREG_SET_ORDER)) {
      $search = $replace = array();
      foreach ($matches as $match) {
        $relative = substr(realpath(dirname($css_file) . "/$match[1]"), $docroot_length);
        if (!empty($relative)) {
          $search[] = $match[0];
          $replace[] = "url('" . url::abs_file($relative) . "')";
        } else {
          Kohana::log("error", "Missing URL reference '{$match[1]}' in CSS file '$css_file'");
        }
      }
      $replace = str_replace(DIRECTORY_SEPARATOR, "/", $replace);
      $css = str_replace($search, $replace, $css);
    }
    $imports = preg_match_all("#@import\s*['|\"]{0,1}(.*?)['|\"]{0,1};#",
                              $css, $matches, PREG_SET_ORDER);

    if ($imports) {
      $search = $replace = array();
      foreach ($matches as $match) {
        $search[] = $match[0];
        $replace[] = $this->process_css(dirname($css_file) . "/$match[1]");
      }
      $css = str_replace($search, $replace, $css);
    }

    return $css;
  }
}