<?php defined("SYSPATH") or die("No direct script access.") ?>

<div id='l10n-client' class='hidden'>
  <div class='labels'>
    <span class='toggle'><?= t('Translate Text') ?></span>
    <div class='label strings'><h2><?= t('Page Text') ?></h2></div>
    <div class='label source'><h2><?= t('Source') ?></div>
    <div class='label translation'><h2><?= t('Translation to %language',
                                             array('%language' => 'TODO')) ?></h2></div>
  </div>
  <div id='l10n-client-string-select'>
    <ul class='string-list'>
      <? foreach ($string_list as $string): ?>
      <li class='<?= $string["translation"] === ''  ? "untranslated" : "translated" ?>'><?= $string["source"] ?></li>
      <? endforeach; ?>
    </ul>
    <?= $l10n_search_form ?>
  </div>
  <div id='l10n-client-string-editor'>
    <div class='source'>
      <div class='source-text'></div>
    </div>
    <div class='translation'>
      <?= $l10n_form ?>
    </div>
  </div>
  <script type="text/javascript">
    var l10n_client_data = <?= json_encode($string_list) ?>;
  </script>
</div>
