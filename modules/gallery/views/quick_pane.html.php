<?php defined("SYSPATH") or die("No direct script access.") ?>
<? foreach ($button_list->main as $button): ?>
<a class="<?= $button->class ?> ui-corner-all ui-state-default" href="<?= $button->href ?>"
  title="<?= $button->title ?>">
  <span class="ui-icon <?= $button->icon ?>">
    <?= $button->title ?>
  </span>
</a>
<? endforeach ?>

<? if (!empty($button_list->additional)): ?>
<a class="gButtonLink ui-corner-all ui-state-default options" href="#" title="<?= t("additional options") ?>">
  <span class="ui-icon ui-icon-triangle-1-s">
    <?= t("Additional options") ?>
  </span>
</a>

<ul id="gQuickPaneOptions" style="display: none">
  <? foreach ($button_list->additional as $button): ?>
  <li><a class="<?= $button->class ?>" href="<?= $button->href ?>"
    title="<?= $button->title ?>">
    <?=  $button->title ?>
  </a></li>
  <? endforeach ?>
</ul>
<? endif ?>
