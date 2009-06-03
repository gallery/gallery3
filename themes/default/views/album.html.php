<?php defined("SYSPATH") or die("No direct script access.") ?>
<? // @todo Set hover on AlbumGrid list items for guest users ?>
<div id="gInfo">
  <?= $theme->album_top() ?>
  <h1><?= p::clean($item->title) ?></h1>
  <div class="gDescription"><?= p::clean($item->description) ?></div>
</div>

<ul id="gAlbumGrid">
  <? foreach ($children as $i => $child): ?>
    <? $item_class = "gPhoto"; ?>
    <? if ($child->is_album()): ?>
      <? $item_class = "gAlbum"; ?>
    <? endif ?>
  <li id="gItemId-<?= $child->id ?>" class="gItem <?= $item_class ?>">
    <?= $theme->thumb_top($child) ?>
    <a href="<?= $child->url() ?>">
      <?= $child->thumb_img(array("class" => "gThumbnail")) ?>
    </a>
    <?= $theme->thumb_bottom($child) ?>
    <h2><span></span><a href="<?= $child->url() ?>"><?= p::clean($child->title) ?></a></h2>
    <ul class="gMetadata">
      <?= $theme->thumb_info($child) ?>
    </ul>
  </li>
  <? endforeach ?>
</ul>
<?= $theme->album_bottom() ?>

<?= $theme->pager() ?>
