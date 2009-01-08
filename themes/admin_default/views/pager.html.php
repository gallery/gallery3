<?php defined("SYSPATH") or die("No direct script access.") ?>
<? // See http://docs.kohanaphp.com/libraries/pagination ?>
<ul id="gPager">
   <? /* XXX: This message isn't easily localizable */
      $from_to_msg = t("{{from_number}} - {{to_number}} of {{total}}",
                       array("from_number" => $current_first_item,
                             "to_number" => $current_last_item,
                             "total" => $total_items)) ?>
  <li><?= $from_to_msg?></li>
  <? if ($first_page): ?>
  <li class="first"><a href="<?= str_replace('{page}', 1, $url) ?>"><?= t("first") ?></a></li>
  <? else: ?>
  <li class="first_inactive"><?= t("first") ?></li>
  <? endif ?>
  <? if ($previous_page): ?>
  <li class="previous"><a href="<?= str_replace('{page}', $previous_page, $url) ?>"><?= t("previous") ?></a></li>
  <? else: ?>
  <li class="previous_inactive"><?= t("previous") ?></li>
  <? endif ?>
  <? if ($next_page): ?>
  <li class="next"><a href="<?= str_replace('{page}', $next_page, $url) ?>"><?= t("next") ?></a></li>
  <? else: ?>
  <li class="next_inactive"><?= t("next") ?></li>
  <? endif ?>
  <? if ($last_page): ?>
  <li class="last"><a href="<?= str_replace('{page}', $last_page, $url) ?>"><?= t("last") ?></a></li>
  <? else: ?>
  <li class="last_inactive"><?= t("last") ?></li>
  <? endif ?>
</ul>
