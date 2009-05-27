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
class Sendmail_Core {
  protected $to;
  protected $subject;
  protected $message;
  protected $headers;
  protected $line_length = 70;
  protected $header_separator = "\r\n";

  /**
   * Return an instance of Sendmail
   * @chainable
   */
  static function factory() {
    return new Sendmail();
  }

  public function __construct() {
    $this->headers = array();
    $config = Kohana::config("sendmail");
    foreach ($config as $key => $value) {
      $this->$key($value);
    }
  }

  public function __get($key) {
    return null;
  }

  public function __call($key, $value) {
    switch ($key) {
    case "to":
      $this->to = is_array($value[0]) ? $value[0] : array($value[0]);
      break;
    case  "header":
      if (count($value) != 2) {
        throw new Exception("@todo INVALID_HEADER_PARAMETERS");
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
      throw new Exception("@todo TO_IS_REQUIRED_FOR_MAIL");
    }
    $to = implode(", ", $this->to);
    $headers = array();
    foreach ($this->headers as $key => $value) {
      $key = ucfirst($key);
      $headers[] = "$key: $value";
    }

    // The docs say headers should be separated by \r\n, but occasionaly that doesn't work and you
    // need to use a single \n.  This can be set in config/sendmail.php
    $headers = implode($this->header_separator, $headers);
    $message = wordwrap($this->message, $this->line_length, "\n");
    if (!$this->mail($to, $this->subject, $message, $headers)) {
      Kohana::log("error", wordwrap("Sending mail failed:\nTo: $to\n $this->subject\n" .
                                    "Headers: $headers\n $this->message"));
      throw new Exception("@todo SEND_MAIL_FAILED");
    }
    return $this;
  }

  public function mail($to, $subject, $message, $headers) {
    return mail($to, $subject, $message, $headers);
  }
}
