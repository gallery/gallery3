<?php defined("SYSPATH") or die("No direct script access.") ?>
<? // See http://docs.kohanaphp.com/libraries/pagination ?>
<ul id="gPager">
  <? /* XXX: This message isn't easily localizable */
     $from_to_msg = t("Photos {{from_number}} - {{to_number}} of {{total}}",
                      array("from_number" => $current_first_item,
                            "to_number" => $current_last_item,
                            "total" => $total_items)) ?>
  <li><?= $from_to_msg ?></li>
  <? if ($first_page): ?>
  <li><span class="ui-icon ui-icon-seek-first"></span><a href="<?= str_replace('{page}', 1, $url) ?>"><?= t("first") ?></a></li>
  <? else: ?>
  <li class="inactive"><span class="ui-icon ui-icon-seek-first"></span><?= t("first") ?></li>
  <? endif ?>
  <? if ($previous_page): ?>
  <li><span class="ui-icon ui-icon-seek-prev"></span><a href="<?= str_replace('{page}', $previous_page, $url) ?>"><?= t("previous") ?></a></li>
  <? else: ?>
  <li class="inactive"><span class="ui-icon ui-icon-seek-prev"></span><?= t("previous") ?></li>
  <? endif ?>
  <? if ($next_page): ?>
  <li><a href="<?= str_replace('{page}', $next_page, $url) ?>"><?= t("next") ?></a><span class="ui-icon ui-icon-seek-next"></span></li>
  <? else: ?>
  <li class="inactive"><?= t("next") ?><span class="ui-icon ui-icon-seek-next"></span></li>
  <? endif ?>
  <? if ($last_page): ?>
  <li><a href="<?= str_replace('{page}', $last_page, $url) ?>"><?= t("last") ?></a><span class="ui-icon ui-icon-seek-end"></span></li>
  <? else: ?>
  <li class="inactive"><?= t("last") ?><span class="ui-icon ui-icon-seek-end"></span></li>
  <? endif ?>
</ul>
