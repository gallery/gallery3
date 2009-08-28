<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul class="gMetadata">
  <li>
    <strong class="caption"><?= t("Title:") ?></strong>
    <?= p::purify($item->title) ?>
  </li>
  <? if ($item->description): ?>
  <li>
    <strong class="caption"><?= t("Description:") ?></strong>
     <?= nl2br(p::purify($item->description)) ?>
  </li>
  <? endif ?>
  <? if (!$item->is_album()): ?>
  <li>
    <strong class="caption"><?= t("File name:") ?></strong>
    <?= p::clean($item->name) ?>
  </li>
  <? endif ?>
  <? if ($item->captured): ?>
  <li>
    <strong class="caption"><?= t("Captured:") ?></strong>
    <?= date("M j, Y H:i:s", $item->captured)?>
  </li>
  <? endif ?>
  <? if ($item->owner): ?>
  <li>
    <strong class="caption"><?= t("Owner:") ?></strong>
    <? if ($item->owner->url): ?>
    <a href="<?= $item->owner->url ?>"><?= p::clean($item->owner->display_name()) ?></a>
    <? else: ?>
    <?= p::clean($item->owner->display_name()) ?>
    <? endif ?>
  </li>
  <? endif ?>
</ul>
