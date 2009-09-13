<?php defined("SYSPATH") or die("No direct script access.") ?>
<li class="ui-icon-left">
  <span class="ui-icon ui-icon-folder-open"></span>
  <span ondblclick="open_dir('')">
    <?= t("All") ?>
  </span>
  <ul>

    <? foreach ($parents as $dir): ?>
    <li class="ui-icon-left">
      <span class="ui-icon ui-icon-folder-open"></span>
      <span ondblclick='open_dir(<?= html::js_string($dir) ?>)'>
        <?= html::clean(basename($dir)) ?>
      </span>
      <ul>
        <? endforeach ?>

        <? foreach ($files as $file): ?>
        <li class="ui-icon-left">
          <span class="ui-icon <?= is_dir($file) ? "ui-icon-folder-collapsed" : "ui-icon-document" ?>"></span>
          <span onclick="select_file(this)"
                <? if (is_dir($file)): ?>
                ondblclick="open_dir($(this).attr('file'))"
                <? endif ?>
                file="<?= html::clean_attribute($file) ?>"
                >
            <?= html::clean(basename($file)) ?>
          </span>
        </li>
        <? endforeach ?>
        <? if (!$files): ?>
        <li> <i> <?= t("empty") ?> </i> </li>
        <? endif ?>

        <? foreach ($parents as $dir): ?>
      </ul>
    </li>
    <? endforeach ?>

  </ul>
</li>
