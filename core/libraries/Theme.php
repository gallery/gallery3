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
class Theme_Core {
  private $theme_name = null;
  private $template = null;

  public function __construct($theme_name, $template) {
    $this->theme_name = $theme_name;
    $this->template = $template;
  }

  public function url($path) {
    return url::base() . "themes/{$this->theme_name}/$path";
  }

  public function item() {
    return $this->template->item;
  }

  public function display($page_name, $view_class="View") {
    return new $view_class($page_name);
  }

  public function pager() {
    $this->pagination = new Pagination();
    $this->pagination->initialize(
      array('query_string' => 'page',
            'total_items' => $this->template->item->children_count(),
            'items_per_page' => $this->template->page_size,
            'style' => 'classic'));
    return $this->pagination->render();
  }

  public function blocks() {
    /** @todo: make this data driven */
    $blocks = array(
      'carousel' => carousel::block($this),
      'tags' => tags::block($this),
      'info' => info::block($this),
      'gmaps' => gmaps::block($this),
    );
    return $blocks;
  }
}
