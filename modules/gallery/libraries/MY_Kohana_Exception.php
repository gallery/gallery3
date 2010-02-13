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
class Kohana_Exception extends Kohana_Exception_Core {
  /**
   * Dump out the full stack trace as part of the text representation of the exception.
   */
  public static function text($e) {
    return sprintf(
      "%s [ %s ]: %s\n%s [ %s ]\n%s",
      get_class($e), $e->getCode(), strip_tags($e->getMessage()),
      $e->getFile(), $e->getLine(),
      $e->getTraceAsString());
  }

  public static function handle(Exception $e) {
    if ($e instanceof ORM_Validation_Exception) {
      Kohana_Log::add("error", "Validation errors: " . print_r($e->validation->errors(), 1));
    }
    try {
      $user = identity::active_user();
      $try_themed_view = $user && !$user->admin;
    } catch (Exception $e2) {
      $try_themed_view = false;
    }

    if ($try_themed_view) {
      try {
        return self::_show_themed_error_page($e);
      } catch (Exception $e3) {
        Kohana_Log::add("error", "Exception in exception handling code: " . self::text($e3));
        return parent::handle($e);
      }
    } else {
      return parent::handle($e);
    }
  }

  /**
   * Shows a themed error page.
   * @see Kohana_Exception::handle
   */
  private static function _show_themed_error_page(Exception $e) {
    // Create a text version of the exception
    $error = Kohana_Exception::text($e);
    
    // Add this exception to the log
    Kohana_Log::add('error', $error);

    // Manually save logs after exceptions
    Kohana_Log::save();

    if (!headers_sent()) {
      if ($e instanceof Kohana_Exception) {
        $e->sendHeaders();
      } else {
        header("HTTP/1.1 500 Internal Server Error");
      }
    }

    $view = new Theme_View("page.html", "other", "error");
    if ($e instanceof Kohana_404_Exception) {
      $view->page_title = t("Dang...  Page not found!");
      $view->content = new View("error_404.html");
      $user = identity::active_user();
      $view->content->is_guest = $user && $user->guest;
      if ($view->content->is_guest) {
        $view->content->login_form = new View("login_ajax.html");
        $view->content->login_form->form = auth::get_login_form("login/auth_html");
        // Avoid anti-phishing protection by passing the url as session variable.
        Session::instance()->set("continue_url", url::current(true));
      }
    } else {
      $view->page_title = t("Dang...  Something went wrong!");
      $view->content = new View("error.html");
    }
    print $view;
  }
}