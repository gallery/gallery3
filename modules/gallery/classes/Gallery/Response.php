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
class Gallery_Response extends Kohana_Response {
  /**
   * Encode an Ajax response so that it's UTF-7 safe.
   *
   * @param  string $message string to print
   */
  public function ajax($content) {
    $this->headers("Content-Type", "text/plain; charset=" . Kohana::$charset);
    $this->body("<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">\n" .
                $content);
  }

  /**
   * JSON Encode a reply to the browser and set the content type to specify that it's a JSON
   * payload.
   *
   * Optionally, the content type can be set as "text/plain" which helps with iframe
   * compatibility (see ticket #2022).
   *
   * @param  mixed    $message     string or object to json encode and print
   * @param  boolean  $text_plain  use content type of "text/plain" (default: false)
   */
  public function json($message, $text_plain=false) {
    if ($text_plain) {
      $this->headers("Content-Type", "text/plain; charset=" . Kohana::$charset);
    } else {
      $this->headers("Content-Type", "application/json; charset=" . Kohana::$charset);
    }
    $this->body(json_encode($message));
  }

  /**
   * Setup the response for a Formo form as needed to work with Gallery's ajax system
   * (e.g. gallery.dialog.js, gallery.in_place_edit.js, etc.).  If passed something
   * other than a validated Formo object, this will throw an exception.
   *
   * This uses two custom form variables:
   *   $form->get("status");         // the status, which is automatically set by Formo::validate().
   *   $form->get("response");       // the desired response upon a valid, completed form.
   *   $form->get("show_in_dialog"); // put the response in the dialog instead of closing it (bool).
   * The "response" variable can be set as a string, an array, or left empty.  Examples:
   *   (form's "response" left unset or empty)        // Reload current URL (default)
   *   $form->set("response", $item->abs_url());      // Redirect to absolute URL
   *   $form->set("response", array("foo" => "bar")); // Send JSON data (no redirect)
   *
   * @see    Formo::validate()
   * @see    Response::json()
   * @see    gallery.dialog.js
   * @param  Formo    $form
   */
  public function ajax_form($form) {
    if (!($form instanceof Formo)) {
      throw new Gallery_Exception("Using ajax_form() requires a Formo object");
    }

    if (Request::current()->is_ajax()) {
      switch ($form->get("status")) {
        case Formo::PASSED:
          $response = $form->get("response");
          if (!$response) {
            $response = array("result" => "success");
          } else if (is_array($response)) {
            $response["result"] = "success";
          } else {
            $response = array("result" => "success", "location" => $response);
            if ($form->get("show_in_dialog")) {
              $response["show_in_dialog"] = 1;
            }
          }
          $this->json($response);
          break;

        case Formo::FAILED:
          // If we have a multipart enctype (e.g. watermark and item uploads), set the content
          // type as "text/plain" which helps with iframe compatibility (see ticket #2022).
          $plain_text = (strpos($form->attr("enctype"), "multipart") !== false);
          $this->json(array("result" => "error", "html" => (string)$form), $plain_text);
          break;

        case Formo::NOT_SENT:
          $this->body((string)$form);
          break;

        default:
          throw new Gallery_Exception("Formo object has not been validated");
      }
    } else {
      // The user got here by entering a the link directly in their address bar instead of
      // following one of our ajax links.  This is not the preferred method of display, but
      // we can make an attempt to make the form look presentable.
      switch ($form->get("status")) {
        case Formo::PASSED:
          $response = $form->get("response");
          if ($response && !is_array($response)) {
            HTTP::redirect($response);
          } else {
            // It's hard to tell what to do here.  There's no redirect address specified,
            // and redirecting to an empty form doesn't make sense, so let's go to the root.
            // @todo: if we use this route more often, we should be more clever about it.
            HTTP::redirect(Item::root()->abs_url());
          }
          break;

        case Formo::FAILED:
        case Formo::NOT_SENT:
          // Wrap the basic form in a theme.
          if (Theme::$is_admin) {
            $view = new View_Admin("required/admin.html");
          } else {
            $view = new View_Theme("required/page.html", "other", "form");
          }
          $view->content = $form;

          // We need a page title.  Similar to how gallery.dialog.js gets its dialog title,
          // we'll get it from the label of the first group (which is rendered as the legend
          // of the first fieldset).
          $view->page_title = Item::root()->title; // our fallback default
          foreach ($form->as_array() as $child) {
            if ($child->get("driver") == "group") {
              $view->page_title = $child->get("label");
              break;
            }
          }

          $this->body($view);
          break;

        default:
          throw new Gallery_Exception("Formo object has not been validated");
      }
    }
  }

  /**
   * Overload Response::send_file() to handle the "encoding" option.  Currently,
   * the only value of encoding we act upon is "base64" which is used in REST.
   * @see Response::send_file()
   */
  public function send_file($filename, $download=null, array $options=null) {
    if ($encoding = Arr::get($options, "encoding")) {
      switch ($encoding) {
        case "base64":
          if ($filename === true) {
            // Use the response body.
            $this->response->body(base64_encode($this->response->body()));
          } else {
            // Load file into the response body, set download name if empty, reset the filename.
            $this->response->body(base64_encode(file_get_contents($filename)));
            if (empty($download)) {
              $download = pathinfo($filename, PATHINFO_BASENAME);
            }
            $filename = true;
          }
          break;
        default:
          // Remove the encoding option to avoid confusion downstream.
          unset($options["encoding"]);
      }
    }

    return parent::send_file($filename, $download, $options);
  }
}
