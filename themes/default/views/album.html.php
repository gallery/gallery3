<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gAlbumHeader">
  <h1><?= $item->title_edit ?></h1>
  <span class="gUnderState"><?= $item->description_edit ?></span>
  <? if ($theme->module("slideshow")): ?>
    <a href="<?= slideshow::link() ?>" id="gSlideshowLink" class="gButtonLink"><?= slideshow::button_text()?></a>
  <? endif; ?>
</div>

<ul id="gAlbumGrid">
  <? foreach ($children as $i => $child): ?>
  <? $album_class = ""; ?>
  <? if ($child->is_album()): ?>
  <? $album_class = "gAlbum "; ?>
  <? endif ?>
  <li class="gItem <?= $album_class ?>">
    <a href="<?= url::site("{$child->type}s/{$child->id}") ?>">
      <img id="gPhotoID-<?= $child->id ?>" class="gThumbnail"
           alt="photo" src="<?= $child->thumbnail_url() ?>"
           width="<?= $child->thumbnail_width ?>"
           height="<?= $child->thumbnail_height ?>" />
    </a>
    <h2><?= $child->title_edit ?></h2>
    <ul class="gMetadata">
      <li>Views: 321</li>
      <? if ($child->owner): ?>
      <li><?= _("By: ") ?><a href="#"><?= $child->owner->name ?></a></li>
      <? endif ?>
    </ul>
  </li>
  <? endforeach ?>
</ul>

<?= $theme->pager() ?>
