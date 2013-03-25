<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="l10n-client" class="hidden">
  <div class="labels">
    <span id="l10n-client-toggler">
      <a id="g-minimize-l10n">_</a>
      <a id="g-close-l10n" title="<?= t("Stop the translation mode")->for_html_attr() ?>"
         href="<?= html::clean_attribute(url::site("l10n_client/toggle_l10n_mode?csrf=".access::csrf_token())) ?>">X</a>
    </span>
    <div class="label strings"><h2><?= t("Page text") ?>
    <? if (!Input::instance()->get('show_all_l10n_messages')): ?>
      <a style="background-color:#fff" href="<?= url::site("admin/languages?show_all_l10n_messages=1") ?>"><?= t("(Show all)") ?></a>
    <? endif; ?>
    </h2></div>
    <div class="label source"><h2><?= t("Source") ?></div>
    <div class="label translation"><h2><?= t("Translation to %language",
                                             array("language" => locales::display_name())) ?></h2></div>
  </div>
  <div id="l10n-client-string-select">
    <ul class="string-list">
      <? foreach ($string_list as $string): ?>
      <li class="<?= $string["translation"] === ""  ? "untranslated" : "translated" ?>">
        <? if (is_array($string["source"])): ?>
       [one] - <?= $string["source"]["one"] ?><br/>
       [other] - <?= $string["source"]["other"] ?>
        <? else: ?>
        <?= $string["source"] ?>
        <? endif; ?>
      </li>
      <? endforeach; ?>
    </ul>

    <?= $l10n_search_form ?>
  </div>
  <div id="l10n-client-string-editor">
    <div class="source">
      <p class="source-text"></p>
      <p id="source-text-tmp-space" style="display:none"></p>
    </div>
    <div class="translation">
      <form method="post" action="<?= url::site("l10n_client/save") ?>" id="g-l10n-client-save-form">
        <?= access::csrf_form_field() ?>
        <?= form::hidden("l10n-message-key") ?>
        <?= form::textarea("l10n-edit-translation", "", ' id="l10n-edit-translation" rows="5" class="translationField"') ?>
        <div id="plural-zero" class="translationField hidden">
          <label for="l10n-edit-plural-translation-zero">[zero]</label>
          <?= form::textarea("l10n-edit-plural-translation-zero", "", ' rows="2"') ?>
        </div>
        <div id="plural-one" class="translationField hidden">
          <label for="l10n-edit-plural-translation-one">[one]</label>
          <?= form::textarea("l10n-edit-plural-translation-one", "", ' rows="2"') ?>
        </div>
        <div id="plural-two" class="translationField hidden">
          <label for="l10n-edit-plural-translation-two">[two]</label>
          <?= form::textarea("l10n-edit-plural-translation-two", "", ' rows="2"') ?>
        </div>
        <div id="plural-few" class="translationField hidden">
          <label for="l10n-edit-plural-translation-few">[few]</label>
          <?= form::textarea("l10n-edit-plural-translation-few", "", ' rows="2"') ?>
        </div>
        <div id="plural-many" class="translationField hidden">
          <label for="l10n-edit-plural-translation-many">[many]</label>
          <?= form::textarea("l10n-edit-plural-translation-many", "", ' rows="2"') ?>
        </div>
        <div id="plural-other" class="translationField hidden">
          <label for="l10n-edit-plural-translation-other">[other]</label>
          (<a href="http://www.unicode.org/cldr/data/charts/supplemental/language_plural_rules.html"><?= t("learn more about plural forms") ?></a>)
          <?= form::textarea("l10n-edit-plural-translation-other", "", ' rows="2"') ?>
        </div>
        <input type="submit" name="l10n-edit-save" value="<?= t("Save translation")->for_html_attr() ?>"/>
        <a href="javascript: Gallery.l10nClient.copySourceText()"
           class="g-button ui-state-default ui-corner-all"><?= t("Copy source text") ?></a>
      </form>
    </div>
  </div>
  <script type="text/javascript">
    var MSG_TRANSLATE_TEXT = <?= t("Translate text")->for_js() ?>;
    var l10n_client_data = <?= json_encode($string_list) ?>;
    var plural_forms = <?= json_encode($plural_forms) ?>;
    var toggle_l10n_mode_url = <?= html::js_string(url::site("l10n_client/toggle_l10n_mode")) ?>;
    var csrf = <?= html::js_string(access::csrf_token()) ?>;
  </script>
</div>
