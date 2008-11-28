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
class Theme_View_Core extends View {
  private $theme_name = null;

  /**
   * Attempts to load a view and pre-load view data.
   *
   * @throws  Kohana_Exception  if the requested view cannot be found
   * @param   string  $name view name
   * @param   string  $page_type page type: album, photo, tags, admin, etc
   * @param   string  $theme_name view name
   * @return  void
   */
  public function __construct($name, $page_type, $theme_name="default") {
    parent::__construct($name);
    $this->theme_name = $theme_name;
    $this->set_global('theme', $this);
    $this->set_global('user', Session::instance()->get('user', null));
    $this->set_global("page_type", $page_type);
  }

  public function url($path) {
    return url::file("themes/{$this->theme_name}/$path");
  }

  public function item() {
    return $this->item;
  }

  public function tag() {
    return $this->tag;
  }

  public function page_type() {
    return $this->page_type;
  }

  public function display($page_name, $view_class="View") {
    return new $view_class($page_name);
  }

  public function pager() {
    $this->pagination = new Pagination();
    $this->pagination->initialize(
      array('query_string' => 'page',
            'total_items' => $this->children_count,
            'items_per_page' => $this->page_size,
            'style' => 'classic'));
    return $this->pagination->render();
  }

  /**
   * Handle all theme functions that insert module content.
   */
  public function __call($function, $args) {
    switch ($function) {
    case "album_blocks":
    case "album_bottom":
    case "album_top":
    case "credits";
    case "head":
    case "header_bottom":
    case "header_top":
    case "navigation_top":
    case "navigation_bottom":
    case "page_bottom":
    case "page_top":
    case "photo_blocks":
    case "photo_top":
    case "sidebar_blocks":
    case "sidebar_bottom":
    case "sidebar_top":
    case "tag_bottom":
    case "tag_top":
    case "thumbnail_bottom":
    case "thumbnail_info":
    case "thumbnail_top":
    case "photo_bottom":
      // @todo: restrict access to this option
      $debug = Session::instance()->get("debug", false);

      $blocks = array();
      foreach (module::installed() as $module) {
        $helper_class = "{$module->name}_block";
        if (method_exists($helper_class, $function)) {
          $blocks[] = call_user_func_array(
            array($helper_class, $function),
            array_merge(array($this), $args));
        }
      }
      if ($debug) {
        if ($function != "head") {
          array_unshift(
            $blocks, "<div class=\"gAnnotatedThemeBlock gAnnotatedThemeBlock_$function gClearFix\">" .
            "<div class=\"title\">$function</div>");
          $blocks[] = "</div>";
        }
      }
      return implode("\n", $blocks);

    default:
      throw new Exception("@todo UNKNOWN_THEME_FUNCTION: $function");
    }
  }
}