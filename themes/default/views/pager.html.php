<?php defined("SYSPATH") or die("No direct script access.") ?>
<? // See http://docs.kohanaphp.com/libraries/pagination ?>
<ul id="gPager">
  <li><?= sprintf(_("Photos %d - %d of %d"), $current_first_item, $current_last_item, $total_items) ?></li>
  <? if ($first_page): ?>
  <li class="first"><a href="<?= str_replace('{page}', 1, $url) ?>"><?= _("first") ?></a></li>
  <? else: ?>
  <li class="first_inactive"><?= _("first") ?></li>
  <? endif ?>
  <? if ($previous_page): ?>
  <li class="previous"><a href="<?= str_replace('{page}', $previous_page, $url) ?>"><?= _("previous") ?></a></li>
  <? else: ?>
  <li class="previous_inactive"><?= _("previous") ?></li>
  <? endif ?>
  <? if ($next_page): ?>
  <li class="next"><a href="<?= str_replace('{page}', $next_page, $url) ?>"><?= _("next") ?></a></li>
  <? else: ?>
  <li class="next_inactive"><?= _("next") ?></li>
  <? endif ?>
  <? if ($last_page): ?>
  <li class="last"><a href="<?= str_replace('{page}', $last_page, $url) ?>"><?= _("last") ?></a></li>
  <? else: ?>
  <li class="last_inactive"><?= _("last") ?></li>
  <? endif ?>
</ul>
