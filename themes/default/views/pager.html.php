<? defined("SYSPATH") or die("No direct script access."); ?>
<? // See http://docs.kohanaphp.com/libraries/pagination ?>
<div id="gPagination">
  <?= sprintf(_("Photos %d - %d of %d"), $current_first_item, $current_last_item, $total_items) ?>
  <? if ($first_page): ?>
  <a href="<?= str_replace('{page}', 1, $url) ?>"><?= _("first") ?></a>
  <? else: ?>
  <span class="first_inactive"><?= _("first") ?></span>
  <? endif ?>

  <? if ($previous_page): ?>
  <a href="<?= str_replace('{page}', $previous_page, $url) ?>"><?= _("previous") ?></a>
  <? else: ?>
  <span class="previous_inactive"><?= _("previous") ?></span>
  <? endif ?>

  <? if ($next_page): ?>
  <a href="<?= str_replace('{page}', $next_page, $url) ?>"><?= _("next") ?></a>
  <? else: ?>
  <span class="next_inactive"><?= _("next") ?></span>
  <? endif ?>

  <? if ($last_page): ?>
  <a href="<?= str_replace('{page}', $last_page, $url) ?>"><?= _("last") ?></a>
  <? else: ?>
  <span class="last_inactive"><?= _("last") ?></span>
  <? endif ?>
</div>
