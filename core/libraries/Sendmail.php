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
class Sendmail_Core {
  protected $to;
  protected $subject;
  protected $message;
  protected $headers;
  protected $line_length = 70;

  /**
   * In test mode this gets the message that would have been set
   */
  private $_send_text;

  /**
   * Return an instance of a Menu_Element
   * @chainable
   */
  static function factory() {
    return new Sendmail();
  }

  public function __construct() {
    $this->headers = array();
    $config = Kohana::config('sendmail');
    foreach ($config as $key => $value) {
      $this->$key($value);
    }
  }

  public function __get($key) {
    if (TEST_MODE && $key == "send_text") {
      return $this->_send_text;
    }
    return null;
  }

  public function __call($key, $value) {
    switch ($key) {
    case "to":
      $this->to = is_array($value) ? $value : array($value);
      break;
    case  "header":
      if (count($value) != 2) {
        throw new Exception("@todo INVALID HEADER PARAMETERS");
      }
      $this->headers[$value[0]] = $value[1];
      break;
    case "from":
      $this->headers["From"] = $value[0];
      break;
    case "reply_to":
      $this->headers["Reply-To"] = $value[0];
      break;
    default:
      $this->$key = $value[0];
    }
    return $this;
  }

  public function send() {
    if (empty($this->to)) {
      throw new Exception("@todo TO IS REQUIRED FOR MAIL");
    }
    $to = implode(", ", $this->to);
    $headers = array();
    foreach ($this->headers as $key => $value) {
      $key = ucfirst($key);
      $headers[] = "$key: $value";
    }
    $headers = implode("\r\n", $headers);
    $message = wordwrap($this->message, $this->line_length, "\r\n");

    if (!TEST_MODE) {
      if (!mail($to, $this->subject, $this->message, $headers)) {
        Kohana::log("error", wordwrap("Sending mail failed:\nTo: $to\n $this->subject\n" .
                                      "Headers: $headers\n $this->message"));
        throw new Exception("@todo SEND MAIL FAILED");
      }
    } else {
      $this->_send_text = "To: $to\r\n{$headers}\r\nSubject: $this->subject\r\n\r\n$message";
    }
    return $this;
  }
}
