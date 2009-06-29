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
  public function script($file, $theme_relative=false) {
    $this->scripts[$file] = 1;
  }

  /**
   * Add a script to the combined scripts list.
   * @param $file  the relative path to a script from the base of the active theme
   * @param
   */
  public function theme_script($file) {
    $file = "themes/{$this->theme_name}/$file";
    $this->scripts[$file] = 1;
  }

  /**
   * Provide a url to a resource within the current theme.  This allows us to refer to theme
   * resources without naming the theme itself which makes themes easier to copy.
   */
  public function theme_url($path, $absolute_url=false) {
    $arg = "themes/{$this->theme_name}/$path";
    return $absolute_url ? url::abs_file($arg) : url::file($arg);
  }

  /**
   * Combine a series of Javascript files into a single one and cache it in the database, then
   * return a single <script> element to refer to it.
   */
  protected function combine_script() {
    $links = array();
    $key = "";
    foreach (array_keys($this->scripts) as $file) {
      $path = DOCROOT . $file;
      if (file_exists($path)) {
        $stats = stat($path);
        $links[] = $path;
        // 7 == size, 9 == mtime, see http://php.net/stat
        $key = "{$key}$file $stats[7] $stats[9],";
      } else {
        Kohana::log("alert", "Javascript file missing: " . $file);
      }
    }

    $key = md5($key);
    $cache = Cache::instance();
    $contents = $cache->get($key);
    if (empty($contents)) {
      $contents = "";
      foreach ($links as $link) {
        $contents .= file_get_contents($link);
      }
      $cache->set($key, $contents, array("javascript"), 30 * 84600);
      if (function_exists("gzencode")) {
        $cache->set("{$key}_gz", gzencode($contents, 9, FORCE_GZIP),
                    array("javascript", "gzip"), 30 * 84600);
      }
    }

    // Handcraft the script link because html::script will add a .js extenstion
    return "<script type=\"text/javascript\" src=\"" . url::site("combined/javascript/$key") .
      "\"></script>";
  }

  /**
   * Add a css file to the combined css list.
   * @param $file  the relative path to a script from the gallery3 directory
   */
  public function css($file, $theme_relative=false) {
    $this->css[$file] = 1;
  }

  /**
   * Add a css file to the combined css list.
   * @param $file  the relative path to a script from the base of the active theme
   * @param
   */
  public function theme_css($file) {
    $file = "themes/{$this->theme_name}/$file";
    $this->css[$file] = 1;
  }

  /**
   * Combine a series of Javascript files into a single one and cache it in the database, then
   * return a single <script> element to refer to it.
   */
  protected function combine_css() {
    $links = array();
    $key = "";
    foreach (array_keys($this->css) as $file) {
      $links[] = "<link media=\"screen, projection\" rel=\"stylesheet\" type=\"text/css\" href=\"" .
        url::file($file) . "\" />";

    }
    return implode("\n", $links);
  }

}