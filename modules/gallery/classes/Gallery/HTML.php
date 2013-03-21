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
class html extends html_Core {
  /**
   * Returns a string that is safe to be used in HTML (XSS protection).
   *
   * If $html is a string, the returned string will be HTML escaped.
   * If $html is a SafeString instance, the returned string may contain
   * unescaped HTML which is assumed to be safe.
   *
   * Example:<pre>
   *   <div><?= html::clean($php_var) ?>
   * </pre>
   */
  static function clean($html) {
    return new SafeString($html);
  }

  /**
   * Returns a string that is safe to be used in HTML (XSS protection),
   * purifying (filtering) the given HTML to ensure that the result contains
   * only non-malicious HTML.
   *
   * Example:<pre>
   *   <div><?= html::purify($item->title) ?>
   * </pre>
   */
  static function purify($html) {
    return SafeString::purify($html);
  }

  /**
   * Flags the given string as safe to be used in HTML (free of malicious HTML/JS).
   *
   * Example:<pre>
   *   // Parameters to t() are automatically escaped by default.
   *   // If the parameter is marked as clean, it won't get escaped.
   *   t('Go <a href="%url">there</a>',
   *     array("url" => html::mark_clean(url::current())))
   * </pre>
   */
  static function mark_clean($html) {
    return SafeString::of_safe_html($html);
  }

  /**
   * Escapes the given string for use in JavaScript.
   *
   * Example:<pre>
   *   <script type="text/javascript>"
   *     var some_js_string = <?= html::js_string($php_string) ?>;
   *   </script>
   * </pre>
   */
  static function js_string($string) {
    return SafeString::of($string)->for_js();
  }

  /**
   * Returns a string safe for use in HTML element attributes.
   *
   * Assumes that the HTML element attribute is already
   * delimited by single or double quotes
   *
   * Example:<pre>
   *     <a title="<?= html::clean_for_attribute($php_var) ?>">;
   *   </script>
   * </pre>
   * @return the string escaped for use in HTML attributes.
   */
  static function clean_attribute($string) {
    return html::clean($string)->for_html_attr();
  }
}
