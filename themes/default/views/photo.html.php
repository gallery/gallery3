<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gItem">
  <?= $theme->photo_top() ?>

  <ul id="gPager">
    <li>
      <? if ($previous_item): ?>
      <a href="<?= $previous_item->url() ?>" class="gButtonLink ui-icon-left ui-state-default ui-corner-all">
      <span class="ui-icon ui-icon-triangle-1-w"></span><?= t("previous") ?></a>
      <? else: ?>
      <a class="gButtonLink ui-icon-left ui-state-disabled ui-corner-all">
      <span class="ui-icon ui-icon-triangle-1-w"></span><?= t("previous") ?></a>
      <? endif; ?>
    </li>
    <li class="gInfo"><?= t("%position of %total", array("position" => $position, "total" => $sibling_count)) ?></li>
    <li>
      <? if ($next_item): ?>
      <a href="<?= $next_item->url() ?>" class="gButtonLink ui-icon-right ui-state-default ui-corner-all">
      <span class="ui-icon ui-icon-triangle-1-e"></span><?= t("next") ?></a>
      <? else: ?>
      <a class="gButtonLink ui-icon-right ui-state-disabled ui-corner-all">
      <span class="ui-icon ui-icon-triangle-1-e"></span><?= t("next") ?></a>
      <? endif ?>
    </li>
  </ul>

  <a href="#" class="gFullSizeLink" title="<?= t("View full size") ?>"><?= $item->resize_tag(array("id" => "gPhotoId-{$item->id}")) ?></a>

  <div id="gInfo">
    <h1><?= $item->title ?></h1>
    <div><?= $item->description ?></div>
  </div>

  <?= $theme->photo_bottom() ?>
</div>
