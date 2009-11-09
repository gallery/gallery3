<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-item">
  <?= $theme->photo_top() ?>

  <ul class="g-pager ui-helper-clearfix">
    <li>
      <? if ($previous_item): ?>
      <a href="<?= $previous_item->url() ?>" class="g-button ui-icon-left ui-state-default ui-corner-all">
      <span class="ui-icon ui-icon-triangle-1-w"></span><?= t("Previous") ?></a>
      <? else: ?>
      <a class="g-button ui-icon-left ui-state-disabled ui-corner-all">
      <span class="ui-icon ui-icon-triangle-1-w"></span><?= t("Previous") ?></a>
      <? endif; ?>
    </li>
    <li class="g-info"><?= t("%position of %total", array("position" => $position, "total" => $sibling_count)) ?></li>
    <li class="g-text-right">
      <? if ($next_item): ?>
      <a href="<?= $next_item->url() ?>" class="g-button ui-icon-right ui-state-default ui-corner-all">
      <span class="ui-icon ui-icon-triangle-1-e"></span><?= t("Next") ?></a>
      <? else: ?>
      <a class="g-button ui-icon-right ui-state-disabled ui-corner-all">
      <span class="ui-icon ui-icon-triangle-1-e"></span><?= t("Next") ?></a>
      <? endif ?>
    </li>
  </ul>

  <div id="g-movie" class="ui-helper-clearfix">
    <?= $item->movie_img(array("class" => "g-movie", "id" => "g-movie-id-{$item->id}")) ?>
    <?= $theme->context_menu($item, "#g-movie-id-{$item->id}") ?>
  </div>
  
  <div id="g-info">
    <h1><?= html::purify($item->title) ?></h1>
    <div><?= nl2br(html::purify($item->description)) ?></div>
  </div>

  <?= $theme->photo_bottom() ?>
</div>
