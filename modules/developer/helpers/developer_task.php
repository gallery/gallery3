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
class developer_task_Core {
  static function available_tasks() {
    // Return empty array so nothing appears in the maintenance screen
    return array();
  }

  static function create_module($task) {
    $context = unserialize($task->context);
    $module_path = (MODPATH . "{$context['name']}");

    switch ($context["step"]) {
    case 0:               // Create directory tree
      foreach (array("", "controllers", "helpers", "js", "views") as $dir) {
        $path = "$module_path/$dir";
        if (!file_exists($path)) {
          mkdir($path, 0774);
        }
      }
      break;
    case 1:               // Generate installer
      ob_start();
      $v = new View("installer.txt");
      $v->module_name = $context['name'];
      $v->callbacks = $context["theme"];
      print $v->render();
      file_put_contents("$module_path/helpers/{$context['name']}_installer.php", ob_get_contents());
      ob_end_clean();
      break;
    case 2:               // Generate theme helper
      self::_render_helper_file($context, "theme");
      break;
    case 3:               // Generate block helper
      self::_render_helper_file($context, "block");
      break;
    case 4:               // Generate menu helper
      self::_render_helper_file($context, "menu");
      break;
    case 5:               // Generate event helper
      self::_render_helper_file($context, "event");
      break;
    case 6:               // Generate module.info (do last)
      ob_start();
      $v = new View("module_info.txt");
      $v->module_name = $context['name'];
      $v->module_description = $context["description"];
      print $v->render();
      file_put_contents("$module_path/module.info", ob_get_contents());
      ob_end_clean();
      break;
    }
    $task->done = (++$context["step"]) >= 7;
    $task->context = serialize($context);
    $task->state = "success";
    $task->percent_complete = ($context["step"] / 7.0) * 100;
  }

  private static function _render_helper_file($context, $helper) {
    if (!empty($context[$helper])) {
      $config = Kohana::config("developer.methods");
      ob_start();
      $v = new View("helpers.txt");
      $v->helper = $helper;
      $v->module_name = $context['name'];
      $v->callbacks = array();
      foreach ($context[$helper] as $callback) {
        $v->callbacks[$callback] = $config[$helper][$callback];
      }
      print $v->render();
      file_put_contents(MODPATH . "{$context['name']}/helpers/{$context['name']}_{$helper}.php",
                        ob_get_contents());
      ob_end_clean();
    }
  }
}