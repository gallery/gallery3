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

/**
 * This is the API for handling modules.
 *
 * Note: by design, this class does not do any permission checking.
 */
class form_helper_Core {
  public static function Draw_Form($inputs, $level=1) {
    $error_messages = array();
    $prefix = str_repeat("  ", $level);

    $output = array();
    foreach ($inputs as $input) {
      if ($input->type == 'group') {
        $output[] = "$prefix<fieldset>\n";
        $output[] = "$prefix  <legend>$input->name</legend>\n";
        $output[] = "$prefix  <ul>\n";
        $output[] =form_helper::Draw_Form($input->inputs, $level + 2);
        $output[] = form_helper::Draw_Form($input->hidden, $level + 2);
        $output[] = "$prefix  </ul>\n";
        $output[] = "$prefix</fieldset>\n";
      } else {
        if ($input->error_messages()) {
          $output[] = "$prefix<li class=\"gError\">\n";
        } else if ($input->type) {
          $output[] = "$prefix<li>\n";
        } else {
          // no type means its a "hidden" so don't wrap it in <li>
        }
        if ($input->label()) {
          $output[] = "$prefix  {$input->label()}\n";
        }
        $output[] = "$prefix  {$input->render()}\n";
        if ($input->message()) {
          $output[] = "$prefix  <p>{$input->message()}</p>\n";
        }
        if ($input->error_messages()) {
          foreach ($input->error_messages() as $error_message) {
            $output[] = "$prefix  <p class=\"gError\">\n";
            $output[] = "$prefix    $error_message\n";
            $output[] = "$prefix  </p>\n";
          }
        }
        if ($input->type) {
          $output[] = "$prefix</li>\n";
        }
      }
    }
    return implode("\n", $output);
  }

}
