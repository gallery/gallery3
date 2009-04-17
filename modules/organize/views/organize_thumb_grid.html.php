<?php defined("SYSPATH") or die("No direct script access.") ?>
<? foreach ($children as $i => $child): ?>
<? $item_class = "gPhoto"; ?>
<? if ($child->is_album()): ?>
  <? $item_class = "gAlbum"; ?>
<? endif ?>
<li id="thumb_<?= $child->id ?>" class="gMicroThumbContainer" ref="<?= $child->id ?>">
  <div id="gMicroThumb-<?= $child->id ?>" class="gMicroThumb <?= $item_class ?>">
    <?= $child->thumb_tag(array("class" => "gThumbnail"), $thumbsize, true) ?>
  </div>
</li>
<? endforeach ?>
