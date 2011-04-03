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
class valid extends valid_Core {
  /**
   * Basic URL validation.
   *
   * Allows non-ASCII characters in the request path, which Kohana's url()
   * method does not.
   *
   * @param  string   URL
   * @return boolean
   */
  public static function url($url) {
    // Adapted from https://github.com/henrik/validates_url_format_of/blob/master/init.rb
    $ipv4_subregex = '(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])';
    $url_regex = <<<ENDREGEX
    ~^
    https?://
    ([^\s:@]+:[^\s:@]*@)?
    ( (([[:alnum:]]+\.)*xn--)?[[:alnum:]]+([-.][[:alnum:]]+)*\.[a-z]{2,6}\.? |
        ${ipv4_subregex}(\.${ipv4_subregex}){3} )
    (:\d{1,5})?
    ([/?]\S*)?
    \$~iux
ENDREGEX;

    return (bool) preg_match($url_regex, $url);
  }
}
