<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="ui-helper-clearfix">
  <p>
  <? if ($item->is_album()): ?>
    <?= t("Delete the album <b>%title</b>? All photos and movies in the album will also be deleted.",
          array("title" => html::purify($item->title))) ?>
  <? else: ?>
    <?= t("Are you sure you want to delete <b>%title</b>?", array("title" => html::purify($item->title))) ?>
  <? endif ?>
  </p>
  <?= $form ?>
</div>
