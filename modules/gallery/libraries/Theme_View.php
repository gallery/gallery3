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
class Theme_View_Core extends Gallery_View {
  /**
   * Attempts to load a view and pre-load view data.
   *
   * @throws  Kohana_Exception  if the requested view cannot be found
   * @param   string  $name view name
   * @param   string  $page_type page type: album, photo, tags, etc
   * @param   string  $theme_name view name
   * @return  void
   */
  public function __construct($name, $page_type) {
    $theme_name = module::get_var("gallery", "active_site_theme");
    if (!file_exists("themes/$theme_name")) {
      module::set_var("gallery", "active_site_theme", "default");
      theme::load_themes();
      Kohana::log("error", "Unable to locate theme '$theme_name', switching to default theme.");
    }
    parent::__construct($name);

    $this->theme_name = module::get_var("gallery", "active_site_theme");
    if (user::active()->admin) {
      $this->theme_name = Input::instance()->get("theme", $this->theme_name);
    }
    $this->item = null;
    $this->tag = null;
    $this->set_global("theme", $this);
    $this->set_global("user", user::active());
    $this->set_global("page_type", $page_type);
    $this->set_global("page_title", null);
    if ($page_type == "album") {
      $this->set_global("thumb_proportion", $this->thumb_proportion());
    }

    $maintenance_mode = Kohana::config("core.maintenance_mode", false, false);
    if ($maintenance_mode) {
      message::warning(t("This site is currently in maintenance mode"));
    }
  }

  /**
   * Proportion of the current thumb_size's to default
   * @return int
   */
  public function thumb_proportion() {
    // @TODO change the 200 to a theme supplied value when and if we come up with an
    // API to allow the theme to set defaults.
    return module::get_var("gallery", "thumb_size", 200) / 200;
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

  public function site_menu() {
    $menu = Menu::factory("root");
    gallery::site_menu($menu, $this);
    module::event("site_menu", $menu, $this);
    return $menu->compact();
  }

  public function album_menu() {
    $menu = Menu::factory("root");
    module::event("album_menu", $menu, $this);
    return $menu->compact();
  }

  public function tag_menu() {
    $menu = Menu::factory("root");
    module::event("tag_menu", $menu, $this);
    return $menu->compact();
  }

  public function photo_menu() {
    $menu = Menu::factory("root");
    if (access::can("view_full", $this->item())) {
      $menu->append(Menu::factory("link")
                    ->id("fullsize")
                    ->label(t("View full size"))
                    ->url($this->item()->file_url())
                    ->css_class("gFullSizeLink"));
    }

    module::event("photo_menu", $menu, $this);
    return $menu->compact();
  }

  public function context_menu($item, $thumbnail_css_selector) {
    $menu = Menu::factory("root")
      ->append(Menu::factory("submenu")
               ->id("context_menu")
               ->label(t("Options")))
      ->css_class("gContextMenu");

    gallery::context_menu($menu, $this, $item, $thumbnail_css_selector);
    module::event("context_menu", $menu, $this, $item, $thumbnail_css_selector);
    return $menu->compact();
  }

  public function pager() {
    if ($this->children_count) {
      $this->pagination = new Pagination();
      $this->pagination->initialize(
        array("query_string" => "page",
              "total_items" => $this->children_count,
              "items_per_page" => $this->page_size,
              "style" => "classic"));
      return $this->pagination->render();
    }
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
    case "page_bottom":
    case "page_top":
    case "photo_blocks":
    case "photo_bottom":
    case "photo_top":
    case "resize_bottom":
    case "resize_top":
    case "sidebar_blocks":
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
        if (method_exists($helper_class, $function)) {
          $blocks[] = call_user_func_array(
            array($helper_class, $function),
            array_merge(array($this), $args));
        }
      }

      if ($function == "head") {
        array_unshift($blocks, $this->combine_files($this->css, "css"));
        array_unshift($blocks, $this->combine_files($this->scripts, "javascript"));
      }

      if (Session::instance()->get("debug")) {
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