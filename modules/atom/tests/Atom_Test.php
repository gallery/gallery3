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
class Atom_Test extends Unit_Test_Case {
/*
 * These aren't real tests. They just demonstrate how the Atom libraries are used.
 * Their output is almost identical to the examples at:
 * http://codex.gallery2.org/Gallery3:Atom_resource_representations
 *
 * Uncomment one or both lines at the end of the tests to see the output.
 */
  public function atom_feed_test() {
    $feed = new Atom_Feed("feed");
    $feed->id("http://gallery.example.com/comments")
      ->title("Comments on Ocean Beach Sunset", "text")
      ->updated("2008-11-15T12:00:00Z");

    $feed->link()
      ->rel("self")
      ->href("http://gallery.example.com/comments");
    $feed->link()
      ->rel("related")
      ->type("application/atom+xml")
      ->title("Get photo meta data")
      ->href("http://gallery.example.com/photos/23");
    $feed->link()
      ->rel("related")
      ->type("image/jpeg")
      ->title("Download photo")
      ->href("http://gallery.example.com/photos/SanFran/sunset.jpg");

    $feed->entry()
      ->id("http://gallery.example.com/comments/32")
      ->updated("2008-11-15T12:00:00Z")
      ->title("")
      ->content("Wow, that's &lt;b>beautiful&lt;b>!", "html")
      ->author()
        ->name("Jonathan Doe")
        ->email("jdoe@example.com")
        ->uri("http://gallery.example.com");

    $xml = $feed->as_xml();
//    file_put_contents("atom-feed.xml", $xml);
//    Kohana::log("debug", "{$xml}");
  }

  public function atom_entry_test() {
    $entry = new Atom_Entry("entry");
    $entry->id("http://gallery.example.com/comments/32")
      ->title("Comment on Ocean Beach Sunset", "text")
      ->updated("2008-11-15T12:00:00Z")
      ->content("Wow, that's &lt;b>beautiful&lt;b>!", "html")
      ->author()
        ->name("Jonathan Doe")
        ->email("jdoe@example.com")
        ->uri("http://gallery.example.com");
    $entry->link()
      ->rel("self")
      ->href("http://gallery.example.com/comments/32");
    $entry->link()
      ->rel("related")
      ->type("application/atom+xml")
      ->title("Get photo meta data")
      ->href("http://gallery.example.com/photos/23");
    $entry->link()
      ->rel("related")
      ->type("text/html")
      ->title("View photo in Gallery")
      ->href("http://gallery.example.com/photos/23");
    $entry->link()
      ->rel("related")
      ->type("image/jpeg")
      ->title("Download photo")
      ->href("http://gallery.example.com/photos/SanFran/sunset.jpg");

    $xml = $entry->as_xml();
//    file_put_contents("atom-entry.xml", $xml);
//    Kohana::log("debug", "{$xml}");
  }
}
