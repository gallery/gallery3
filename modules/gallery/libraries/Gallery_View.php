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
class Gallery_View_Core extends View {
  protected $theme_name = null;
  protected $combine_queue = array();

  /**
   * Provide a url to a resource within the current theme.  This allows us to refer to theme
   * resources without naming the theme itself which makes themes easier to copy.
   */
  public function url($path, $absolute_url=false) {
    $arg = "themes/{$this->theme_name}/$path";
    return $absolute_url ? url::abs_file($arg) : url::file($arg);
  }

  /**
   * Set up the data and render a pager.
   *
   * See themes/wind/views/pager.html for documentation on the variables generated here.
   */
  public function paginator() {
    $v = new View("paginator.html");
    $v->page_type = $this->page_type;
    $v->page_subtype = $this->page_subtype;
    $v->first_page_url = null;
    $v->previous_page_url = null;
    $v->next_page_url = null;
    $v->last_page_url = null;

    if ($this->page_type == "collection") {
      $v->page = $this->page;
      $v->max_pages = $this->max_pages;
      $v->total = $this->children_count;

      if ($this->page != 1) {
        $v->first_page_url = url::site(url::merge(array("page" => 1)));
        $v->previous_page_url = url::site(url::merge(array("page" => $this->page - 1)));
      }

      if ($this->page != $this->max_pages) {
        $v->next_page_url = url::site(url::merge(array("page" => $this->page + 1)));
        $v->last_page_url = url::site(url::merge(array("page" => $this->max_pages)));
      }

      $v->first_visible_position = ($this->page - 1) * $this->page_size + 1;
      $v->last_visible_position = min($this->page * $this->page_size, $v->total);
    } else if ($this->page_type == "item") {
      $v->position = $this->position;
      $v->total = $this->sibling_count;
      if ($this->previous_item) {
        $v->previous_page_url = $this->previous_item->url();
      }

      if ($this->next_item) {
        $v->next_page_url = $this->next_item->url();
      }
    }

    return $v;
  }

  /**
   * Begin gather up scripts or css files so that they can be combined into a single request.
   *
   * @param $types  a comma separated list of types to combine, eg "script,css"
   */
  public function start_combining($types) {
    if (gallery::allow_css_and_js_combining()) {
      foreach (explode(",", $types) as $type) {
        $this->combine_queue[$type] = array();
      }
    }
  }

  /**
   * If script combining is enabled, add this script to the list of scripts that will be
   * combined into a single script element.  When combined, the order of scripts is preserved.
   *
   * @param $file  the file name or path of the script to include. If a path is specified then
   *               it needs to be relative to DOCROOT. Just specifying a file name will result
   *               in searching Kohana's cascading file system.
   * @param $group the group of scripts to combine this with.  defaults to "core"
   */
  public function script($file, $group="core") {
    if (($path = gallery::find_file("js", $file, false))) {
      if (isset($this->combine_queue["script"])) {
        $this->combine_queue["script"][$group][$path] = 1;
      } else {
        return html::script($path);
      }
    } else {
      Kohana_Log::add("error", "Can't find script file: $file");
    }
  }

  /**
   * If css combining is enabled, add this css to the list of css that will be
   * combined into a single style element.  When combined, the order of style elements
   * is preserved.
   *
   * @param $file  the file name or path of the css to include. If a path is specified then
   *               it needs to be relative to DOCROOT. Just specifying a file name will result
   *               in searching Kohana's cascading file system.
   * @param $group the group of css to combine this with.  defaults to "core"
   */
  public function css($file, $group="core") {
    if (($path = gallery::find_file("css", $file, false))) {
      if (isset($this->combine_queue["css"])) {
        $this->combine_queue["css"][$group][$path] = 1;
      } else {
        return html::stylesheet($path);
      }
    } else {
      Kohana_Log::add("error", "Can't find css file: $file");
    }
  }

  /**
   * Combine a series of files into a single one and cache it in the database.
   * @param $type  the data type (script or css)
   * @param $group the group of scripts or css we want
   */
  public function get_combined($type, $group="core") {
    $links = array();

    if (empty($this->combine_queue[$type][$group])) {
      return;
    }

    // Include the url in the cache key so that if the Gallery moves, we don't use old cached
    // entries.
    $key = array(url::abs_file(""));

    foreach (array_keys($this->combine_queue[$type][$group]) as $path) {
      $stats = stat($path);
      // 7 == size, 9 == mtime, see http://php.net/stat
      $key[] = "$path $stats[7] $stats[9]";
    }

    $key = md5(join(" ", $key));
    $cache = Cache::instance();
    $contents = $cache->get($key);

    if (empty($contents)) {
      $combine_data = new stdClass();
      $combine_data->type = $type;
      $combine_data->contents = $this->combine_queue[$type][$group];
      module::event("before_combine", $combine_data);

      $contents = "";
      foreach (array_keys($this->combine_queue[$type][$group]) as $path) {
        if ($type == "css") {
          $contents .= "/* $path */\n" . $this->process_css($path) . "\n";
        } else {
          $contents .= "/* $path */\n" . file_get_contents($path) . "\n";
        }
      }

      $combine_data = new stdClass();
      $combine_data->type = $type;
      $combine_data->contents = $contents;
      module::event("after_combine", $combine_data);

      $cache->set($key, $combine_data->contents, array($type), 30 * 84600);

      $use_gzip = function_exists("gzencode") &&
        (int) ini_get("zlib.output_compression") === 0;
      if ($use_gzip) {
        $cache->set("{$key}_gz", gzencode($combine_data->contents, 9, FORCE_GZIP),
                    array($type, "gzip"), 30 * 84600);
      }

    }

    unset($this->combine_queue[$type][$group]);
    if (empty($this->combine_queue[$type])) {
      unset($this->combine_queue[$type]);
    }

    if ($type == "css") {
      return html::stylesheet("combined/css/$key", "screen,print,projection", true);
    } else {
      return html::script("combined/javascript/$key", true);
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
        $relative = dirname($css_file) . "/$match[1]";
        if (!empty($relative)) {
          $search[] = $match[0];
          $replace[] = "url('" . url::abs_file($relative) . "')";
        } else {
          Kohana_Log::add("error", "Missing URL reference '{$match[1]}' in CSS file '$css_file'");

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