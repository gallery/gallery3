<?php defined("SYSPATH") or die("No direct script access.") ?>
<? // See http://docs.kohanaphp.com/libraries/pagination ?>
<ul id="gPager">
  <? /* XXX: This message isn't easily localizable */
     $from_to_msg = t("Photos %from_number - %to_number of %total",
                      array("from_number" => $current_first_item,
                            "to_number" => $current_last_item,
                            "total" => $total_items)) ?>
  <li>
  <? if ($first_page): ?>
    <a href="<?= str_replace('{page}', 1, $url) ?>" class="gButtonLink ui-icon-left ui-state-default ui-corner-all">
      <span class="ui-icon ui-icon-seek-first"></span><?= t("first") ?></a>
  <? else: ?>
    <a class="gButtonLink ui-icon-left ui-state-disabled ui-corner-all">
      <span class="ui-icon ui-icon-seek-first"></span><?= t("first") ?></a>
  <? endif ?>
  <? if ($previous_page): ?>
    <a href="<?= str_replace('{page}', $previous_page, $url) ?>" class="gButtonLink ui-icon-left ui-state-default ui-corner-all">
      <span class="ui-icon ui-icon-seek-prev"></span><?= t("previous") ?></a>
  <? else: ?>
    <a class="gButtonLink ui-icon-left ui-state-disabled ui-corner-all">
      <span class="ui-icon ui-icon-seek-prev"></span><?= t("previous") ?></a>
  <? endif ?>
  </li>
  <li class="gInfo"><?= $from_to_msg ?></li>
  <li class="txtright">
  <? if ($next_page): ?>
    <a href="<?= str_replace('{page}', $next_page, $url) ?>" class="gButtonLink ui-icon-right ui-state-default ui-corner-all">
      <span class="ui-icon ui-icon-seek-next"></span><?= t("next") ?></a>
  <? else: ?>
    <a class="gButtonLink ui-state-disabled ui-icon-right ui-corner-all">
      <span class="ui-icon ui-icon-seek-next"></span><?= t("next") ?></a>
  <? endif ?>
  <? if ($last_page): ?>
    <a href="<?= str_replace('{page}', $last_page, $url) ?>" class="gButtonLink ui-icon-right ui-state-default ui-corner-all">
      <span class="ui-icon ui-icon-seek-end"></span><?= t("last") ?></a>
  <? else: ?>
    <a class="gButtonLink ui-state-disabled ui-icon-right ui-corner-all">
      <span class="ui-icon ui-icon-seek-end"></span><?= t("last") ?></a>
  <? endif ?>
  </li>
</ul>
