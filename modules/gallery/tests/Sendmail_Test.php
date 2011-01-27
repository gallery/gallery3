<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
class Sendmail_Test extends Gallery_Unit_Test_Case {
  public function setup() {
    module::set_var("gallery", "email_from", "from@gallery3.com");
    module::set_var("gallery", "email_reply_to", "reply_to@gallery3.com");
  }

  public function sendmail_basic_test() {
    $expected = "To: receiver@someemail.com\r\n" .
                "From: from@gallery3.com\n" .
                "Reply-To: reply_to@gallery3.com\r\n" .
                "Subject: Test Email Unit test\r\n\r\n" .
                "The mail message body";
    $result = Sendmail_For_Test::factory()
      ->to("receiver@someemail.com")
      ->subject("Test Email Unit test")
      ->message("The mail message body")
      ->send()
      ->send_text;

    $this->assert_equal($expected, $result);
  }

  public function sendmail_reply_to_test() {
    $expected = "To: receiver@someemail.com\r\n" .
                "From: from@gallery3.com\n" .
                "Reply-To: reply_to@gallery3.com\r\n" .
                "Subject: Test Email Unit test\r\n\r\n" .
                "The mail message body";
    $result = Sendmail_For_Test::factory()
      ->to("receiver@someemail.com")
      ->subject("Test Email Unit test")
      ->reply_to("reply_to@gallery3.com")
      ->message("The mail message body")
      ->send()
      ->send_text;
    $this->assert_equal($expected, $result);
  }

  public function sendmail_html_message_test() {
    $expected = "To: receiver@someemail.com\r\n" .
                "From: from@gallery3.com\n" .
                "Reply-To: reply_to@gallery3.com\n" .
                "MIME-Version: 1.0\n" .
                "Content-Type: text/html; charset=UTF-8\r\n" .
                "Subject: Test Email Unit test\r\n\r\n" .
                "<html><body><p>This is an html msg</p></body></html>";
    $result = Sendmail_For_Test::factory()
      ->to("receiver@someemail.com")
      ->subject("Test Email Unit test")
      ->header("MIME-Version", "1.0")
      ->header("Content-Type", "text/html; charset=UTF-8")
      ->message("<html><body><p>This is an html msg</p></body></html>")
      ->send()
      ->send_text;
    $this->assert_equal($expected, $result);
  }

  public function sendmail_wrapped_message_test() {
    $domain = Input::instance()->server("HTTP_HOST");
    $expected = "To: receiver@someemail.com\r\n" .
                "From: from@gallery3.com\n" .
                "Reply-To: reply_to@gallery3.com\r\n" .
                "Subject: Test Email Unit test\r\n\r\n" .
                "This is a long message that needs to go\n" .
                "over forty characters If we get lucky we\n" .
                "might make it long enought to wrap a\n" .
                "couple of times.";
    $result = Sendmail_For_Test::factory()
      ->to("receiver@someemail.com")
      ->subject("Test Email Unit test")
      ->line_length(40)
      ->message("This is a long message that needs to go over forty characters " .
                "If we get lucky we might make it long enought to wrap a couple " .
                "of times.")
      ->send()
      ->send_text;
    $this->assert_equal($expected, $result);
  }
}

class Sendmail_For_Test extends Sendmail {
  static function factory() {
    return new Sendmail_For_Test();
  }

  public function mail($to, $subject, $message, $headers) {
    $this->send_text = "To: $to\r\n{$headers}\r\nSubject: $this->subject\r\n\r\n$message";
    return true;
  }
}