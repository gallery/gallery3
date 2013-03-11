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
class Theme_View_Core extends Gallery_View {
  /**
   * Attempts to load a view and pre-load view data.
   *
   * @throws  Kohana_Exception  if the requested view cannot be found
   * @param   string  $name view name
   * @param   string  $page_type page type: collection, item, or other
   * @param   string  $page_subtype page sub type: album, photo, tags, etc
   * @param   string  $theme_name view name
   * @return  void
   */
  public function __construct($name, $page_type, $page_subtype) {
    parent::__construct($name);

    $this->theme_name = module::get_var("gallery", "active_site_theme");
    if (identity::active_user()->admin) {
      $theme_name = Input::instance()->get("theme");
      if ($theme_name &&
          file_exists(THEMEPATH . $theme_name) &&
          strpos(realpath(THEMEPATH . $theme_name), THEMEPATH) == 0) {
        $this->theme_name = $theme_name;
      }
    }
    $this->item = null;
    $this->tag = null;
    $this->set_global(array("theme" => $this,
                            "theme_info" => theme::get_info($this->theme_name),
                            "user" => identity::active_user(),
                            "page_type" => $page_type,
                            "page_subtype" => $page_subtype,
                            "page_title" => null));

    if (module::get_var("gallery", "maintenance_mode", 0)) {
      if (identity::active_user()->admin) {
        message::warning(t("This site is currently in maintenance mode.  Visit the <a href=\"%maintenance_url\">maintenance page</a>", array("maintenance_url" => url::site("admin/maintenance"))));
    } else
        message::warning(t("This site is currently in maintenance mode."));
    }
  }

  /**
   * Proportion of the current thumb_size's to default
   * @param object Item_Model (optional) check the proportions for this item
   * @return int
   */
  public function thumb_proportion($item=null) {
    // If the item is an album with children, grab the first item in that album instead.  We're
    // interested in the size of the thumbnails in this album, not the thumbnail of the
    // album itself.
    if ($item && $item->is_album() && $item->children_count()) {
      $item = $item->children(1)->current();
    }

    // By default we have a globally fixed thumbnail size In core code, we just return a fixed
    // proportion based on the global thumbnail size, but since modules can override that, we
    // return the actual proportions when we have them.
    if ($item && $item->has_thumb()) {
      return max($item->thumb_width, $item->thumb_height) / 200;
    } else {
      // @TODO change the 200 to a theme supplied value when and if we come up with an
      // API to allow the theme to set defaults.
      return module::get_var("gallery", "thumb_size", 200) / 200;
    }
  }

  public function item() {
    return $this->item;
  }

  public function siblings($limit=null, $offset=null) {
    return call_user_func_array(
      $this->siblings_callback[0],
      array_merge($this->siblings_callback[1], array($limit, $offset)));
  }

  public function tag() {
    return $this->tag;
  }

  public function page_type() {
    return $this->page_type;
  }

  public function page_subtype() {
    return $this->page_subtype;
  }

  public function user_menu() {
    $menu = Menu::factory("root")
      ->css_id("g-login-menu")
      ->css_class("g-inline ui-helper-clear-fix");
    module::event("user_menu", $menu, $this);
    return $menu->render();
  }

  public function site_menu($item_css_selector) {
    $menu = Menu::factory("root");
    module::event("site_menu", $menu, $this, $item_css_selector);
    return $menu->render();
  }

  public function album_menu() {
    $menu = Menu::factory("root");
    module::event("album_menu", $menu, $this);
    return $menu->render();
  }

  public function tag_menu() {
    $menu = Menu::factory("root");
    module::event("tag_menu", $menu, $this);
    return $menu->render();
  }

  public function photo_menu() {
    $menu = Menu::factory("root");
    if (access::can("view_full", $this->item())) {
      $menu->append(Menu::factory("link")
                    ->id("fullsize")
                    ->label(t("View full size"))
                    ->url($this->item()->file_url())
                    ->css_class("g-fullsize-link"));
    }

    module::event("photo_menu", $menu, $this);
    return $menu->render();
  }

  public function movie_menu() {
    $menu = Menu::factory("root");
    module::event("movie_menu", $menu, $this);
    return $menu->render();
  }

  public function context_menu($item, $thumbnail_css_selector) {
    $menu = Menu::factory("root")
      ->append(Menu::factory("submenu")
               ->id("context_menu")
               ->label(t("Options")))
      ->css_class("g-context-menu");

    module::event("context_menu", $menu, $this, $item, $thumbnail_css_selector);
    return $menu->render();
  }

  /**
   * Print out any site wide status information.
   */
  public function site_status() {
    return site_status::get();
  }

  /**
   * Print out any messages waiting for this user.
   */
  public function messages() {
    return message::get();
  }

  /**
   * Print out the sidebar.
   */
  public function sidebar_blocks() {
    $sidebar = block_manager::get_html("site_sidebar", $this);
    if (empty($sidebar) && identity::active_user()->admin) {
      $sidebar = new View("no_sidebar.html");
    }
    return $sidebar;
  }

  /**
   * Handle all theme functions that insert module content.
   */
  public function __call($function, $args) {
    switch ($function) {
    case "album_blocks":
    case "album_bottom":
    case "album_top":
    case "body_attributes":
    case "credits";
    case "dynamic_bottom":
    case "dynamic_top":
    case "footer":
    case "head":
    case "header_bottom":
    case "header_top":
    case "html_attributes":
    case "page_bottom":
    case "page_top":
    case "photo_blocks":
    case "photo_bottom":
    case "photo_top":
    case "resize_bottom":
    case "resize_top":
    case "sidebar_bottom":
    case "sidebar_top":
    case "thumb_bottom":
    case "thumb_info":
    case "thumb_top":
      $blocks = array();
      if (method_exists("gallery_theme", $function)) {
        switch (count($args)) {
        case 0:
          $blocks[] = gallery_theme::$function($this);
          break;
        case 1:
          $blocks[] = gallery_theme::$function($this, $args[0]);
          break;
        case 2:
          $blocks[] = gallery_theme::$function($this, $args[0], $args[1]);
          break;
        default:
          $blocks[] = call_user_func_array(
            array("gallery_theme", $function),
            array_merge(array($this), $args));
        }
      }

      foreach (module::active() as $module) {
        if ($module->name == "gallery") {
          continue;
        }
        $helper_class = "{$module->name}_theme";
        if (class_exists($helper_class) && method_exists($helper_class, $function)) {
          $blocks[] = call_user_func_array(
            array($helper_class, $function),
            array_merge(array($this), $args));
        }
      }

      $helper_class = theme::$site_theme_name . "_theme";
      if (class_exists($helper_class) && method_exists($helper_class, $function)) {
        $blocks[] = call_user_func_array(
          array($helper_class, $function),
          array_merge(array($this), $args));
      }

      if (Session::instance()->get("debug")) {
        if ($function != "head" && $function != "body_attributes") {
          array_unshift(
            $blocks,
            "<div class=\"g-annotated-theme-block g-annotated-theme-block_$function g-clear-fix\">" .
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