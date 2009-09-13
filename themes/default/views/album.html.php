<?php defined("SYSPATH") or die("No direct script access.") ?>
<? // @todo Set hover on AlbumGrid list items for guest users ?>
<div id="gInfo">
  <?= $theme->album_top() ?>
  <h1><?= html::purify($item->title) ?></h1>
  <div class="gDescription"><?= nl2br(html::purify($item->description)) ?></div>
</div>

<ul id="gAlbumGrid">
<? if (count($children)): ?>
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
    <?= $theme->context_menu($child, "#gItemId-{$child->id} .gThumbnail") ?>
    <h2><span></span><a href="<?= $child->url() ?>"><?= html::purify($child->title) ?></a></h2>
    <ul class="gMetadata">
      <?= $theme->thumb_info($child) ?>
    </ul>
  </li>
  <? endforeach ?>
<? else: ?>
  <? if ($user->admin || access::can("add", $item)): ?>
  <? $addurl = url::file("index.php/simple_uploader/app/$item->id") ?>
  <li><?= t("There aren't any photos here yet! <a %attrs>Add some</a>.",
            array("attrs" => html::mark_clean("href=\"$addurl\" class=\"gDialogLink\""))) ?></li>
  <? else: ?>
  <li><?= t("There aren't any photos here yet!") ?></li>
  <? endif; ?>
<? endif; ?>
</ul>
<?= $theme->album_bottom() ?>

<?= $theme->pager() ?>
