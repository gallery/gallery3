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
class L10n_Client_Controller extends Controller {
  public function save($string) {
    access::verify_csrf();

    print json_encode(new stdClass());
  }

  private static function _l10n_client_form() {
    $form = new Forge("/l10n_client/save", "", "post", array("id" => "gL10nClientSaveForm"));
    $group = $form->group("l10n_message");
    $group->textarea("l10n-edit-target");
    $group->submit("l10n-edit-save")->value(t("Save translation"));
    // TODO(andy_st): Avoiding multiple submit buttons for now (hassle with jQuery form plugin).
    // $group->submit("l10n-edit-copy")->value(t("Copy source"));
    // $group->submit("l10n-edit-clear")->value(t("Clear"));

    return $form;
  }

  private static function _l10n_client_search_form() {
    $form = new Forge("/l10n_client/search", "", "post", array("id" => "gL10nSearchForm"));
    $group = $form->group("l10n_search");
    $group->input("l10n-search")->id("gL10nSearch");
    $group->submit("l10n-search-filter-clear")->value(t("X"));

    return $form;
  }

  public static function l10n_form() {
    $calls = I18n::instance()->getCallLog();

    if ($calls) {
      $string_list = array();
      foreach ($calls as $call) {
        list ($message, $options) = $call;
        if (is_array($message)) {
          // TODO: Translate each message. If it has a plural form, get
          // the current locale's plural rules and all plural translations.
          $options['count'] = 1;
          $source = $message['one'];
        } else {
          $source = $message;
        }
        $translation = '';
        if (I18n::instance()->hasTranslation($message, $options)) {
          $translation = I18n::instance()->hasTtranslation($message, $options);
        }
        $string_list[] = array('source' => $source,
                               'translation' => $translation);
      }

      return View::factory('l10n_client.html',
                           array('string_list' => $string_list,
                                 'l10n_form' => self::_l10n_client_form(),
                                 'l10n_search_form' => self::_l10n_client_search_form()))
        ->render();
    }

    return '';
  }
}
