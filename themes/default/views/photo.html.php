<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gItem">
  <?= $theme->photo_top() ?>
  
  <ul id="gPager">
    <li><?= sprintf(_("%d of %d"), $position, $sibling_count) ?></li>
    <? if ($previous_item): ?>
    <li class="previous"><a href="<?= $previous_item->url() ?>"><?= _("previous") ?></a></li>
    <? endif ?>
    <? if ($next_item): ?>
    <li class="next"><a href="<?= $next_item->url() ?>"><?= _("next") ?></a></li>
    <? endif ?>
  </ul>

  <img id="gPhotoId-<?= $item->id ?>" 
      src="<?= $item->resize_url() ?>"
      alt="<?= $item->title ?>" 
      width="<?= $item->resize_width ?>"
      height="<?= $item->resize_height ?>" />

  <div id="gInfo">
    <h1><?= $item->title ?></h1>
    <div><?= $item->description ?></div>
  </div>

  <?= $theme->photo_bottom() ?>
</div>
