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

require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
    $this->phpmail = new PHPMailer(true);

    $this->loadConfig();

    $this->headers = array();
    $this->from(module::get_var("gallery", "email_from", ""));
    $this->reply_to(module::get_var("gallery", "email_reply_to", ""));
    $this->line_length(module::get_var("gallery", "email_line_length", 70));
    $separator = module::get_var("gallery", "email_header_separator", null);
    $this->header_separator(empty($separator) ? "\n" : unserialize($separator));
  }

  protected function loadConfig() {
    $this->config = Kohana::config('phpmailer');

    if (isset($this->config['options'])) {
      return true;
    }

    $opts = $this->config['options'];

    if (isset($opts['use_smtp']) && $opts['use_smtp']) {
      $this->phpmail->isSMTP();
    }

    if (isset($opts['use_smtp_auth']) && $opts['use_smtp_auth']) {
      $this->phpmail->SMTPAuth = true;
    }

    if (isset($opts['hostname'])) {
      $this->phpmail->Host = $opts['hostname'];
    }

    if (isset($opts['username'])) {
      $this->phpmail->Username = $opts['username'];
    }

    if (isset($opts['password'])) {
      $this->phpmail->Password = $opts['password'];
    }

    if (isset($opts['port'])) {
      $this->phpmail->Port = $opts['port'];
    }

    if (isset($opts['password'])) {
      $this->phpmail->Host = $opts['password'];
    }

    if (isset($opts['secure'])) {
      $secure = strtolower($opts['secure']);

      if ($secure == 'smtps') {
        $this->phpmail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      } elseif ($secure == 'tls') {
        $this->phpmail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      }
    }
  }

  public function __get($key) {
    return null;
  }

  public function __call($key, $value) {
    switch ($key) {
    case "to":
      $this->to = is_array($value[0]) ? $value[0] : array($value[0]);

      foreach ($this->to as $to) {
        $this->phpmail->addAddress($to);
      }

      break;
    case  "header":
      if (count($value) != 2) {
        Kohana_Log::add("error", wordwrap("Invalid header parameters\n" . Kohana::debug($value)));
        throw new Exception("@todo INVALID_HEADER_PARAMETERS");
      }
      $this->headers[$value[0]] = $value[1];
      break;
    case "from":
      $this->phpmail->setFrom($value[0]);
      break;
    case "reply_to":
      $this->phpmail->addReplyTo($value[0]);
      break;
    default:
      $this->$key = $value[0];
    }
    return $this;
  }

  public function send() {
    if (empty($this->to)) {
      Kohana_Log::add("error", wordwrap("Sending mail failed:\nNo to address specified"));
      throw new Exception("@todo TO_IS_REQUIRED_FOR_MAIL");
    }

    // all modules appear to use HTML, defaulting to true
    // could possibly check for a content-type header being passed in
    $this->phpmail->isHTML(true);

    $message = wordwrap($this->message, $this->line_length, "\n");

    $this->phpmail->Subject = $this->subject;
    $this->phpmail->Body = $message;

    $this->phpmail->send();

    return $this;
  }
}
